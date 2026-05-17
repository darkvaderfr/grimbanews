<?php

namespace App\Services;

use App\Support\GrimbaArticleText;
use App\Support\GrimbaArticleDedupe;
use App\Support\GrimbaSourceCountryBackfill;
use Botble\Blog\Models\Post;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/*
 * S128 — NewsAPI.org fetcher.
 *
 * GrimbaNews-grade story coverage requires a much wider news pool
 * than the 13 RSS feeds GrimbaNews shipped with. NewsAPI returns up
 * to 100 articles per query, supports /everything (full search) and
 * /top-headlines (curated breaking), and exposes ~80 known sources
 * with stable ids that map cleanly to our news_sources.api_id.
 *
 * Architecture parallels GrimbaRssPoller intentionally:
 *   - one settings-controlled secret (NEWSAPI_KEY env or
 *     setting('grimba_newsapi_key'))
 *   - per-call dedup against newsapi_items (sha1 of article url)
 *   - posts persisted via the same Post model + Post::saving hook
 *     that auto-fills bias / ownership / credibility from source_id
 *   - hero image lifted from `urlToImage` (feed-level field) and
 *     falls back to the article-page scrape used in S93
 *   - near-duplicate cluster matching reuses GrimbaRssPoller's
 *     static helper so a NewsAPI article and an RSS-ingested article
 *     covering the same event auto-attach to the same story_cluster
 */
class GrimbaNewsApiFetcher
{
    private const ENDPOINT = 'https://newsapi.org/v2';
    private const USER_AGENT = 'GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)';
    private const FETCH_TIMEOUT = 20;
    private const MAX_PAGE_SIZE = 100;

    public function __construct(
        private GrimbaArticleImageScraper $imageScraper,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->key() !== null;
    }

    public function key(): ?string
    {
        $fromSetting = trim((string) setting('grimba_newsapi_key', ''));
        if ($fromSetting !== '') {
            return $fromSetting;
        }
        $fromEnv = trim((string) env('NEWSAPI_KEY', ''));
        return $fromEnv !== '' ? $fromEnv : null;
    }

    /**
     * Run all configured queries (top-headlines + per-language
     * `everything` searches) and ingest new articles. Returns
     * a per-query summary suitable for CLI display or admin UI.
     *
     * @return array<int, array{query:string, kind:string, status:string, total:int, returned:int, ingested:int, deduped:int, skipped:int, error:?string}>
     */
    public function fetchAll(): array
    {
        $key = $this->key();
        if ($key === null) {
            return [[
                'query' => '-', 'kind' => '-', 'status' => 'skipped',
                'total' => 0, 'returned' => 0, 'ingested' => 0, 'deduped' => 0, 'skipped' => 0,
                'error' => 'NEWSAPI_KEY not set (env or setting).',
            ]];
        }

        $summary = [];
        $calls = 0;
        $maxCalls = $this->maxCallsPerRun();
        $dailyBudget = $this->dailyRequestBudget();

        // Top-headlines per configured country/category. This is the
        // high-volume automatic intake path: every scheduled sweep asks
        // NewsAPI for each configured category instead of waiting for an
        // editor to manually seed the dashboard.
        foreach ($this->countries() as $country) {
            foreach ($this->categories() as $category) {
                if (! $this->canSpendCall($calls, $maxCalls, $dailyBudget)) {
                    $summary[] = $this->skippedSummary('top-headlines', "country={$country} category={$category}", 'NewsAPI request guardrail reached.');
                    break 2;
                }
                $calls++;
                $summary[] = $this->fetchTopHeadlines($country, $category);
            }
        }

        // /everything queries: full-text searches on the topic feed.
        // Defaults to French. Editor controls per-query via settings.
        $queries = $this->everythingQueries();
        $lang = (string) setting('grimba_newsapi_language', 'fr');

        foreach ($queries as $q) {
            if (! $this->canSpendCall($calls, $maxCalls, $dailyBudget)) {
                $summary[] = $this->skippedSummary('everything', "q={$q} ({$lang})", 'NewsAPI request guardrail reached.');
                break;
            }
            $calls++;
            $summary[] = $this->fetchEverything($q, $lang);
        }

        return $summary;
    }

    public function plannedCallCount(): int
    {
        return count($this->countries()) * count($this->categories()) + count($this->everythingQueries());
    }

    public function dailyRequestBudget(): int
    {
        return max(1, (int) setting('grimba_newsapi_daily_request_budget', 900));
    }

    public function maxCallsPerRun(): int
    {
        return max(1, (int) setting('grimba_newsapi_max_calls_per_run', 120));
    }

