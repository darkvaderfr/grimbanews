<?php

namespace App\Services;

use Botble\Blog\Models\Post;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/*
 * S128 — NewsAPI.org fetcher.
 *
 * GroundNews-grade story coverage requires a much wider news pool
 * than the 13 RSS feeds GrimbaNews shipped with. NewsAPI returns up
 * to 100 articles per query, supports /everything (full search) and
 * /top-headlines (curated breaking), and exposes ~80 known sources
 * with stable ids that map cleanly to our news_sources.api_id.
 *
 * Architecture parallels GrimbaRssPoller intentionally:
 *   - one settings-controlled secret (NEWSAPI_KEY env or
 *     setting('grimba_newsapi_key'))
 *   - per-call dedup against newsapi_items (sha1 of article url)
 *   - drafts persisted via the same Post model + Post::saving hook
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
     * @return array<int, array{query:string, kind:string, status:string, total:int, ingested:int, error:?string}>
     */
    public function fetchAll(): array
    {
        $key = $this->key();
        if ($key === null) {
            return [[
                'query' => '-', 'kind' => '-', 'status' => 'skipped',
                'total' => 0, 'ingested' => 0,
                'error' => 'NEWSAPI_KEY not set (env or setting).',
            ]];
        }

        $summary = [];

        // Top-headlines per configured country. Always-on, very cheap.
        foreach ($this->countries() as $country) {
            $summary[] = $this->fetchTopHeadlines($country);
        }

        // /everything queries: full-text searches on the topic feed.
        // Defaults to French. Editor controls per-query via settings.
        $queries = $this->everythingQueries();
        $lang = (string) setting('grimba_newsapi_language', 'fr');

        foreach ($queries as $q) {
            $summary[] = $this->fetchEverything($q, $lang);
        }

        return $summary;
    }

    /**
     * @return array<string>
     */
    private function countries(): array
    {
        $raw = (string) setting('grimba_newsapi_countries', 'fr,us,gb');
        $list = array_filter(array_map(fn ($s) => mb_strtolower(trim((string) $s)), explode(',', $raw)));
        return $list ?: ['fr'];
    }

    /**
     * @return array<string>
     */
    private function everythingQueries(): array
    {
        $raw = (string) setting('grimba_newsapi_queries', 'macron OR retraites OR énergie OR climat OR ukraine OR israël');
        return array_values(array_filter(array_map('trim', explode("\n", str_replace(',', "\n", $raw)))));
    }

    /**
     * @return array{query:string, kind:string, status:string, total:int, ingested:int, error:?string}
     */
    private function fetchTopHeadlines(string $country): array
    {
        return $this->run('top-headlines', "country={$country}", [
            'country'  => $country,
            'pageSize' => self::MAX_PAGE_SIZE,
        ]);
    }

    /**
     * @return array{query:string, kind:string, status:string, total:int, ingested:int, error:?string}
     */
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
     * @return array{query:string, kind:string, status:string, total:int, ingested:int, error:?string}
     */
    private function run(string $endpoint, string $label, array $params): array
    {
        try {
            $res = Http::withUserAgent(self::USER_AGENT)
                ->withHeaders(['X-Api-Key' => $this->key()])
                ->timeout(self::FETCH_TIMEOUT)
                ->connectTimeout(10)
                ->get(self::ENDPOINT . '/' . $endpoint, $params);

            if (! $res->successful()) {
                $err = $res->json('message') ?: ('HTTP ' . $res->status());
                return [
                    'query' => $label, 'kind' => $endpoint,
                    'status' => 'failed', 'total' => 0, 'ingested' => 0,
                    'error' => Str::limit((string) $err, 160),
                ];
            }

            $body = $res->json();
            $articles = (array) ($body['articles'] ?? []);
            $total = (int) ($body['totalResults'] ?? count($articles));

            $ingested = 0;
            foreach ($articles as $a) {
                if ($this->ingestArticle($a)) {
                    $ingested++;
                }
            }

            return [
                'query' => $label, 'kind' => $endpoint,
                'status' => 'ok', 'total' => $total, 'ingested' => $ingested,
                'error' => null,
            ];
        } catch (Throwable $e) {
            Log::warning('[GrimbaNewsApiFetcher] call failed', [
                'endpoint' => $endpoint, 'label' => $label, 'error' => $e->getMessage(),
            ]);
            return [
                'query' => $label, 'kind' => $endpoint,
                'status' => 'failed', 'total' => 0, 'ingested' => 0,
                'error' => Str::limit($e->getMessage(), 160),
            ];
        }
    }

    /**
     * Returns true on a new ingest, false on dedup hit / skip.
     */
    private function ingestArticle(array $article): bool
    {
        $url = (string) ($article['url'] ?? '');
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $hash = sha1($url);

        if (DB::table('newsapi_items')->where('article_url_hash', $hash)->exists()) {
            return false;
        }

        $apiSourceId = (string) ($article['source']['id'] ?? '');
        $sourceName  = (string) ($article['source']['name'] ?? '');

        // Resolve to our news_sources row. If absent, auto-create a
        // placeholder marked unknown bias for editor review.
        $sourceId = $this->resolveSourceId($apiSourceId, $sourceName);

        $title = trim((string) ($article['title'] ?? ''));
        if ($title === '') {
            return false;
        }

        // Strip the trailing source attribution that NewsAPI bakes
        // into many headlines (" - Le Monde", " | BBC News").
        $title = preg_replace('/\s+[–\-—|]\s+[^|–\-—]+$/u', '', $title) ?: $title;

        $description = (string) ($article['description'] ?? '');
        $content     = (string) ($article['content'] ?? $description);
        $publishedAt = $this->toIso((string) ($article['publishedAt'] ?? ''));

        // urlToImage is feed-level. Many NewsAPI sources set it; for
        // those that don't, fall back to article-page scrape (shared
        // with the RSS pipeline at S93).
        $image = (string) ($article['urlToImage'] ?? '');
        if ($image === '' || ! filter_var($image, FILTER_VALIDATE_URL)) {
            [$scraped] = $this->imageScraper->extractFromUrl($url);
            $image = $scraped ?: '';
        }

        try {
            DB::beginTransaction();

            $postId = $this->createDraftPost([
                'title'        => $title,
                'description'  => $description,
                'content'      => $content,
                'url'          => $url,
                'image'        => $image,
                'source_id'    => $sourceId,
                'source_name'  => $sourceName ?: null,
                'published_at' => $publishedAt,
            ]);

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
            return $postId !== null;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::warning('[GrimbaNewsApiFetcher] ingest failed', [
                'url' => $url, 'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function resolveSourceId(string $apiId, string $name): ?int
    {
        if ($apiId !== '') {
            $byApi = DB::table('news_sources')->where('api_id', $apiId)->value('id');
            if ($byApi) {
                return (int) $byApi;
            }
        }

        if ($name !== '') {
            $byName = DB::table('news_sources')->where('name', $name)->value('id');
            if ($byName) {
                // Backfill api_id on the existing row so future calls
                // hit the indexed lookup above.
                if ($apiId !== '') {
                    DB::table('news_sources')->where('id', $byName)->update(['api_id' => $apiId]);
                }
                return (int) $byName;
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

        $insertId = DB::table('news_sources')->insertGetId([
            'name'             => Str::limit($name, 180, ''),
            'slug'             => $slug,
            'api_id'           => $apiId !== '' ? $apiId : null,
            'website'          => null,
            'bias_rating'      => 'unknown',
            'ownership_type'   => null,
            'credibility_score'=> null,
            'country'          => null,
            'language'         => null,
            'description'      => 'Source créée automatiquement par l\'ingest NewsAPI. À enrichir.',
            'notes'            => 'auto-created by GrimbaNewsApiFetcher',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return (int) $insertId;
    }

    private function createDraftPost(array $a): ?int
    {
        try {
            $post = new Post();
            $post->name        = Str::limit($a['title'], 240, '');
            $post->description = Str::limit(strip_tags($a['description']), 600, '…');
            $post->content     = '<p><a href="' . e($a['url']) . '" target="_blank" rel="noopener">Lire l’article original</a></p>'
                . '<p>' . e(Str::limit(strip_tags($a['content']), 1200, '…')) . '</p>';

            $autoPublish = (bool) setting('grimba_ingest_auto_publish', false);
            if (! $autoPublish && env('GRIMBA_INGEST_AUTO_PUBLISH')) {
                $autoPublish = filter_var(env('GRIMBA_INGEST_AUTO_PUBLISH'), FILTER_VALIDATE_BOOLEAN);
            }
            $post->status      = $autoPublish ? 'published' : 'draft';
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
                    ->classify((string) $post->name, $post->description, $post->source_name);
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
