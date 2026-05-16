<?php

namespace App\Services;

use App\Support\GrimbaProviderCredits;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * newsdata.io free-tier breaking-news fetcher — Vader 2026-05-16.
 *
 * Free plan: 200 credits/day, 10 articles per call. We default the
 * budget to 190 to leave operator headroom for "Run now" buttons.
 *
 * Sprint scaffold (S-NDI-05): public contract + config readers only.
 * The HTTP call + normaliser land in S-NDI-06; the credit-accounting
 * pre-flight check lands in S-NDI-07. Until then `fetch()` returns a
 * stable `skipped` row so the dispatcher in `GrimbaLiveNewsFetcher`
 * stays green when the provider is enabled.
 *
 * @see docs/GRIMBANEWS_NEWSDATAIO_INTEGRATION_PLAN.md
 */
class GrimbaNewsdataIoFetcher
{
    public const PROVIDER = 'newsdata-io';

    private const DEFAULT_DAILY_BUDGET = 190;
    private const DEFAULT_MAX_CALLS_PER_RUN = 2;
    private const DEFAULT_PAGE_SIZE = 10;
    private const DEFAULT_LANGUAGES = 'fr,en';
    private const DEFAULT_COUNTRIES = 'fr,sn,ci,ml,cm';
    private const DEFAULT_CATEGORIES = 'top,politics,world';
    private const USER_AGENT = 'GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)';

    public function __construct(
        private readonly GrimbaLiveNewsFetcher $pipeline,
    ) {
    }

    /**
     * @param array<int, string>|null $queries Override queries from settings.
     * @return array<int, array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}>
     */
    public function fetch(?array $queries = null): array
    {
        if (! $this->isActive()) {
            return [$this->skipped('-', 'newsdata.io provider is disabled in settings.')];
        }

        if (! $this->isConfigured()) {
            return [$this->skipped('-', 'newsdata.io API key is not configured.')];
        }

        $queries = $queries ?: $this->queries();
        if (! $queries) {
            return [$this->skipped('-', 'No newsdata.io queries configured.')];
        }

        // S-NDI-08 — round-robin starting index based on credits-used so
        // that the rotation surfaces all configured queries across a day
        // even if cron ticks lose calls to budget.
        $startIdx = $this->creditsUsedToday() % count($queries);
        $maxCalls = min($this->maxCallsPerRun(), count($queries), $this->creditsRemainingToday());

        if ($maxCalls <= 0) {
            return [$this->skipped('-', 'newsdata.io daily credit budget reached.')];
        }

        $summary = [];
        for ($i = 0; $i < $maxCalls; $i++) {
            $query = $queries[($startIdx + $i) % count($queries)];
            $summary[] = $this->fetchQuery($query);
        }

        return $summary;
    }