    public function callsToday(): int
    {
        if (! Schema::hasTable('grimba_newsapi_runs')) {
            return 0;
        }

        return DB::table('grimba_newsapi_runs')
            ->where('status', '!=', 'skipped')
            ->where('started_at', '>=', now()->startOfDay())
            ->count();
    }

    /**
     * @return array<string>
     */
    public function countries(): array
    {
        $raw = (string) setting('grimba_newsapi_countries', 'fr,us,gb,ca,ng,za,eg,ma,au,in,ae,il,br,mx');
        $list = array_filter(array_map(fn ($s) => mb_strtolower(trim((string) $s)), explode(',', $raw)));
        return $list ?: ['fr'];
    }

    /**
     * @return array<string>
     */
    public function categories(): array
    {
        $allowed = ['business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology'];
        $raw = (string) setting('grimba_newsapi_categories', implode(',', $allowed));
        $list = collect(explode(',', str_replace("\n", ',', $raw)))
            ->map(fn ($s) => mb_strtolower(trim((string) $s)))
            ->filter(fn ($s) => in_array($s, $allowed, true))
            ->unique()
            ->values()
            ->all();

        return $list ?: $allowed;
    }

    /**
     * @return array<string>
     */
    public function everythingQueries(): array
    {
        $raw = (string) setting('grimba_newsapi_queries', 'macron OR retraites OR énergie OR climat OR ukraine OR israël');
        return array_values(array_filter(array_map('trim', explode("\n", str_replace(',', "\n", $raw)))));
    }

    /**
     * @return array{query:string, kind:string, status:string, total:int, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    private function fetchTopHeadlines(string $country, string $category): array
    {
        return $this->run('top-headlines', "country={$country} category={$category}", [
            'country'  => $country,
            'category' => $category,
            'pageSize' => self::MAX_PAGE_SIZE,
        ]);
    }

    /**
     * @return array{query:string, kind:string, status:string, total:int, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    /**
     * Public per-query wrapper for the `/everything` endpoint. Used by
     * `grimba:backfill-category` to drive the fetcher with a tightly-
     * scoped seed query, bypassing the configured `everythingQueries`
     * list. Returns the same summary row shape as `fetchAll()`.
     *
     * Vader 2026-05-17 — fixes the `fetchOnce()` typo in
     * `GrimbaBackfillCategory` (was referencing a non-existent method).
     */
    public function fetchEverythingPublic(string $query, ?string $lang = null): array
    {
        $lang = $lang ?: (string) setting('grimba_newsapi_language', 'fr');
        return $this->fetchEverything($query, $lang);
    }

    private function fetchEverything(string $query, string $lang): array
    {
        // NewsAPI free tier indexes /everything with a ~24h delay,
        // so a 24h window from "now" returns 0 articles even when
        // the same query against /top-headlines is non-empty. We use
        // a 48h window + sha1-hash dedup so re-ingesting the same
        // article twice is a cheap no-op. On a paid tier this is
        // overkill but harmless. Adjustable via setting if needed.
        $hours = (int) setting('grimba_newsapi_everything_window_hours', 48);
        $from = now()->subHours($hours)->toIso8601String();

        return $this->run('everything', "q={$query} ({$lang})", [
            'q'        => $query,
            'language' => $lang,
            'from'     => $from,
            'sortBy'   => 'publishedAt',
            'pageSize' => self::MAX_PAGE_SIZE,
        ]);
    }

