<?php

namespace App\Services;

use Botble\Blog\Models\Post;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;
use stdClass;
use Throwable;

/*
 * GrimbaRssPoller — fetch registered RSS/Atom feeds, dedup items via
 * the rss_feed_items ledger, and create draft Post rows so editors
 * can review before publishing.
 *
 * Deliberately minimal: no queue, no retries beyond Http::retry, no
 * HTML scrubbing beyond strip_tags. Those are later-sprint concerns.
 * MVP goal is to get real francophone content flowing into the DB so
 * comparator pages stop running on 15 seed posts.
 */
class GrimbaRssPoller
{
    private const USER_AGENT = 'GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)';
    private const FETCH_TIMEOUT = 15;
    private const MAX_ITEMS_PER_FEED = 25;

    /**
     * Poll every active feed. Returns a summary array suitable for CLI
     * display or admin telemetry.
     *
     * @return array<int, array{feed_id:int, source_name:string, status:string, ingested:int, error:?string}>
     */
    public function pollAll(): array
    {
        $feeds = DB::table('rss_feeds')
            ->join('news_sources', 'news_sources.id', '=', 'rss_feeds.source_id')
            ->where('rss_feeds.is_active', true)
            ->orderBy('rss_feeds.id')
            ->get([
                'rss_feeds.*',
                'news_sources.name as source_name',
                'news_sources.bias_rating as source_bias',
            ]);

        $summary = [];
        foreach ($feeds as $feed) {
            $summary[] = $this->pollOne($feed);
        }
        return $summary;
    }

    /**
     * Poll a single feed row (rss_feeds joined with news_sources).
     *
     * @return array{feed_id:int, source_name:string, status:string, ingested:int, error:?string}
     */
    public function pollOne(stdClass $feed): array
    {
        $now = now();
        $ingested = 0;

        try {
            $xml = $this->fetch($feed->url);
            $items = $this->parseItems($xml);

            $items = array_slice($items, 0, self::MAX_ITEMS_PER_FEED);

            foreach ($items as $item) {
                if ($this->ingestItem($feed, $item)) {
                    $ingested++;
                }
            }

            DB::table('rss_feeds')->where('id', $feed->id)->update([
                'last_polled_at'       => $now,
                'last_success_at'      => $now,
                'last_error'           => null,
                'consecutive_failures' => 0,
                'items_ingested'       => DB::raw('items_ingested + ' . (int) $ingested),
                'updated_at'           => $now,
            ]);

            return [
                'feed_id'     => (int) $feed->id,
                'source_name' => (string) ($feed->source_name ?? ''),
                'status'      => 'ok',
                'ingested'    => $ingested,
                'error'       => null,
            ];
        } catch (Throwable $e) {
            DB::table('rss_feeds')->where('id', $feed->id)->update([
                'last_polled_at'       => $now,
                'last_error'           => substr($e->getMessage(), 0, 2000),
                'consecutive_failures' => DB::raw('consecutive_failures + 1'),
                'updated_at'           => $now,
            ]);

            Log::warning('[GrimbaRssPoller] feed failed', [
                'feed_id' => $feed->id,
                'url'     => $feed->url,
                'error'   => $e->getMessage(),
            ]);

            return [
                'feed_id'     => (int) $feed->id,
                'source_name' => (string) ($feed->source_name ?? ''),
                'status'      => 'failed',
                'ingested'    => 0,
                'error'       => $e->getMessage(),
            ];
        }
    }

    private function fetch(string $url): string
    {
        $response = Http::withUserAgent(self::USER_AGENT)
            ->withHeaders([
                'Accept'          => 'application/rss+xml, application/atom+xml, application/xml, text/xml;q=0.8',
                'Accept-Language' => 'fr,en;q=0.6',
            ])
            ->timeout(self::FETCH_TIMEOUT)
            ->connectTimeout(10)
            ->withOptions(['allow_redirects' => ['max' => 5]])
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('HTTP ' . $response->status() . ' from upstream');
        }

        $body = (string) $response->body();
        if (trim($body) === '') {
            throw new \RuntimeException('Empty response body');
        }

