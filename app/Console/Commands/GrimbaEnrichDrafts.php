<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Throwable;

/*
 * S84 — one-shot image backfill for RSS-ingested posts that were
 * created before the S79 hero-image lift landed and therefore have
 * no `posts.image` value.
 *
 * Two-stage strategy per candidate:
 *   1. Re-fetch the upstream feed once, build a guid→image map, and
 *      fill any post whose guid is still in the window. Cheap — one
 *      HTTP round-trip per feed covers every candidate from that feed.
 *   2. Fallback: fetch the article page itself and read
 *      og:image / twitter:image / first `<img>`. Slow — one HTTP
 *      round-trip per candidate — but necessary since most older
 *      drafts have aged out of the feed window.
 *
 * Idempotent. Safe to re-run after feeds add new images or after
 * article pages regain an og:image tag.
 */
class GrimbaEnrichDrafts extends Command
{
    protected $signature = 'grimba:enrich-drafts
        {--limit=0 : Cap number of posts processed (0 = no cap)}
        {--feed= : Restrict to one rss_feeds.id}
        {--dry-run : Report what would change without writing}';

    protected $description = 'Backfill hero images on RSS-ingested posts that have no `posts.image` yet (S84).';

    private const USER_AGENT = 'GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)';
    private const FETCH_TIMEOUT = 15;

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $feedId = $this->option('feed');
        $dry = (bool) $this->option('dry-run');

        $query = DB::table('posts')
            ->join('rss_feed_items', 'rss_feed_items.post_id', '=', 'posts.id')
            ->leftJoin('rss_feeds', 'rss_feeds.id', '=', 'rss_feed_items.feed_id')
            ->where(fn ($q) => $q->whereNull('posts.image')->orWhere('posts.image', ''))
            ->orderBy('posts.id');

        if ($feedId !== null) {
            $query->where('rss_feed_items.feed_id', (int) $feedId);
        }
        if ($limit > 0) {
            $query->limit($limit);
        }

        $targets = $query->get([
            'posts.id as post_id',
            'rss_feed_items.link as link',
            'rss_feed_items.guid as guid',
            'rss_feed_items.feed_id as feed_id',
            'rss_feeds.url as feed_url',
        ]);

        if ($targets->isEmpty()) {
            $this->info('No candidates — nothing to backfill.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Candidates: %d%s%s',
            $targets->count(),
            $feedId !== null ? " (feed #{$feedId})" : '',
            $dry ? ' [DRY RUN]' : ''
        ));

        $feedCache = [];
        $got = 0;
        $via = ['feed' => 0, 'og' => 0, 'twitter' => 0, 'img' => 0];
        $missing = 0;

        $bar = $this->output->createProgressBar($targets->count());
        $bar->start();

        foreach ($targets as $t) {
            $url = null;
            $how = null;

            if ($t->feed_url) {
                if (! array_key_exists($t->feed_id, $feedCache)) {
                    $feedCache[$t->feed_id] = $this->loadFeedGuidMap($t->feed_url);
                }
                $map = $feedCache[$t->feed_id];
                if (is_array($map) && isset($map[$t->guid]) && $map[$t->guid] !== null) {
                    $url = $map[$t->guid];
                    $how = 'feed';
                }
            }

            if ($url === null && ! empty($t->link)) {
                [$url, $how] = $this->extractFromArticlePage($t->link);
            }

            if ($url !== null) {
                if (! $dry) {
                    DB::table('posts')
                        ->where('id', $t->post_id)
                        ->update([
                            'image'      => $url,
                            'updated_at' => now(),
                        ]);
                }
                $got++;
                $via[$how] = ($via[$how] ?? 0) + 1;
            } else {
                $missing++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['result', 'count'], [
            ['enriched',              $got],
            ['  └ via feed',          $via['feed']],
            ['  └ via og:image',      $via['og']],
            ['  └ via twitter:image', $via['twitter']],
            ['  └ via first <img>',   $via['img']],
            ['still missing',         $missing],
        ]);

        if ($dry) {
            $this->warn('DRY RUN — no posts.image values were written. Re-run without --dry-run to persist.');
        }

        return self::SUCCESS;
    }

