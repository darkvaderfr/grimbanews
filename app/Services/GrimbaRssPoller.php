<?php

namespace App\Services;

use Botble\Blog\Models\Post;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use SimpleXMLElement;
use stdClass;
use Throwable;

/*
 * GrimbaRssPoller — fetch registered RSS/Atom feeds, dedup items via
 * the rss_feed_items ledger, and create public Post rows by default.
 *
 * Deliberately minimal: no queue, no retries beyond Http::retry, no
 * HTML scrubbing beyond strip_tags. Those are later-sprint concerns.
 * The launch default is publish-first: editors can still force draft
 * review with grimba_ingest_auto_publish=false / env override, but
 * stale public categories are treated as the higher product risk.
 */
class GrimbaRssPoller
{
    private const USER_AGENT = 'GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)';
    private const FETCH_TIMEOUT = 15;
    private const MAX_ITEMS_PER_FEED = 50;

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
                'news_sources.country as source_country',
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

        [$image, $imageMethod] = $this->extractImageUrlWithMethod($node, $summary);

        return [
            'guid'         => $guid,
            'title'        => $title,
            'link'         => $link ?: null,
            'summary'      => $this->cleanSummary($summary),
            'published_at' => $published,
            'image'        => $image,
            'image_method' => $imageMethod,
        ];
    }

    private function normaliseAtomItem(SimpleXMLElement $node): array
    {
        $title = trim((string) $node->title);

        $link = '';
        $enclosure = '';
        foreach ($node->link as $l) {
            $rel = (string) $l['rel'];
            $type = (string) $l['type'];
            if (($rel === '' || $rel === 'alternate') && $link === '') {
                $link = (string) $l['href'];
            }
            if ($rel === 'enclosure' && str_starts_with($type, 'image/')) {
                $enclosure = (string) $l['href'];
            }
        }

        $guid = trim((string) ($node->id ?? '')) ?: $link;

        $summary = (string) ($node->summary ?? $node->content ?? '');

        $pub = (string) ($node->published ?? $node->updated ?? '');
        $published = $pub !== '' ? $this->toIso($pub) : null;

        if ($enclosure !== '') {
            $image = $enclosure;
            $imageMethod = 'atom_enclosure';
        } else {
            [$image, $imageMethod] = $this->extractImageUrlWithMethod($node, $summary);
        }

        return [
            'guid'         => $guid,
            'title'        => $title,
            'link'         => $link ?: null,
            'summary'      => $this->cleanSummary($summary),
            'published_at' => $published,
            'image'        => $image,
            'image_method' => $imageMethod,
        ];
    }

    /**
     * Lift a hero image URL out of an RSS/Atom item.
     *
     * Checks in order:
     *   1. <enclosure url="…" type="image/…"/>      (RSS 2.0 attachments)
     *   2. <media:thumbnail url="…"/>                 (MRSS namespace)
     *   3. <media:content url="…" medium="image"/>    (MRSS namespace)
     *   4. First <img src="…"> in description /
     *      content:encoded                            (universal fallback)
     *
     * Returns null when nothing plausible is found. Filters obviously
     * broken entries (no http/https scheme) so we never persist a
     * tracking-pixel data: URI as the hero image.
     */
    /** @return array{0:?string,1:?string} */
    private function extractImageUrlWithMethod(SimpleXMLElement $node, string $summaryHtml): array
    {
        // 1. RSS 2.0 <enclosure>
        foreach ($node->enclosure as $enc) {
            $type = (string) $enc['type'];
            $url  = (string) $enc['url'];
            if ($url !== '' && str_starts_with($type, 'image/') && $this->looksLikeHttpUrl($url)) {
                return [$url, 'enclosure'];
            }
        }

        // 2 + 3. Media RSS namespace. Property access (`$media->content`)
        // on a namespace-filtered SimpleXMLElement returns children with
        // attributes stripped — burned an hour on that SimpleXML quirk.
        // Iterating with `foreach ($media as $tag => $c)` preserves
        // attributes via $c->attributes().
        $media = $node->children('http://search.yahoo.com/mrss/');
        if ($media) {
            $thumb = null;
            $contentHit = null;
            foreach ($media as $tag => $c) {
                $attrs = $c->attributes();
                $url   = (string) ($attrs['url'] ?? '');
                if ($url === '' || ! $this->looksLikeHttpUrl($url)) continue;

                if ($tag === 'thumbnail' && $thumb === null) {
                    $thumb = $url;
                    continue;
                }
                if ($tag === 'content' && $contentHit === null) {
                    $medium  = (string) ($attrs['medium'] ?? '');
                    $type    = (string) ($attrs['type'] ?? '');
                    $hasSize = isset($attrs['width']) || isset($attrs['height']);
                    $looksImg = (bool) preg_match('/\.(jpe?g|png|gif|webp|avif)(\?|$)/i', $url);
                    if ($medium === 'image'
                        || str_starts_with($type, 'image/')
                        || $hasSize
                        || $looksImg) {
                        $contentHit = $url;
                    }
                }
            }
            if ($thumb !== null) return [$thumb, 'media_thumbnail'];
            if ($contentHit !== null) return [$contentHit, 'media_content'];
        }

        // 4. First <img src="…"> in body HTML
        if ($summaryHtml !== '' && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $summaryHtml, $m)) {
            $url = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($this->looksLikeHttpUrl($url)) return [$url, 'summary_img'];
        }

        return [null, null];
    }

    private function looksLikeHttpUrl(string $url): bool
    {
        return (bool) preg_match('#^https?://[^\s<>"\']+$#i', $url);
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
        // S151b — two-layer dedup. Per-feed (feed_id, guid) catches
        // most cases; canonical-URL-hash across ALL feeds catches the
        // ones where a single article is broadcast under different
        // GUIDs (BBC RSS appends #0, #2 fragments) or appears in two
        // feeds we both poll.
        $existing = DB::table('rss_feed_items')
            ->where('feed_id', $feed->id)
            ->where('guid', $item['guid'])
            ->first(['id']);

        if ($existing) {
            return false;
        }

        $canonicalHash = null;
        if (! empty($item['link'])) {
            $canonicalHash = app(\App\Services\GrimbaUrlCanonicalizer::class)
                ->hash((string) $item['link']);

            if ($canonicalHash) {
                $byCanonical = DB::table('rss_feed_items')
                    ->where('canonical_url_hash', $canonicalHash)
                    ->first(['id']);
                if ($byCanonical) {
                    return false;
                }
            }
        }

        $postId = $this->createDraftPost($feed, $item);

        DB::table('rss_feed_items')->insert([
            'feed_id'        => $feed->id,
            'guid'           => $item['guid'],
            'link'           => $item['link'] ?? null,
            'canonical_url_hash' => $canonicalHash,
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
            // Publish-first is now the default. Editors can opt back
            // into review-first with GRIMBA_INGEST_AUTO_PUBLISH=false
            // or the matching Botble setting.
            $autoPublish = true;
            if (is_callable('setting')) {
                $autoPublish = (bool) setting('grimba_ingest_auto_publish', true);
            }
            $envAutoPublish = env('GRIMBA_INGEST_AUTO_PUBLISH', null);
            if ($envAutoPublish !== null) {
                $autoPublish = filter_var($envAutoPublish, FILTER_VALIDATE_BOOLEAN);
            }
            $post->status      = $autoPublish ? 'published' : 'draft';
            if ($autoPublish && Schema::hasColumn('posts', 'published_at')) {
                $post->published_at = now();
            }
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

            // Hero image lift (S79). Botble's Post.image accepts both
            // storage-relative paths and absolute URLs; we keep the
            // upstream URL so we don't re-host copyrighted images.
            //
            // S93: when the RSS item carried no image, fall back to
            // scraping the article page for og:image / twitter:image.
            // Costs +1 HTTP req per image-less new item — bounded by
            // the per-tick dedup so it's ≤ MAX_ITEMS_PER_FEED.
            $imageSourceUrl = ! empty($item['image']) ? (string) ($feed->url ?? '') : (string) ($item['link'] ?? '');
            $imageMethod = $item['image_method'] ?? null;
            $imageError = null;

            if (! empty($item['image'])) {
                $post->image = $item['image'];
            } elseif (! empty($item['link'])) {
                [$scraped, $scrapeMethod] = app(GrimbaArticleImageScraper::class)->extractFromUrl($item['link']);
                if ($scraped) {
                    $post->image = $scraped;
                    $imageMethod = $scrapeMethod ?: 'scrape';
                } else {
                    $imageError = 'no usable image found';
                }
            } else {
                $imageError = 'missing upstream article url';
            }

            $this->applyImageProvenance($post, $imageSourceUrl, $imageMethod, $imageError);

            // Near-duplicate detection (S78 + S132 + S159) — match
            // against existing clusters first; if none, attempt to form
            // a new cluster from recent orphan articles on the same
            // event. Folds translated_name tokens in when available so
            // cross-language coverage clusters too.
            if (empty($post->story_cluster_id)) {
                $candidate = self::findOrFormCluster(
                    (string) $post->name,
                    30,
                    0.30,
                    false,
                    $post->translated_name ?? null,
                );
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

            // S165 — auto-classify into news categories at ingest.
            try {
                $catIds = app(\App\Services\GrimbaCategoryClassifier::class)
                    ->classify((string) $post->name, $post->description, $post->source_name, $feed->source_country ?? null);
                foreach ($catIds as $cid) {
                    DB::table('post_categories')->insertOrIgnore([
                        'category_id' => $cid,
                        'post_id'     => $post->id,
                    ]);
                }
            } catch (Throwable $e) {
                Log::warning('[GrimbaRssPoller] category classification failed', [
                    'post_id' => $post->id, 'error' => $e->getMessage(),
                ]);
            }

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
    public static function findLikelyCluster(string $title, int $lookbackDays = 30, float $threshold = 0.30, ?string $translatedTitle = null): ?int
    {
        // S151 — threshold tuned 0.35 → 0.30. The previous setting
        // missed too many same-event francophone clusters: news
        // headlines vary a lot in phrasing across outlets, and the
        // tokeniser drops stopwords aggressively, so even close
        // matches scored just below 0.35.
        //
        // S159 — caller can pass the post's translated_name. Tokens
        // from both the original AND the translation are unioned, so
        // an EN headline ("Trump cancels Pakistan trip") and a FR
        // headline ("Trump annule le voyage au Pakistan") share
        // enough proper-noun tokens to cluster across the language
        // boundary. The diacritic-folder already normalises both.
        //
        // The lookback window cap (still 30 days here) is the safety
        // net: a 2025 "retraites" story won't pull a 2026 "retraites"
        // story even at the looser threshold.
        $target = self::tokensForPost($title, $translatedTitle);
        if (count($target) < 3) {
            return null;
        }

        $candidates = DB::table('posts')
            ->whereNotNull('story_cluster_id')
            ->whereIn('status', ['published', 'draft'])
            ->where('created_at', '>=', now()->subDays($lookbackDays))
            ->get(['name', 'translated_name', 'story_cluster_id']);

        $byCluster = [];
        foreach ($candidates as $c) {
            $other = self::tokensForPost((string) $c->name, $c->translated_name);
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
     * S132 — full cluster resolution: existing match OR new cluster
     * formation from orphans. THE GroundNews-grade unlock.
     *
     * Without this method, two un-clustered articles on the same
     * event NEVER cluster — findLikelyCluster only matches against
     * posts that already have a story_cluster_id. At scale (NewsAPI
     * ingest, 100s of articles/day), 95% of stories stayed orphan.
     *
     * Two-stage resolution:
     *   1. findLikelyCluster — if any existing cluster matches, use it
     *   2. otherwise, scan recent ORPHAN posts (story_cluster_id IS NULL)
     *      and find ones that match the new title above threshold.
     *      If ≥1 orphan matches, MINT a new story_cluster row, attach
     *      the orphan(s) plus the calling article's id (caller fills
     *      the new article's row separately because it isn't saved
     *      yet at call time).
     *
     * Returns the resolved story_cluster_id, or null when nothing
     * matches (= a genuine new story; first article in its cluster).
     *
     * Pure: doesn't touch the calling article's row. The caller
     * applies the returned id to its own draft. Existing orphans are
     * mutated in-place (atomic update inside a transaction). When
     * resolving an already-saved orphan, pass $excludePostId so the
     * orphan scan cannot match the article against itself and mint a
     * one-post cluster.
     */
    public static function findOrFormCluster(
        string $title,
        int $lookbackDays = 30,
        float $threshold = 0.30,
        bool $dryRun = false,
        ?string $translatedTitle = null,
        ?int $excludePostId = null,
    ): ?int {
        // Stage 1 — existing-cluster match (cheap, indexed lookup).
        $existing = self::findLikelyCluster($title, $lookbackDays, $threshold, $translatedTitle);
        if ($existing !== null) {
            return $existing;
        }

        // Stage 2 — orphan scan. Only fires when stage 1 returned null.
        $target = self::tokensForPost($title, $translatedTitle);
        if (count($target) < 3) {
            return null;
        }

        // S151 — orphan-orphan formation uses a TIGHTER recency window
        // (default 2 days). Orphans only cluster with each other when
        // they're plausibly covering the same breaking event — across
        // multi-week windows, false-positive matches dominated.
        $orphanWindowHours = max(6, $lookbackDays * 2); // 60h default for 30-day caller
        $orphanCutoff = now()->subHours($orphanWindowHours);
        // Cap at 7 days even when the caller passed a long lookback —
        // events covered concurrently across outlets happen in days,
        // not weeks. The existing-cluster path (stage 1) handles the
        // long-tail "is this an old story?" case.
        $sevenDaysAgo = now()->subDays(7);
        if ($orphanCutoff->lt($sevenDaysAgo)) {
            $orphanCutoff = $sevenDaysAgo;
        }

        $orphans = DB::table('posts')
            ->whereNull('story_cluster_id')
            ->whereIn('status', ['published', 'draft'])
            ->where('created_at', '>=', $orphanCutoff)
            ->when($excludePostId !== null, fn ($query) => $query->where('id', '!=', $excludePostId))
            ->orderByDesc('id')
            // 500 cap: at scale we don't want to scan all-time orphans.
            // Recency wins; older orphans get a chance via the next
            // article that arrives within their window.
            ->limit(500)
            ->get(['id', 'name', 'translated_name']);

        $matches = [];
        foreach ($orphans as $o) {
            // S159 — same as findLikelyCluster, fold translation
            // tokens in so EN + FR variants of the same event match.
            $other = self::tokensForPost((string) $o->name, $o->translated_name);
            if (count($other) < 3) continue;
            $score = self::jaccard($target, $other);
            if ($score >= $threshold) {
                $matches[] = (int) $o->id;
            }
        }

        if (empty($matches)) {
            return null;
        }

        // Dry-run probe: signal "would form" by returning -1. Caller
        // can render this in a preview without writing.
        if ($dryRun) {
            return -1;
        }

        // Mint a new cluster + attach the matching orphans atomically.
        // Topic = the calling title trimmed; editor can edit later.
        $clusterId = DB::transaction(function () use ($title, $matches): int {
            $cid = DB::table('story_clusters')->insertGetId([
                'topic'      => Str::limit(trim($title), 200, '…'),
                'description'=> null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('posts')
                ->whereIn('id', $matches)
                ->update([
                    'story_cluster_id' => $cid,
                    'updated_at'       => now(),
                ]);

            return (int) $cid;
        });

        Log::info('[GrimbaRssPoller] orphan cluster formed', [
            'cluster_id' => $clusterId,
            'seed_title' => Str::limit($title, 80, '…'),
            'attached_orphans' => count($matches),
        ]);

        return $clusterId;
    }

    /**
     * S159 — token set for a post, unioning original title + an
     * optional translated title. When both are present, an EN
     * headline like "Trump cancels Pakistan trip" and its FR
     * translation "Trump annule le voyage au Pakistan" share the
     * proper-noun tokens (trump, pakistan), letting cross-language
     * coverage cluster together.
     *
     * @return array<int, string>
     */
    public static function tokensForPost(string $name, ?string $translatedName = null): array
    {
        $tokens = self::tokeniseForMatch($name);
        if ($translatedName !== null && trim($translatedName) !== '') {
            $tokens = array_values(array_unique(array_merge(
                $tokens,
                self::tokeniseForMatch($translatedName)
            )));
        }
        return $tokens;
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

    private function applyImageProvenance(Post $post, ?string $sourceUrl, ?string $method, ?string $error): void
    {
        if (Schema::hasColumn('posts', 'image_source_url')) {
            $post->image_source_url = $sourceUrl ? Str::limit($sourceUrl, 2048, '') : null;
        }
        if (Schema::hasColumn('posts', 'image_extraction_method')) {
            $post->image_extraction_method = $method ? Str::limit($method, 32, '') : null;
        }
        if (Schema::hasColumn('posts', 'image_extracted_at')) {
            $post->image_extracted_at = now();
        }
        if (Schema::hasColumn('posts', 'image_extract_error')) {
            $post->image_extract_error = $error ? Str::limit($error, 191, '') : null;
        }
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