        return $body;
    }

    /**
     * Parse RSS 2.0 or Atom. Returns a normalised item list.
     *
     * @return array<int, array{guid:string, title:string, link:?string, summary:string, published_at:?string}>
     */
    private function parseItems(string $xml): array
    {
        libxml_use_internal_errors(true);
        $root = simplexml_load_string($xml);
        if ($root === false) {
            $errs = array_map(fn ($e) => trim($e->message), libxml_get_errors());
            libxml_clear_errors();
            throw new \RuntimeException('XML parse failed: ' . implode('; ', array_slice($errs, 0, 3)));
        }

        $name = strtolower($root->getName());
        $items = [];

        if ($name === 'rss' && isset($root->channel)) {
            foreach ($root->channel->item as $node) {
                $items[] = $this->normaliseRssItem($node);
            }
        } elseif ($name === 'feed') {
            foreach ($root->entry as $node) {
                $items[] = $this->normaliseAtomItem($node);
            }
        } else {
            throw new \RuntimeException('Unknown feed root element: ' . $name);
        }

        return array_values(array_filter($items, fn ($i) => $i['guid'] !== '' && $i['title'] !== ''));
    }

    private function normaliseRssItem(SimpleXMLElement $node): array
    {
        $title = trim((string) $node->title);
        $link  = trim((string) $node->link);
        $guid  = trim((string) ($node->guid ?? '')) ?: $link;

        $summary = (string) ($node->description ?? '');
        $contentNs = $node->children('content', true);
        if (isset($contentNs->encoded) && trim((string) $contentNs->encoded) !== '') {
            $summary = (string) $contentNs->encoded;
        }

        $pub = (string) ($node->pubDate ?? '');
        $published = $pub !== '' ? $this->toIso($pub) : null;

        return [
            'guid'         => $guid,
            'title'        => $title,
            'link'         => $link ?: null,
            'summary'      => $this->cleanSummary($summary),
            'published_at' => $published,
        ];
    }

    private function normaliseAtomItem(SimpleXMLElement $node): array
    {
        $title = trim((string) $node->title);

        $link = '';
        foreach ($node->link as $l) {
            $rel = (string) $l['rel'];
            if ($rel === '' || $rel === 'alternate') {
                $link = (string) $l['href'];
                break;
            }
        }

        $guid = trim((string) ($node->id ?? '')) ?: $link;

        $summary = (string) ($node->summary ?? $node->content ?? '');

        $pub = (string) ($node->published ?? $node->updated ?? '');
        $published = $pub !== '' ? $this->toIso($pub) : null;

        return [
            'guid'         => $guid,
            'title'        => $title,
            'link'         => $link ?: null,
            'summary'      => $this->cleanSummary($summary),
            'published_at' => $published,
        ];
    }

    private function toIso(string $raw): ?string
    {
        $ts = strtotime($raw);
        return $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
    }

    private function cleanSummary(string $raw): string
    {
        $plain = trim(html_entity_decode(strip_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plain = preg_replace('/\s+/u', ' ', $plain) ?? $plain;
        return Str::limit($plain, 600, '…');
    }

    /**
     * Returns true if this item produced a new post; false if it was
     * already in the ledger (dedup hit).
     */
    private function ingestItem(stdClass $feed, array $item): bool
    {
        $existing = DB::table('rss_feed_items')
            ->where('feed_id', $feed->id)
            ->where('guid', $item['guid'])
            ->first(['id']);

        if ($existing) {
            return false;
        }

        $postId = $this->createDraftPost($feed, $item);

        DB::table('rss_feed_items')->insert([
            'feed_id'        => $feed->id,
            'guid'           => $item['guid'],
            'link'           => $item['link'] ?? null,
            'title_snapshot' => Str::limit($item['title'], 450, ''),
            'post_id'        => $postId,
            'seen_at'        => now(),
            'published_at'   => $item['published_at'],
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return true;
    }

    private function createDraftPost(stdClass $feed, array $item): ?int
    {
        try {
            $post = new Post();
            $post->name        = Str::limit($item['title'], 240, '');
            $post->description = $item['summary'] ?? '';
            $post->content     = $item['link']
                ? '<p><a href="' . e($item['link']) . '" target="_blank" rel="noopener">Lire l’article original</a></p>'
                . '<p>' . e($item['summary'] ?? '') . '</p>'
                : '<p>' . e($item['summary'] ?? '') . '</p>';
            $post->status      = 'draft';
            $post->author_id   = 1;
            $post->author_type = \Botble\ACL\Models\User::class;
            $post->is_featured = false;

            // source_id lets Post::saving auto-copy bias/ownership/credibility
            $post->source_id = $feed->source_id;

            // Explicit defaults in case the saving hook misses anything
            $post->source_name = $feed->source_name ?? null;
            $post->bias_rating = $feed->source_bias ?? 'unknown';

            if (! empty($item['published_at'])) {
                $post->created_at = $item['published_at'];
            }

            // Near-duplicate detection (S78) — if this title matches an
            // article already attached to a cluster, inherit the cluster
            // so the L/C/R coverage bar lights up on first ingest.
            // Editors can override via the post editor dropdown if the
            // auto-guess is wrong.
            if (empty($post->story_cluster_id)) {
                $candidate = self::findLikelyCluster($post->name);
                if ($candidate !== null) {
                    $post->story_cluster_id = $candidate;
                }
            }

            $post->save();

            $slugValue = $this->uniqueSlug($post->name);
            Slug::create([
                'key'            => $slugValue,
                'reference_id'   => $post->id,
                'reference_type' => Post::class,
                'prefix'         => SlugHelper::getPrefix(Post::class) ?? '',
            ]);

            return (int) $post->id;
        } catch (Throwable $e) {
            Log::warning('[GrimbaRssPoller] createDraftPost failed', [
                'feed_id' => $feed->id,
                'guid'    => $item['guid'],
                'error'   => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Near-duplicate cluster detection via Jaccard similarity on
     * tokenised, diacritic-stripped, stopword-filtered titles.
     *
     * Public + static so the retroactive artisan command
     * (grimba:recluster) can reuse it without instantiating the
     * full poller.
     *
     * Returns a story_cluster_id when at least one existing post
     * (≤30 days old, attached to a cluster) crosses the 0.35
     * Jaccard threshold. Null when nothing sticks.
     */
    public static function findLikelyCluster(string $title, int $lookbackDays = 30, float $threshold = 0.35): ?int
    {
        $target = self::tokeniseForMatch($title);
        if (count($target) < 3) {
            return null;
        }

        $candidates = DB::table('posts')
            ->whereNotNull('story_cluster_id')
            ->whereIn('status', ['published', 'draft'])
            ->where('created_at', '>=', now()->subDays($lookbackDays))
            ->get(['name', 'story_cluster_id']);

        $byCluster = [];
        foreach ($candidates as $c) {
            $other = self::tokeniseForMatch((string) $c->name);
            if (count($other) < 3) continue;

            $score = self::jaccard($target, $other);
            if ($score >= $threshold) {
                $cid = (int) $c->story_cluster_id;
                $byCluster[$cid] = max($byCluster[$cid] ?? 0.0, $score);
            }
        }

        if (empty($byCluster)) {
            return null;
        }

        arsort($byCluster);
        return (int) array_key_first($byCluster);
    }

    /**
     * Lowercase, strip diacritics, drop digits/punctuation, drop
     * stopwords + anything shorter than 3 chars. Unique set semantics.
     */
    private static function tokeniseForMatch(string $s): array
    {
        $s = mb_strtolower($s);
        // Diacritic fold: "Réforme" → "Reforme", "grève" → "greve"
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($ascii === false || $ascii === '') {
            $ascii = $s;
        }
        // Replace any apostrophe/punct/digit/space block with a single space
        $spaced = preg_replace('/[\p{P}\d\s]+/u', ' ', $ascii) ?? $ascii;
        $tokens = array_filter(preg_split('/\s+/', trim($spaced)) ?: []);

        static $stop = [
            // French
            'les','des','une','dans','pour','avec','sans','plus','mais','sont','est','ont','pas',
            'que','qui','ces','son','ses','leur','leurs','nos','vos','etre','avoir','cette','cet',
            'tous','tout','toute','toutes','entre','apres','avant','sous','sur','par','aux','aux',
            'ceci','cela','cet','faire','deja','encore','fois','donc','alors','ainsi','meme',
            // English
            'the','and','but','for','with','without','this','that','these','those','from','into',
            'about','over','after','before','under','above','between','against','during','while',
            'where','when','what','which','who','whom','how','why','they','them','their','theirs',
            'there','here','been','being','have','has','had','does','did','doing','will','would',
            'could','should','can','may','might','must','shall','into','onto',
        ];

        $out = [];
        foreach ($tokens as $t) {
            if (strlen($t) < 3) continue;
            if (in_array($t, $stop, true)) continue;
            $out[$t] = true;
        }
        return array_keys($out);
    }

    private static function jaccard(array $a, array $b): float
    {
        if (empty($a) || empty($b)) return 0.0;
        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));
        return $union > 0 ? $intersection / $union : 0.0;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'article';
        $base = Str::limit($base, 200, '');
        $slug = $base;
        $i = 2;
        while (Slug::where('key', $slug)->where('reference_type', Post::class)->exists()) {
            $slug = Str::limit($base, 195, '') . '-' . $i;
            $i++;
            if ($i > 50) {
                $slug = $base . '-' . Str::random(6);
                break;
            }
        }
        return $slug;
    }
}