    /**
     * @return array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    public function fetchQuery(string $query): array
    {
        // S-NDI-07 — pre-flight credit check. DB is canonical; cache is
        // hot-path fast guard. Either flag stops the call before we
        // consume a credit.
        if (GrimbaProviderCredits::fast(self::PROVIDER) >= $this->dailyCreditBudget()) {
            return $this->skipped($query, 'newsdata.io daily credit budget reached.');
        }

        $started = now();
        $startedAt = microtime(true);
        $runId = $this->pipeline->startLiveRun(self::PROVIDER, $query, $started);

        try {
            $params = $this->buildParams($query);

            $response = Http::withUserAgent(self::USER_AGENT)
                ->timeout($this->timeoutSeconds())
                ->connectTimeout($this->connectTimeoutSeconds())
                ->get($this->baseUrl() . '/latest', $params);

            // Credit consumed regardless of outcome — bump immediately so
            // a hot-loop bug can't burn the budget. Skipped pre-flight
            // checks short-circuit before this line.
            GrimbaProviderCredits::bump(self::PROVIDER);

            if (! $response->successful()) {
                $summary = $this->pipeline->failed(self::PROVIDER, $query, 'HTTP ' . $response->status());
                $this->pipeline->finishLiveRun($runId, $summary, $startedAt);

                return $summary;
            }

            $body = $response->json();
            $status = (string) (data_get($body, 'status') ?? '');

            // newsdata.io returns HTTP 200 with status='error' on auth /
            // rate-limit failures. Treat those as failed runs.
            if ($status !== '' && $status !== 'success') {
                $error = (string) (
                    data_get($body, 'results.message')
                    ?: data_get($body, 'message')
                    ?: data_get($body, 'code')
                    ?: 'newsdata.io returned status="' . $status . '"'
                );
                $summary = $this->pipeline->failed(self::PROVIDER, $query, $error);
                $this->pipeline->finishLiveRun($runId, $summary, $startedAt);

                return $summary;
            }

            $results = (array) (data_get($body, 'results') ?: []);
            $articles = array_map(
                fn (array $a): array => $this->normaliseArticle($a),
                array_values(array_filter($results, 'is_array'))
            );
            $articles = array_values(array_filter($articles, fn (array $a): bool => $a['url'] !== '' && $a['title'] !== ''));

            $summary = $this->pipeline->ingestMany(self::PROVIDER, $query, $articles);
            $this->pipeline->finishLiveRun($runId, $summary, $startedAt);

            return $summary;
        } catch (Throwable $e) {
            Log::warning('[GrimbaNewsdataIoFetcher] call failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            $summary = $this->pipeline->failed(self::PROVIDER, $query, $e->getMessage());
            $this->pipeline->finishLiveRun($runId, $summary, $startedAt);

            return $summary;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildParams(string $query): array
    {
        $params = [
            'apikey' => $this->apiKey(),
            'q'      => $query,
            'size'   => $this->pageSize(),
        ];
        if ($languages = $this->languages()) {
            $params['language'] = implode(',', $languages);
        }
        if ($countries = $this->countries()) {
            $params['country'] = implode(',', $countries);
        }
        if ($categories = $this->categories()) {
            $params['category'] = implode(',', $categories);
        }

        return $params;
    }

    /**
     * Normalise a newsdata.io result row into the shape ingestMany() expects.
     *
     * @param array<string, mixed> $article
     * @return array<string, mixed>
     */
    public function normaliseArticle(array $article): array
    {
        $url = (string) (data_get($article, 'link') ?: '');
        $title = (string) (data_get($article, 'title') ?: '');
        $description = (string) (data_get($article, 'description') ?: '');
        $content = (string) (data_get($article, 'content') ?: $description);

        $sourceName = (string) (
            data_get($article, 'source_name')
            ?: data_get($article, 'source_id')
            ?: $this->pipeline->hostFromUrl($url)
            ?: ''
        );
        $sourceDomain = (string) (
            data_get($article, 'source_url')
            ?: $this->pipeline->hostFromUrl($url)
            ?: ''
        );

        $country = data_get($article, 'country.0') ?: data_get($article, 'country');
        $language = data_get($article, 'language');
        $providerArticleId = (string) (data_get($article, 'article_id') ?: ($url !== '' ? sha1($url) : ''));

        return [
            'provider_item_id' => 'newsdata-io:' . $providerArticleId,
            'url' => $url,
            'title' => Str::limit($title, 240, ''),
            'description' => Str::limit(strip_tags($description), 600, '…'),
            'content' => $content,
            'image' => (string) (data_get($article, 'image_url') ?: ''),
            'source_name' => $sourceName,
            'source_domain' => $sourceDomain,
            'source_country' => is_string($country) ? strtoupper($country) : null,
            'language' => $this->normaliseLanguage($language),
            'published_at' => $this->pipeline->toIso(data_get($article, 'pubDate')),
        ];
    }

    /**
     * newsdata.io returns full language names ("english") on some endpoints
     * and ISO-2 codes ("en") on others. Normalise to ISO-2 so downstream
     * filters (posts.language reader-facing) behave consistently.
     */
    private function normaliseLanguage(mixed $language): ?string
    {
        $raw = strtolower(trim((string) $language));
        if ($raw === '') {
            return null;
        }

        $map = [
            'english'     => 'en',
            'french'      => 'fr',
            'français'    => 'fr',
            'francais'    => 'fr',
            'spanish'     => 'es',
            'español'     => 'es',
            'portuguese'  => 'pt',
            'arabic'      => 'ar',
            'german'      => 'de',
            'italian'     => 'it',
            'russian'     => 'ru',
            'mandarin'    => 'zh',
            'chinese'     => 'zh',
            'japanese'    => 'ja',
            'korean'      => 'ko',
            'turkish'     => 'tr',
            'dutch'       => 'nl',
            'swahili'     => 'sw',
            'yoruba'      => 'yo',
            'hausa'       => 'ha',
            'amharic'     => 'am',
            'wolof'       => 'wo',
        ];

        return $map[$raw] ?? (preg_match('/^[a-z]{2,5}$/', $raw) ? $raw : null);
    }