    /**
     * Re-fetch a feed once and build a guid → image-url map.
     * Returns [] on parse success with zero matches, null on fetch/parse failure.
     *
     * @return array<string, ?string>|null
     */
    private function loadFeedGuidMap(string $feedUrl): ?array
    {
        try {
            $res = Http::withUserAgent(self::USER_AGENT)
                ->withHeaders([
                    'Accept' => 'application/rss+xml, application/atom+xml, application/xml, text/xml;q=0.8',
                ])
                ->timeout(self::FETCH_TIMEOUT)
                ->connectTimeout(10)
                ->withOptions(['allow_redirects' => ['max' => 5]])
                ->get($feedUrl);

            if (! $res->successful()) {
                return null;
            }

            $body = (string) $res->body();
            if (trim($body) === '') {
                return null;
            }

            libxml_use_internal_errors(true);
            $root = simplexml_load_string($body);
            if ($root === false) {
                libxml_clear_errors();
                return null;
            }

            $name = strtolower($root->getName());
            $map = [];

            if ($name === 'rss' && isset($root->channel)) {
                foreach ($root->channel->item as $node) {
                    $guid = trim((string) ($node->guid ?? '')) ?: trim((string) $node->link);
                    if ($guid === '') {
                        continue;
                    }
                    $summary = (string) ($node->description ?? '');
                    $contentNs = $node->children('content', true);
                    if (isset($contentNs->encoded) && trim((string) $contentNs->encoded) !== '') {
                        $summary = (string) $contentNs->encoded;
                    }
                    $map[$guid] = $this->extractImageFromNode($node, $summary);
                }
            } elseif ($name === 'feed') {
                foreach ($root->entry as $node) {
                    $guid = trim((string) ($node->id ?? '')) ?: $this->firstAtomLink($node);
                    if ($guid === '') {
                        continue;
                    }
                    $summary = (string) ($node->summary ?? $node->content ?? '');
                    $map[$guid] = $this->extractImageFromNode($node, $summary);
                }
            }

            return $map;
        } catch (Throwable $e) {
            Log::warning('[grimba:enrich-drafts] feed re-fetch failed', [
                'url'   => $feedUrl,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Mirrors GrimbaRssPoller::extractImageUrl intentionally — kept
     * local so the one-shot command doesn't force publicising a
     * private method on the hot-path poller.
     */
    private function extractImageFromNode(SimpleXMLElement $node, string $summaryHtml): ?string
    {
        // 1. RSS 2.0 <enclosure>
        foreach ($node->enclosure as $enc) {
            $type = (string) $enc['type'];
            $url  = (string) $enc['url'];
            if ($url !== '' && str_starts_with($type, 'image/') && $this->looksHttp($url)) {
                return $url;
            }
        }

        // 2 + 3. Media RSS (mrss) — iterate to preserve attributes.
        $media = $node->children('http://search.yahoo.com/mrss/');
        if ($media) {
            $thumb = null;
            $content = null;
            foreach ($media as $tag => $c) {
                $attrs = $c->attributes();
                $url = (string) ($attrs['url'] ?? '');
                if ($url === '' || ! $this->looksHttp($url)) {
                    continue;
                }
                if ($tag === 'thumbnail' && $thumb === null) {
                    $thumb = $url;
                    continue;
                }
                if ($tag === 'content' && $content === null) {
                    $medium  = (string) ($attrs['medium'] ?? '');
                    $type    = (string) ($attrs['type'] ?? '');
                    $hasSize = isset($attrs['width']) || isset($attrs['height']);
                    $isImg   = (bool) preg_match('/\.(jpe?g|png|gif|webp|avif)(\?|$)/i', $url);
                    if ($medium === 'image' || str_starts_with($type, 'image/') || $hasSize || $isImg) {
                        $content = $url;
                    }
                }
            }
            if ($thumb !== null) {
                return $thumb;
            }
            if ($content !== null) {
                return $content;
            }
        }

        // 4. First <img> in body HTML
        if ($summaryHtml !== '' && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $summaryHtml, $m)) {
            $url = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($this->looksHttp($url)) {
                return $url;
            }
        }

        return null;
    }

    private function firstAtomLink(SimpleXMLElement $entry): string
    {
        foreach ($entry->link as $l) {
            $rel = (string) $l['rel'];
            if ($rel === '' || $rel === 'alternate') {
                return (string) $l['href'];
            }
        }
        return '';
    }

    /**
     * Fetch an article page and try to lift og:image, twitter:image,
     * or the first `.jpg/.png/.webp/.avif` `<img>` in the HTML.
     *
     * @return array{0: ?string, 1: ?string} [url, method]
     */
    private function extractFromArticlePage(string $url): array
    {
        try {
            $res = Http::withUserAgent(self::USER_AGENT)
                ->withHeaders([
                    'Accept'          => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'fr,en;q=0.6',
                ])
                ->timeout(self::FETCH_TIMEOUT)
                ->connectTimeout(10)
                ->withOptions(['allow_redirects' => ['max' => 5]])
                ->get($url);

            if (! $res->successful()) {
                return [null, null];
            }

            $html = (string) $res->body();
            if ($html === '') {
                return [null, null];
            }

            // og:image (property-then-content OR content-then-property)
            if (preg_match('/<meta[^>]+property=["\']og:image(?::url)?["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)
                || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]*property=["\']og:image(?::url)?["\']/i', $html, $m)) {
                $img = $this->resolveUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $url);
                if ($img && $this->looksHttp($img)) {
                    return [$img, 'og'];
                }
            }

            // twitter:image
            if (preg_match('/<meta[^>]+name=["\']twitter:image(?::src)?["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $m)
                || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]*name=["\']twitter:image(?::src)?["\']/i', $html, $m)) {
                $img = $this->resolveUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $url);
                if ($img && $this->looksHttp($img)) {
                    return [$img, 'twitter'];
                }
            }

            // First meaningful <img> with a real image extension —
            // skip tracking pixels and sprite sheets.
            if (preg_match('/<img[^>]+src=["\']([^"\']+\.(?:jpe?g|png|webp|avif)(?:\?[^"\']*)?)["\']/i', $html, $m)) {
                $img = $this->resolveUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $url);
                if ($img && $this->looksHttp($img)) {
                    return [$img, 'img'];
                }
            }
        } catch (Throwable $e) {
            Log::debug('[grimba:enrich-drafts] article fetch failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return [null, null];
    }

    /**
     * Upgrade relative/protocol-relative URLs to absolute, using the
     * article URL as the base. Some publishers ship og:image as
     * /wp-content/... or //cdn.example/...
     */
    private function resolveUrl(string $candidate, string $base): ?string
    {
        if ($candidate === '') {
            return null;
        }
        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            return $candidate;
        }
        $parts = parse_url($base);
        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }
        $origin = $parts['scheme'] . '://' . $parts['host'];
        if (str_starts_with($candidate, '//')) {
            return $parts['scheme'] . ':' . $candidate;
        }
        if (str_starts_with($candidate, '/')) {
            return $origin . $candidate;
        }
        // rare: plain relative — resolve against directory of base path
        $dir = isset($parts['path']) ? rtrim(dirname($parts['path']), '/') : '';
        return $origin . $dir . '/' . $candidate;
    }

    private function looksHttp(string $url): bool
    {
        return (bool) preg_match('#^https?://[^\s<>"\']+$#i', $url);
    }
}
