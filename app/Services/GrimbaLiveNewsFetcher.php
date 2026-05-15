<?php

namespace App\Services;

use App\Support\GrimbaArticleText;
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

class GrimbaLiveNewsFetcher
{
    private const USER_AGENT = 'GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)';
    private const FETCH_TIMEOUT = 20;
    private const GDELT_ENDPOINT = 'https://api.gdeltproject.org/api/v2/doc/doc';
    private const GOOGLE_NEWS_ENDPOINT = 'https://news.google.com/rss/search';
    private const MEDIASTACK_ENDPOINT = 'https://api.mediastack.com/v1/news';

    /**
     * @param array<int, string>|null $providers
     * @return array<int, array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}>
     */
    public function fetchAll(?array $providers = null): array
    {
        $providers = $providers ?: $this->providers();
        $summary = [];

        foreach ($providers as $provider) {
            $provider = mb_strtolower(trim($provider));

            $summary = array_merge($summary, match ($provider) {
                'gdelt' => $this->fetchGdelt(),
                'google', 'google-news' => $this->fetchGoogleNews(),
                'mediastack' => $this->fetchMediastack(),
                default => [[
                    'provider' => $provider,
                    'query' => '-',
                    'status' => 'skipped',
                    'returned' => 0,
                    'ingested' => 0,
                    'deduped' => 0,
                    'skipped' => 0,
                    'error' => 'Unknown live-news provider.',
                ]],
            });
        }

        return $summary;
    }