    public function isConfigured(): bool
    {
        return trim($this->apiKey()) !== '';
    }

    public function isActive(): bool
    {
        $envOverride = env('NEWSDATA_IO_KEY', null);

        return (bool) setting('grimba_newsdata_io_active', $envOverride !== null && $envOverride !== '');
    }

    public function apiKey(): string
    {
        $fromSetting = (string) setting('grimba_newsdata_io_key', '');

        return $fromSetting !== '' ? $fromSetting : (string) env('NEWSDATA_IO_KEY', '');
    }

    public function baseUrl(): string
    {
        return rtrim((string) env('NEWSDATA_IO_BASE_URL', 'https://newsdata.io/api/1'), '/');
    }

    public function dailyCreditBudget(): int
    {
        $raw = (int) setting('grimba_newsdata_io_daily_credit_budget', self::DEFAULT_DAILY_BUDGET);

        return max(1, min(200, $raw));
    }

    public function maxCallsPerRun(): int
    {
        $raw = (int) setting('grimba_newsdata_io_max_calls_per_run', self::DEFAULT_MAX_CALLS_PER_RUN);

        return max(1, min(6, $raw));
    }

    public function pageSize(): int
    {
        $raw = (int) setting('grimba_newsdata_io_page_size', self::DEFAULT_PAGE_SIZE);

        return max(1, min(10, $raw));
    }

    /**
     * @return array<int, string>
     */
    public function queries(): array
    {
        $raw = (string) setting('grimba_newsdata_io_queries', $this->defaultQueries());

        return collect(explode("\n", str_replace(',', "\n", $raw)))
            ->map(fn (string $q): string => trim($q))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function languages(): array
    {
        return $this->parseCsv((string) setting('grimba_newsdata_io_languages', self::DEFAULT_LANGUAGES), 5);
    }

    /**
     * @return array<int, string>
     */
    public function countries(): array
    {
        return $this->parseCsv((string) setting('grimba_newsdata_io_countries', self::DEFAULT_COUNTRIES), 5);
    }

    /**
     * @return array<int, string>
     */
    public function categories(): array
    {
        return $this->parseCsv((string) setting('grimba_newsdata_io_categories', self::DEFAULT_CATEGORIES), 8);
    }

    public function timeoutSeconds(): int
    {
        return max(2, min(60, (int) setting('grimba_newsdata_io_timeout', 12)));
    }

    public function connectTimeoutSeconds(): int
    {
        return max(1, min(30, (int) setting('grimba_newsdata_io_connect_timeout', 5)));
    }

    public function creditsUsedToday(): int
    {
        return GrimbaProviderCredits::fast(self::PROVIDER);
    }

    public function creditsRemainingToday(): int
    {
        return max(0, $this->dailyCreditBudget() - $this->creditsUsedToday());
    }

    public function plannedCallCount(): int
    {
        return min($this->maxCallsPerRun(), max(0, count($this->queries())));
    }

    private function defaultQueries(): string
    {
        return implode("\n", [
            'breaking news Africa',
            'élection politique Afrique',
            'breaking news Europe',
            'dernière minute France',
            'breaking news Americas',
            'última hora América Latina',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function parseCsv(string $raw, int $cap): array
    {
        return collect(explode(',', $raw))
            ->map(fn (string $v): string => strtolower(trim($v)))
            ->filter()
            ->unique()
            ->take($cap)
            ->values()
            ->all();
    }

    /**
     * @return array{provider:string, query:string, status:string, returned:int, ingested:int, deduped:int, skipped:int, error:?string}
     */
    private function skipped(string $query, string $reason): array
    {
        return [
            'provider' => self::PROVIDER,
            'query'    => $query,
            'status'   => 'skipped',
            'returned' => 0,
            'ingested' => 0,
            'deduped'  => 0,
            'skipped'  => 0,
            'error'    => $reason,
        ];
    }
}