    /**
     * Run one HTTP call against NewsAPI + ingest matched articles.
     *
     * @param array<string,mixed> $params
     * @return array{query:string, kind:string, status:string, total:int, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    private function run(string $endpoint, string $label, array $params): array
    {
        $startedAt = microtime(true);
        $started = now();
        $runId = $this->startRun($endpoint, $label, $params, $started);

        try {
            $res = Http::withUserAgent(self::USER_AGENT)
                ->withHeaders(['X-Api-Key' => $this->key()])
                ->timeout(self::FETCH_TIMEOUT)
                ->connectTimeout(10)
                ->get(self::ENDPOINT . '/' . $endpoint, $params);

            if (! $res->successful()) {
                $err = $res->json('message') ?: ('HTTP ' . $res->status());
                $summary = [
                    'query' => $label, 'kind' => $endpoint,
                    'status' => 'failed', 'total' => 0, 'returned' => 0, 'ingested' => 0, 'deduped' => 0, 'skipped' => 0,
                    'error' => Str::limit((string) $err, 160),
                ];
                $this->finishRun($runId, $summary, $startedAt);

                return $summary;
            }

            $body = $res->json();
            $articles = (array) ($body['articles'] ?? []);
            $total = (int) ($body['totalResults'] ?? count($articles));
            $returned = count($articles);

            $ingested = 0;
            $deduped = 0;
            $skipped = 0;
            foreach ($articles as $a) {
                $result = $this->ingestArticle($a);
                if ($result === 'ingested') {
                    $ingested++;
                } elseif ($result === 'duplicate') {
                    $deduped++;
                } else {
                    $skipped++;
                }
            }

            $summary = [
                'query' => $label, 'kind' => $endpoint,
                'status' => 'ok', 'total' => $total, 'returned' => $returned, 'ingested' => $ingested, 'deduped' => $deduped, 'skipped' => $skipped,
                'error' => null,
            ];
            $this->finishRun($runId, $summary, $startedAt);

            return $summary;
        } catch (Throwable $e) {
            Log::warning('[GrimbaNewsApiFetcher] call failed', [
                'endpoint' => $endpoint, 'label' => $label, 'error' => $e->getMessage(),
            ]);
            $summary = [
                'query' => $label, 'kind' => $endpoint,
                'status' => 'failed', 'total' => 0, 'returned' => 0, 'ingested' => 0, 'deduped' => 0, 'skipped' => 0,
                'error' => Str::limit($e->getMessage(), 160),
            ];
            $this->finishRun($runId, $summary, $startedAt);

            return $summary;
        }
    }

    /**
     * @return 'ingested'|'duplicate'|'skipped'
     */
    private function ingestArticle(array $article): string
    {
        $url = (string) ($article['url'] ?? '');
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return 'skipped';
        }

        $apiSourceId = (string) ($article['source']['id'] ?? '');
        $sourceName  = (string) ($article['source']['name'] ?? '');

        // Resolve to our news_sources row. If absent, auto-create a
        // placeholder marked unknown bias for editor review.
        $sourceId = $this->resolveSourceId($apiSourceId, $sourceName, $url);
        $sourceCountry = $sourceId
            ? DB::table('news_sources')->where('id', $sourceId)->value('country')
            : null;

        $title = trim((string) ($article['title'] ?? ''));
        if ($title === '') {
            return 'skipped';
        }

        // Strip the trailing source attribution that NewsAPI bakes
        // into many headlines (" - Le Monde", " | BBC News").
        $title = preg_replace('/\s+[–\-—|]\s+[^|–\-—]+$/u', '', $title) ?: $title;

        if (GrimbaArticleDedupe::hasSeen($url, $title, $sourceName, $this->hostFromUrl($url))) {
            return 'duplicate';
        }

        $hash = sha1($url);

        $description = GrimbaArticleText::stripNewsApiTruncationMarker((string) ($article['description'] ?? '')) ?? '';
        $content     = GrimbaArticleText::stripNewsApiTruncationMarker((string) ($article['content'] ?? $description)) ?? '';
        if (trim(strip_tags($content)) === '' && trim(strip_tags($description)) !== '') {
            $content = $description;
        }
        $publishedAt = $this->toIso((string) ($article['publishedAt'] ?? ''));

        // urlToImage is feed-level. Many NewsAPI sources set it; for
        // those that don't, fall back to article-page scrape (shared
        // with the RSS pipeline at S93).
        $image = (string) ($article['urlToImage'] ?? '');
        $imageMethod = null;
        $imageError = null;
        if ($image === '' || ! filter_var($image, FILTER_VALIDATE_URL)) {
            [$scraped, $scrapeMethod] = $this->imageScraper->extractFromUrl($url);
            $image = $scraped ?: '';
            $imageMethod = $scraped ? ($scrapeMethod ?: 'scrape') : null;
            $imageError = $scraped ? null : 'no usable image found';
        } else {
            $imageMethod = 'newsapi';
        }

