<?php

namespace App\Console\Commands;

use App\Services\GrimbaArticleImageScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        {--dry-run : Report what would change without writing}
        {--force : Re-attempt scrape on ALL RSS-backed posts (not just image-less). Captures upstream upgrades — e.g. a publisher fixing their og:image tag.}';

    protected $description = 'Backfill hero images on RSS-ingested posts that have no `posts.image` yet (S84).';

    private const USER_AGENT = 'GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)';
    private const FETCH_TIMEOUT = 15;

    public function handle(GrimbaArticleImageScraper $scraper): int
    {
        $limit  = (int) $this->option('limit');
        $feedId = $this->option('feed');
        $dry    = (bool) $this->option('dry-run');
        $force  = (bool) $this->option('force');
        $startedAt = microtime(true);

        $query = DB::table('posts')
            ->join('rss_feed_items', 'rss_feed_items.post_id', '=', 'posts.id')
            ->leftJoin('rss_feeds', 'rss_feeds.id', '=', 'rss_feed_items.feed_id')
            ->orderBy('posts.id');

        if (! $force) {
            $query->where(fn ($q) => $q->whereNull('posts.image')->orWhere('posts.image', ''));
        }

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
            $this->logCoverage([
                'candidates' => 0, 'enriched' => 0, 'via' => ['feed' => 0, 'og' => 0, 'twitter' => 0, 'img' => 0],
                'missing' => 0, 'force' => $force, 'dry' => $dry, 'feed_id' => $feedId,
                'duration_s' => round(microtime(true) - $startedAt, 2),
            ]);
            return self::SUCCESS;
        }

        $this->info(sprintf('Candidates: %d%s%s%s',
            $targets->count(),
            $feedId !== null ? " (feed #{$feedId})" : '',
            $force ? ' [FORCE — all posts, not just image-less]' : '',
            $dry ? ' [DRY RUN]' : ''
        ));

        $feedCache = [];
        $got = 0;
        $via = ['feed' => 0, 'og' => 0, 'twitter' => 0, 'img' => 0];
        $missing = 0;
        $hasImageProvenance = Schema::hasColumn('posts', 'image_source_url')
            || Schema::hasColumn('posts', 'image_extraction_method')
            || Schema::hasColumn('posts', 'image_extracted_at')
            || Schema::hasColumn('posts', 'image_extract_error');

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
                [$url, $how] = $scraper->extractFromUrl($t->link);
            }

            if ($url !== null) {
                $how = $how ?: 'img';
                if (! $dry) {
                    $update = [
                        'image'      => $url,
                        'updated_at' => now(),
                    ];

                    if ($hasImageProvenance) {
                        $update = array_merge($update, $this->imageProvenanceUpdate(
                            (string) ($how === 'feed' ? $t->feed_url : $t->link),
                            $how,
                            null
                        ));
                    }

                    DB::table('posts')->where('id', $t->post_id)->update($update);
                }
                $got++;
                $via[$how] = ($via[$how] ?? 0) + 1;
            } else {
                if (! $dry && $hasImageProvenance) {
                    DB::table('posts')->where('id', $t->post_id)->update(array_merge(
                        $this->imageProvenanceUpdate((string) ($t->link ?: $t->feed_url), null, 'no usable image found'),
                        ['updated_at' => now()]
                    ));
                }
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

        $this->logCoverage([
            'candidates' => $targets->count(),
            'enriched'   => $got,
            'via'        => $via,
            'missing'    => $missing,
            'force'      => $force,
            'dry'        => $dry,
            'feed_id'    => $feedId,
            'duration_s' => round(microtime(true) - $startedAt, 2),
        ]);

        return self::SUCCESS;
    }

    /**
     * @return array<string, string|null|\Carbon\CarbonInterface>
     */
    private function imageProvenanceUpdate(?string $sourceUrl, ?string $method, ?string $error): array
    {
        $update = [];

        if (Schema::hasColumn('posts', 'image_source_url')) {
            $update['image_source_url'] = $sourceUrl ? mb_substr($sourceUrl, 0, 2048) : null;
        }
        if (Schema::hasColumn('posts', 'image_extraction_method')) {
            $update['image_extraction_method'] = $method ? mb_substr($method, 0, 32) : null;
        }
        if (Schema::hasColumn('posts', 'image_extracted_at')) {
            $update['image_extracted_at'] = now();
        }
        if (Schema::hasColumn('posts', 'image_extract_error')) {
            $update['image_extract_error'] = $error ? mb_substr($error, 0, 191) : null;
        }

        return $update;
    }

    /**
     * S105 — append a one-line structured record to
     * storage/logs/grimba-image-coverage.log so Vader can grep weekly
     * trends without parsing artisan stdout. Also writes to Laravel's
     * default log channel for centralised view.
     *
     * Format: ISO timestamp · candidates=N · enriched=M (feed=A og=B twitter=C img=D) · missing=K · force=bool · dry=bool · duration=s
     */
    private function logCoverage(array $stats): void
    {
        $line = sprintf(
            "[%s] grimba:enrich-drafts candidates=%d enriched=%d (feed=%d og=%d twitter=%d img=%d) missing=%d force=%s dry=%s feed=%s duration=%ss\n",
            now()->toIso8601String(),
            (int) $stats['candidates'],
            (int) $stats['enriched'],
            (int) ($stats['via']['feed'] ?? 0),
            (int) ($stats['via']['og'] ?? 0),
            (int) ($stats['via']['twitter'] ?? 0),
            (int) ($stats['via']['img'] ?? 0),
            (int) $stats['missing'],
            $stats['force'] ? 'true' : 'false',
            $stats['dry']   ? 'true' : 'false',
            $stats['feed_id'] !== null ? (string) $stats['feed_id'] : '-',
            $stats['duration_s']
        );

        $path = storage_path('logs/grimba-image-coverage.log');
        @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
        Log::info('[grimba:enrich-drafts] run complete', $stats);
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

    private function looksHttp(string $url): bool
    {
        return (bool) preg_match('#^https?://[^\s<>"\']+$#i', $url);
    }
}