    /**
     * @return array<int, string>
     */
    public function providers(): array
    {
        $raw = (string) setting('grimba_breaking_providers', 'google-news,gdelt,mediastack');

        return collect(explode(',', str_replace("\n", ',', $raw)))
            ->map(fn (string $provider): string => mb_strtolower(trim($provider)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}>
     */
    private function fetchGdelt(): array
    {
        if (! (bool) setting('grimba_gdelt_active', true)) {
            return [$this->skipped('gdelt', '-', 'grimba_gdelt_active=false')];
        }

        $summary = [];
        $calls = 0;
        $maxCalls = max(1, (int) setting('grimba_gdelt_max_calls_per_run', 4));

        foreach ($this->gdeltQueries() as $query) {
            if ($calls >= $maxCalls) {
                $summary[] = $this->skipped('gdelt', $query, 'GDELT call guardrail reached.');
                break;
            }

            $calls++;
            $summary[] = $this->fetchGdeltQuery($query);
        }

        return $summary;
    }

    /**
     * @return array<int, string>
     */
    private function gdeltQueries(): array
    {
        $default = implode("\n", [
            '(breaking OR "breaking news" OR urgent OR alerte OR "en direct")',
            '(africa OR afrique OR sahel OR mali OR senegal OR sénégal OR nigeria OR sudan OR soudan OR ethiopia OR éthiopie OR kenya OR "south africa" OR "afrique du sud" OR congo OR cameroon OR cameroun OR ghana OR morocco OR maroc OR algeria OR algérie OR egypt OR égypte)',
            '(election OR élection OR war OR guerre OR ceasefire OR "cessez-le-feu" OR protest OR manifestation OR coup OR flood OR inondation OR earthquake OR cyberattack)',
        ]);
        $raw = (string) setting('grimba_gdelt_queries', $default);

        return collect(explode("\n", str_replace(',', "\n", $raw)))
            ->map(fn (string $query): string => trim($query))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    private function fetchGdeltQuery(string $query): array
    {
        try {
            $response = Http::withUserAgent(self::USER_AGENT)
                ->timeout(max(3, (int) setting('grimba_gdelt_timeout', 8)))
                ->connectTimeout(max(2, (int) setting('grimba_gdelt_connect_timeout', 5)))
                ->get(self::GDELT_ENDPOINT, [
                    'query' => $query,
                    'mode' => 'artlist',
                    'format' => 'json',
                    'sort' => 'datedesc',
                    'timespan' => (string) setting('grimba_gdelt_timespan', '24h'),
                    'maxrecords' => max(1, min(250, (int) setting('grimba_gdelt_max_records', 50))),
                ]);

            if (! $response->successful()) {
                return $this->failed('gdelt', $query, 'HTTP ' . $response->status());
            }

            $articles = (array) ($response->json('articles') ?? []);

            return $this->ingestMany('gdelt', $query, array_map(
                fn (array $article): array => $this->normaliseGdeltArticle($article),
                $articles
            ));
        } catch (Throwable $e) {
            Log::warning('[GrimbaLiveNewsFetcher] GDELT call failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return $this->failed('gdelt', $query, $e->getMessage());
        }
    }

    /**
     * @return array<int, array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}>
     */
    private function fetchGoogleNews(): array
    {
        if (! (bool) setting('grimba_google_news_active', true)) {
            return [$this->skipped('google-news', '-', 'grimba_google_news_active=false')];
        }

        $summary = [];
        $calls = 0;
        $maxCalls = max(1, (int) setting('grimba_google_news_max_calls_per_run', 4));

        foreach ($this->googleNewsQueries() as $query) {
            if ($calls >= $maxCalls) {
                $summary[] = $this->skipped('google-news', $query, 'Google News RSS call guardrail reached.');
                break;
            }

            $calls++;
            $summary[] = $this->fetchGoogleNewsQuery($query);
        }

        return $summary;
    }

    /**
     * @return array<int, string>
     */
    private function googleNewsQueries(): array
    {
        $default = implode("\n", [
            '("breaking news" OR urgent OR alerte) when:6h',
            '(africa OR afrique OR sahel OR mali OR senegal OR nigeria OR sudan OR kenya OR congo OR cameroon) when:1d',
            '(election OR war OR guerre OR climate OR climat OR cyberattack OR manifestation) when:6h',
        ]);
        $raw = (string) setting('grimba_google_news_queries', $default);

        return collect(explode("\n", str_replace(',', "\n", $raw)))
            ->map(fn (string $query): string => trim($query))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    private function fetchGoogleNewsQuery(string $query): array
    {
        try {
            $locale = (string) setting('grimba_google_news_locale', 'fr-FR');
            [$language, $country] = array_pad(explode('-', $locale, 2), 2, 'FR');
            $language = mb_strtolower($language ?: 'fr');
            $country = mb_strtoupper($country ?: 'FR');

            $response = Http::withUserAgent(self::USER_AGENT)
                ->timeout(self::FETCH_TIMEOUT)
                ->connectTimeout(10)
                ->get(self::GOOGLE_NEWS_ENDPOINT, [
                    'q' => $query,
                    'hl' => $language,
                    'gl' => $country,
                    'ceid' => $country . ':' . $language,
                ]);

            if (! $response->successful()) {
                return $this->failed('google-news', $query, 'HTTP ' . $response->status());
            }

            $articles = array_slice(
                $this->parseGoogleNewsRss((string) $response->body()),
                0,
                max(1, min(100, (int) setting('grimba_google_news_max_records', 30)))
            );

            return $this->ingestMany('google-news', $query, $articles);
        } catch (Throwable $e) {
            Log::warning('[GrimbaLiveNewsFetcher] Google News RSS call failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return $this->failed('google-news', $query, $e->getMessage());
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseGoogleNewsRss(string $xml): array
    {
        libxml_use_internal_errors(true);
        $root = simplexml_load_string($xml);
        if ($root === false || ! isset($root->channel)) {
            libxml_clear_errors();

            return [];
        }

        $items = [];
        foreach ($root->channel->item as $node) {
            $link = trim((string) $node->link);
            $title = trim((string) $node->title);
            $sourceName = trim((string) ($node->source ?? ''));
            $sourceUrl = isset($node->source) ? trim((string) $node->source['url']) : '';
            $description = trim(html_entity_decode(strip_tags((string) ($node->description ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $description = preg_replace('/\s+/u', ' ', $description) ?? $description;

            if ($sourceName !== '') {
                $title = preg_replace('/\s+-\s+' . preg_quote($sourceName, '/') . '$/u', '', $title) ?: $title;
            }

            $items[] = [
                'provider_item_id' => $link !== '' ? sha1($link) : null,
                'url' => $link,
                'title' => $title,
                'description' => $description,
                'content' => $description,
                'image' => '',
                'source_name' => $sourceName ?: ($this->hostFromUrl($sourceUrl) ?: $this->hostFromUrl($link)),
                'source_domain' => $this->hostFromUrl($sourceUrl) ?: '',
                'source_country' => null,
                'language' => null,
                'published_at' => $this->toIso($node->pubDate ?? null),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}>
     */
    private function fetchMediastack(): array
    {
        $key = trim((string) setting('grimba_mediastack_key', env('MEDIASTACK_KEY', '')));
        if ($key === '') {
            return [$this->skipped('mediastack', '-', 'MEDIASTACK_KEY not set.')];
        }

        $keywords = (string) setting('grimba_mediastack_keywords', 'breaking,africa,afrique,politics,economy,climate,war');
        $languages = (string) setting('grimba_mediastack_languages', 'fr,en');

        try {
            $response = Http::withUserAgent(self::USER_AGENT)
                ->timeout(self::FETCH_TIMEOUT)
                ->connectTimeout(10)
                ->get(self::MEDIASTACK_ENDPOINT, [
                    'access_key' => $key,
                    'keywords' => $keywords,
                    'languages' => $languages,
                    'sort' => 'published_desc',
                    'limit' => max(1, min(100, (int) setting('grimba_mediastack_limit', 50))),
                ]);

            if (! $response->successful()) {
                return [$this->failed('mediastack', $keywords, 'HTTP ' . $response->status())];
            }

            $articles = (array) ($response->json('data') ?? []);

            return [$this->ingestMany('mediastack', $keywords, array_map(
                fn (array $article): array => $this->normaliseMediastackArticle($article),
                $articles
            ))];
        } catch (Throwable $e) {
            Log::warning('[GrimbaLiveNewsFetcher] Mediastack call failed', [
                'error' => $e->getMessage(),
            ]);

            return [$this->failed('mediastack', $keywords, $e->getMessage())];
        }
    }

    /**
     * @param array<int, array<string, mixed>> $articles
     * @return array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    private function ingestMany(string $provider, string $query, array $articles): array
    {
        $ingested = 0;
        $deduped = 0;
        $skipped = 0;

        foreach ($articles as $article) {
            $result = $this->ingestArticle($provider, $article);
            if ($result === 'ingested') {
                $ingested++;
            } elseif ($result === 'duplicate') {
                $deduped++;
            } else {
                $skipped++;
            }
        }

        return [
            'provider' => $provider,
            'query' => $query,
            'status' => 'ok',
            'returned' => count($articles),
            'ingested' => $ingested,
            'deduped' => $deduped,
            'skipped' => $skipped,
            'error' => null,
        ];
    }

    /**
     * @return 'ingested'|'duplicate'|'skipped'
     */
    private function ingestArticle(string $provider, array $article): string
    {
        $url = (string) ($article['url'] ?? '');
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return 'skipped';
        }

        $hash = sha1($url);
        if ($this->hasSeenUrl($url, $hash)) {
            return 'duplicate';
        }

        $title = trim((string) ($article['title'] ?? ''));
        if ($title === '') {
            return 'skipped';
        }

        $sourceId = $this->resolveSourceId(
            (string) ($article['source_name'] ?? ''),
            $url,
            (string) ($article['source_domain'] ?? ''),
            $article['source_country'] ?? null,
            $article['language'] ?? null,
            $provider
        );

        $sourceCountry = $sourceId
            ? DB::table('news_sources')->where('id', $sourceId)->value('country')
            : ($article['source_country'] ?? null);

        try {
            DB::beginTransaction();

            $postId = $this->createPost($provider, $article, $sourceId, $sourceCountry);
            if ($postId === null) {
                DB::rollBack();
                return 'skipped';
            }

            if (Schema::hasTable('grimba_live_news_items')) {
                DB::table('grimba_live_news_items')->insert([
                    'provider' => $provider,
                    'provider_item_id' => $article['provider_item_id'] ?? null,
                    'source_id' => $sourceId,
                    'source_name' => Str::limit((string) ($article['source_name'] ?? ''), 191, '') ?: null,
                    'source_domain' => Str::limit((string) ($article['source_domain'] ?? ''), 191, '') ?: null,
                    'source_country' => $sourceCountry,
                    'article_url' => Str::limit($url, 2040, ''),
                    'article_url_hash' => $hash,
                    'post_id' => $postId,
                    'published_at' => $article['published_at'] ?? null,
                    'fetched_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return 'ingested';
        } catch (Throwable $e) {
            DB::rollBack();
            Log::warning('[GrimbaLiveNewsFetcher] ingest failed', [
                'provider' => $provider,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return 'skipped';
        }
    }

    private function hasSeenUrl(string $url, string $hash): bool
    {
        if (Schema::hasTable('grimba_live_news_items')
            && DB::table('grimba_live_news_items')->where('article_url_hash', $hash)->exists()) {
            return true;
        }

        if (Schema::hasTable('newsapi_items')
            && DB::table('newsapi_items')->where('article_url_hash', $hash)->exists()) {
            return true;
        }

        if (Schema::hasTable('rss_feed_items') && Schema::hasColumn('rss_feed_items', 'canonical_url_hash')) {
            $canonicalHash = app(GrimbaUrlCanonicalizer::class)->hash($url);
            if ($canonicalHash
                && DB::table('rss_feed_items')->where('canonical_url_hash', $canonicalHash)->exists()) {
                return true;
            }
        }

        return false;
    }

    private function createPost(string $provider, array $article, ?int $sourceId, ?string $sourceCountry): ?int
    {
        try {
            $url = (string) $article['url'];
            $description = GrimbaArticleText::stripNewsApiTruncationMarker(
                (string) ($article['description'] ?? '')
            ) ?? '';
            $content = GrimbaArticleText::stripNewsApiTruncationMarker(
                (string) ($article['content'] ?? $description)
            ) ?? '';

            $body = trim(strip_tags($content ?: $description));
            if ($body === '') {
                $body = 'Couverture repérée par GrimbaNews via ' . strtoupper($provider)
                    . '. Le texte complet est récupéré automatiquement après publication.';
            }

            $post = new Post();
            $post->name = Str::limit((string) $article['title'], 240, '');
            $post->description = Str::limit(strip_tags($description ?: $body), 600, '…');
            $post->content = '<p><a href="' . e($url) . '" target="_blank" rel="noopener">Lire l’article original</a></p>'
                . '<p>' . e(Str::limit($body, 1400, '…')) . '</p>';

            $autoPublish = $this->autoPublish();
            $post->status = $autoPublish ? 'published' : 'draft';
            if ($autoPublish && Schema::hasColumn('posts', 'published_at')) {
                $post->published_at = now();
            }

            $post->author_id = 1;
            $post->author_type = \Botble\ACL\Models\User::class;
            $post->is_featured = false;

            if ($sourceId) {
                $post->source_id = $sourceId;
            }
            if (! empty($article['source_name'])) {
                $post->source_name = (string) $article['source_name'];
            }
            if (! empty($article['published_at'])) {
                $post->created_at = $article['published_at'];
            }
            if (! empty($article['image']) && filter_var((string) $article['image'], FILTER_VALIDATE_URL)) {
                $post->image = (string) $article['image'];
            }

            $this->applyImageProvenance(
                $post,
                $url,
                ! empty($article['image']) ? $provider : null,
                ! empty($article['image']) ? null : 'no provider image'
            );

            if (empty($post->story_cluster_id)) {
                $clusterId = GrimbaRssPoller::findOrFormCluster(
                    (string) $post->name,
                    30,
                    0.30,
                    false,
                    $post->translated_name ?? null,
                );
                if ($clusterId !== null) {
                    $post->story_cluster_id = $clusterId;
                }
            }

            $post->save();

            Slug::create([
                'key' => $this->uniqueSlug($post->name),
                'reference_id' => $post->id,
                'reference_type' => Post::class,
                'prefix' => SlugHelper::getPrefix(Post::class) ?? '',
            ]);

            $this->classifyPost($post, $sourceCountry);

            return (int) $post->id;
        } catch (Throwable $e) {
            Log::warning('[GrimbaLiveNewsFetcher] createPost failed', [
                'provider' => $provider,
                'title' => $article['title'] ?? '?',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function classifyPost(Post $post, ?string $sourceCountry): void
    {
        try {
            $categoryIds = app(GrimbaCategoryClassifier::class)->classify(
                (string) $post->name,
                $post->description,
                $post->source_name,
                $sourceCountry
            );

            foreach ($categoryIds as $categoryId) {
                DB::table('post_categories')->insertOrIgnore([
                    'category_id' => $categoryId,
                    'post_id' => $post->id,
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('[GrimbaLiveNewsFetcher] category classification failed', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function autoPublish(): bool
    {
        $autoPublish = true;
        if (is_callable('setting')) {
            $autoPublish = (bool) setting('grimba_ingest_auto_publish', true);
        }

        $env = env('GRIMBA_INGEST_AUTO_PUBLISH', null);
        if ($env !== null) {
            $autoPublish = filter_var($env, FILTER_VALIDATE_BOOLEAN);
        }

        return $autoPublish;
    }

    private function resolveSourceId(
        string $sourceName,
        string $articleUrl,
        string $sourceDomain,
        mixed $sourceCountry,
        mixed $language,
        string $provider
    ): ?int {
        $domain = $sourceDomain !== '' ? $this->normaliseHost($sourceDomain) : $this->hostFromUrl($articleUrl);
        $name = trim($sourceName) !== '' ? trim($sourceName) : ($domain ?: '');
        if ($name === '') {
            return null;
        }
        $storedName = Str::limit($name, 118, '');

        $existing = DB::table('news_sources')
            ->where('name', $storedName)
            ->first(['id', 'country']);
        if ($existing) {
            $this->backfillCountryIfMissing((int) $existing->id, $name, $domain, $sourceCountry);

            return (int) $existing->id;
        }

        if ($domain !== '') {
            $byDomain = $this->sourceByDomain($domain);
            if ($byDomain) {
                $this->backfillCountryIfMissing((int) $byDomain->id, (string) $byDomain->name, $domain, $sourceCountry);

                return (int) $byDomain->id;
            }
        }

        $country = $this->normaliseCountry($sourceCountry)
            ?: $this->inferredCountry($name, $domain ? 'https://' . $domain : $articleUrl);
        $slug = $this->uniqueSourceSlug($storedName);
        $now = now();

        $insert = [
            'name' => $storedName,
            'website' => $domain ? 'https://' . $domain : null,
            'slug' => $slug,
            'bias_rating' => 'unknown',
            'ownership_type' => null,
            'credibility_score' => null,
            'country' => $country,
            'language' => $this->normaliseLanguage($language),
            'description' => 'Source créée automatiquement par le flux live ' . strtoupper($provider) . '. À enrichir.',
            'notes' => 'auto-created by GrimbaLiveNewsFetcher',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('news_sources', 'api_id')) {
            $insert['api_id'] = null;
        }

        return (int) DB::table('news_sources')->insertGetId($insert);
    }

    private function sourceByDomain(string $domain): ?object
    {
        return DB::table('news_sources')
            ->whereNotNull('website')
            ->get(['id', 'name', 'website', 'country'])
            ->first(function (object $source) use ($domain): bool {
                $host = $this->hostFromUrl((string) $source->website);

                return $host === $domain || ($host !== null && str_ends_with($host, '.' . $domain));
            });
    }

    private function backfillCountryIfMissing(int $sourceId, string $name, ?string $domain, mixed $sourceCountry): void
    {
        $current = DB::table('news_sources')->where('id', $sourceId)->value('country');
        if (trim((string) $current) !== '') {
            return;
        }

        $country = $this->normaliseCountry($sourceCountry)
            ?: $this->inferredCountry($name, $domain ? 'https://' . $domain : null);
        if ($country === null) {
            return;
        }

        DB::table('news_sources')->where('id', $sourceId)->update([
            'country' => $country,
            'updated_at' => now(),
        ]);
    }

    private function inferredCountry(string $name, ?string $website): ?string
    {
        $inferred = GrimbaSourceCountryBackfill::infer($name, $website, null);

        return $inferred && $inferred['confidence'] >= 80 ? $inferred['country'] : null;
    }

    private function normaliseCountry(mixed $country): ?string
    {
        $raw = trim((string) $country);
        if ($raw === '') {
            return null;
        }

        $normalised = GrimbaSourceCountryBackfill::normalizeCountry($raw);
        if ($normalised !== null) {
            return $normalised;
        }

        $map = [
            'algeria' => 'DZ',
            'cameroon' => 'CM',
            'canada' => 'CA',
            'egypt' => 'EG',
            'france' => 'FR',
            'ghana' => 'GH',
            'kenya' => 'KE',
            'morocco' => 'MA',
            'nigeria' => 'NG',
            'qatar' => 'QA',
            'senegal' => 'SN',
            'south africa' => 'ZA',
            'tunisia' => 'TN',
            'united kingdom' => 'GB',
            'united states' => 'US',
        ];

        $key = mb_strtolower($raw);

        return $map[$key] ?? null;
    }

    private function normaliseLanguage(mixed $language): ?string
    {
        $language = mb_strtolower(trim((string) $language));
        if ($language === '') {
            return null;
        }

        return preg_match('/^[a-z]{2,5}$/', $language) ? $language : null;
    }

    private function normaliseGdeltArticle(array $article): array
    {
        $url = (string) ($article['url'] ?? '');
        $domain = (string) ($article['domain'] ?? '');

        return [
            'provider_item_id' => $url !== '' ? sha1($url) : null,
            'url' => $url,
            'title' => (string) ($article['title'] ?? ''),
            'description' => (string) ($article['description'] ?? ''),
            'content' => (string) ($article['description'] ?? ''),
            'image' => (string) ($article['socialimage'] ?? $article['image'] ?? ''),
            'source_name' => $domain !== '' ? $domain : $this->hostFromUrl($url),
            'source_domain' => $domain,
            'source_country' => $article['sourcecountry'] ?? $article['sourceCountry'] ?? null,
            'language' => $article['language'] ?? null,
            'published_at' => $this->toIso($article['seendate'] ?? $article['publishedAt'] ?? null),
        ];
    }

    private function normaliseMediastackArticle(array $article): array
    {
        $url = (string) ($article['url'] ?? '');

        return [
            'provider_item_id' => $url !== '' ? sha1($url) : null,
            'url' => $url,
            'title' => (string) ($article['title'] ?? ''),
            'description' => (string) ($article['description'] ?? ''),
            'content' => (string) ($article['description'] ?? ''),
            'image' => (string) ($article['image'] ?? ''),
            'source_name' => (string) ($article['source'] ?? ''),
            'source_domain' => $this->hostFromUrl($url),
            'source_country' => $article['country'] ?? null,
            'language' => $article['language'] ?? null,
            'published_at' => $this->toIso($article['published_at'] ?? null),
        ];
    }

    private function toIso(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->toDateTimeString();
        } catch (Throwable) {
            return null;
        }
    }

    private function hostFromUrl(?string $url): ?string
    {
        $raw = trim((string) $url);
        if ($raw === '') {
            return null;
        }

        $url = preg_match('#^https?://#i', $raw) ? $raw : 'https://' . ltrim($raw, '/');
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        return $this->normaliseHost($host);
    }

    private function normaliseHost(string $host): string
    {
        $host = mb_strtolower(trim($host));
        $host = preg_replace('/^(www|m|amp)\./', '', $host) ?: $host;

        return trim($host, '.');
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
                $slug = Str::limit($base, 188, '') . '-' . Str::random(6);
                break;
            }
        }

        return $slug;
    }

    private function uniqueSourceSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'source-' . substr(sha1($name), 0, 8);
        $base = Str::limit($base, 180, '');
        $slug = $base;
        $i = 2;

        while (DB::table('news_sources')->where('slug', $slug)->exists()) {
            $slug = Str::limit($base, 174, '') . '-' . $i;
            $i++;
        }

        return $slug;
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

    /**
     * @return array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    private function skipped(string $provider, string $query, string $reason): array
    {
        return [
            'provider' => $provider,
            'query' => $query,
            'status' => 'skipped',
            'returned' => 0,
            'ingested' => 0,
            'deduped' => 0,
            'skipped' => 0,
            'error' => $reason,
        ];
    }

    /**
     * @return array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    private function failed(string $provider, string $query, string $error): array
    {
        return [
            'provider' => $provider,
            'query' => $query,
            'status' => 'failed',
            'returned' => 0,
            'ingested' => 0,
            'deduped' => 0,
            'skipped' => 0,
            'error' => Str::limit($error, 160),
        ];
    }
}