        try {
            DB::beginTransaction();

            $postId = $this->createDraftPost([
                'title'        => $title,
                'description'  => $description,
                'content'      => $content,
                'url'          => $url,
                'image'        => $image,
                'image_method' => $imageMethod,
                'image_error'  => $imageError,
                'source_id'    => $sourceId,
                'source_name'  => $sourceName ?: null,
                'source_country' => $sourceCountry,
                'published_at' => $publishedAt,
            ]);
            if ($postId === null) {
                DB::rollBack();
                return 'skipped';
            }

            DB::table('newsapi_items')->insert([
                'source_id'        => $sourceId,
                'api_source_id'    => $apiSourceId !== '' ? $apiSourceId : null,
                'article_url'      => Str::limit($url, 2040, ''),
                'article_url_hash' => $hash,
                'post_id'          => $postId,
                'published_at'     => $publishedAt,
                'fetched_at'       => now(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();
            return 'ingested';
        } catch (Throwable $e) {
            DB::rollBack();
            Log::warning('[GrimbaNewsApiFetcher] ingest failed', [
                'url' => $url, 'error' => $e->getMessage(),
            ]);
            return 'skipped';
        }
    }

    /**
     * @return array{query:string, kind:string, status:string, total:int, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    private function skippedSummary(string $endpoint, string $label, string $reason): array
    {
        return [
            'query' => $label,
            'kind' => $endpoint,
            'status' => 'skipped',
            'total' => 0,
            'returned' => 0,
            'ingested' => 0,
            'deduped' => 0,
            'skipped' => 0,
            'error' => $reason,
        ];
    }

    private function canSpendCall(int $callsThisRun, int $maxCalls, int $dailyBudget): bool
    {
        return $callsThisRun < $maxCalls && $this->callsToday() < $dailyBudget;
    }

    private function startRun(string $endpoint, string $label, array $params, Carbon $started): ?int
    {
        if (! Schema::hasTable('grimba_newsapi_runs')) {
            return null;
        }

        return (int) DB::table('grimba_newsapi_runs')->insertGetId([
            'endpoint' => $endpoint,
            'country' => $params['country'] ?? null,
            'category' => $params['category'] ?? null,
            'language' => $params['language'] ?? null,
            'query_label' => Str::limit($label, 500, ''),
            'request_params' => json_encode($params),
            'status' => 'pending',
            'started_at' => $started,
            'created_at' => $started,
            'updated_at' => $started,
        ]);
    }

    /**
     * @param array{query:string, kind:string, status:string, total:int, returned:int, ingested:int, deduped:int, skipped:int, error:?string} $summary
     */
    private function finishRun(?int $runId, array $summary, float $startedAt): void
    {
        if ($runId === null || ! Schema::hasTable('grimba_newsapi_runs')) {
            return;
        }

        DB::table('grimba_newsapi_runs')->where('id', $runId)->update([
            'status' => $summary['status'],
            'total_results' => $summary['total'],
            'returned_articles' => $summary['returned'],
            'ingested_articles' => $summary['ingested'],
            'deduped_articles' => $summary['deduped'],
            'skipped_articles' => $summary['skipped'],
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'error_message' => $summary['error'],
            'finished_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveSourceId(string $apiId, string $name, string $articleUrl = ''): ?int
    {
        if ($apiId !== '') {
            $byApi = DB::table('news_sources')->where('api_id', $apiId)->first(['id', 'name', 'website', 'country']);
            if ($byApi) {
                if (trim((string) ($byApi->country ?? '')) === '') {
                    $this->backfillSourceCountryIfMissing(
                        (int) $byApi->id,
                        (string) ($byApi->name ?: $name),
                        $byApi->website,
                        $apiId,
                        $articleUrl
                    );
                }

                return (int) $byApi->id;
            }
        }

        if ($name !== '') {
            $byName = DB::table('news_sources')->where('name', $name)->first(['id', 'website', 'country']);
            if ($byName) {
                // Backfill api_id on the existing row so future calls
                // hit the indexed lookup above.
                $updates = [];
                if ($apiId !== '') {
                    $updates['api_id'] = $apiId;
                }

                $country = $this->inferredSourceCountry($name, $byName->website, $apiId, $articleUrl);
                if (trim((string) ($byName->country ?? '')) === '' && $country !== null) {
                    $updates['country'] = $country;
                }

                if ($updates !== []) {
                    $updates['updated_at'] = now();
                    DB::table('news_sources')->where('id', $byName->id)->update($updates);
                }

                return (int) $byName->id;
            }
        }

        // Auto-create a stub source row marked unknown bias. Editor
        // can later upgrade via /admin/grimba/news-sources.
        if ($name === '') {
            return null;
        }

        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = 'source-' . substr(sha1($name), 0, 8);
        }

        // Avoid slug collision.
        $base = $slug;
        $i = 2;
        while (DB::table('news_sources')->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        $country = $this->inferredSourceCountry($name, null, $apiId, $articleUrl);

        $insertId = DB::table('news_sources')->insertGetId([
            'name'             => Str::limit($name, 180, ''),
            'slug'             => $slug,
            'api_id'           => $apiId !== '' ? $apiId : null,
            'website'          => null,
            'bias_rating'      => 'unknown',
            'ownership_type'   => null,
            'credibility_score'=> null,
            'country'          => $country,
            'language'         => null,
            'description'      => 'Source créée automatiquement par l\'ingest NewsAPI. À enrichir.',
            'notes'            => 'auto-created by GrimbaNewsApiFetcher',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return (int) $insertId;
    }

    private function backfillSourceCountryIfMissing(int $sourceId, string $name, ?string $website, string $apiId, string $articleUrl): void
    {
        if (DB::table('news_sources')
            ->where('id', $sourceId)
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->exists()) {
            return;
        }

        $country = $this->inferredSourceCountry($name, $website, $apiId, $articleUrl);
        if ($country === null) {
            return;
        }

        DB::table('news_sources')->where('id', $sourceId)->update([
            'country' => $country,
            'updated_at' => now(),
        ]);
    }

    private function inferredSourceCountry(string $name, ?string $website, string $apiId, string $articleUrl): ?string
    {
        $evidence = $website ?: $articleUrl ?: $name;
        $inferred = GrimbaSourceCountryBackfill::infer($name, $evidence, $apiId);

        return $inferred && $inferred['confidence'] >= 80 ? $inferred['country'] : null;
    }

    private function createDraftPost(array $a): ?int
    {
        try {
            $post = new Post();
            $description = GrimbaArticleText::stripNewsApiTruncationMarker((string) ($a['description'] ?? '')) ?? '';
            $content = GrimbaArticleText::stripNewsApiTruncationMarker((string) ($a['content'] ?? '')) ?? '';
            if (trim(strip_tags($content)) === '' && trim(strip_tags($description)) !== '') {
                $content = $description;
            }

            $post->name        = Str::limit($a['title'], 240, '');
            $post->description = Str::limit(strip_tags($description), 600, '…');
            // Vader 2026-05-16 — drop the "Lire l'article original" link
            // wrapper + suppress the NewsAPI "Full text is unavailable in
            // the news API lite version" boilerplate. Canonical source is
            // already linked from the article-hero-card.
            $__rawContent = (string) $content;
            $__rawContent = preg_replace(
                '#\s*Full text is unavailable in the news API lite version\.?\s*#iu',
                '',
                $__rawContent
            ) ?? $__rawContent;
            $post->content     = '<p>' . e(Str::limit(strip_tags($__rawContent), 1200, '…')) . '</p>';

            $autoPublish = (bool) setting('grimba_ingest_auto_publish', true);
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

            if ($a['source_id']) {
                $post->source_id = $a['source_id'];
            }
            if ($a['source_name']) {
                $post->source_name = $a['source_name'];
            }

            if ($a['published_at']) {
                $post->created_at = $a['published_at'];
            }
            if ($a['image'] !== '') {
                $post->image = $a['image'];
            }
            $this->applyImageProvenance(
                $post,
                (string) ($a['url'] ?? ''),
                $a['image_method'] ?? null,
                $a['image_error'] ?? null
            );

            // Reuse the static cluster helper (S132 + S159) — match
            // against existing clusters AND form new clusters from
            // orphans, folding in translated_name tokens for cross-
            // language matching when a translation exists already.
            if (empty($post->story_cluster_id)) {
                $candidate = GrimbaRssPoller::findOrFormCluster(
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
                    ->classify((string) $post->name, $post->description, $post->source_name, $a['source_country'] ?? null);
                foreach ($catIds as $cid) {
                    DB::table('post_categories')->insertOrIgnore([
                        'category_id' => $cid,
                        'post_id'     => $post->id,
                    ]);
                }
            } catch (Throwable $e) {
                Log::warning('[GrimbaNewsApiFetcher] category classification failed', [
                    'post_id' => $post->id, 'error' => $e->getMessage(),
                ]);
            }

            return (int) $post->id;
        } catch (Throwable $e) {
            Log::warning('[GrimbaNewsApiFetcher] createDraftPost failed', [
                'title' => $a['title'] ?? '?', 'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function toIso(string $raw): ?string
    {
        if ($raw === '') return null;
        try {
            return Carbon::parse($raw)->toDateTimeString();
        } catch (Throwable) {
            return null;
        }
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

    private function hostFromUrl(string $url): string
    {
        $raw = trim($url);
        if ($raw === '') {
            return '';
        }

        $url = preg_match('#^https?://#i', $raw) ? $raw : 'https://' . ltrim($raw, '/');
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return '';
        }

        $host = mb_strtolower(trim($host));
        $host = preg_replace('/^(www|m|amp)\./', '', $host) ?: $host;

        return trim($host, '.');
    }
}
