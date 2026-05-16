<?php

namespace App\Services;

use App\Support\GrimbaProviderCredits;
use Illuminate\Support\Facades\Log;

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

        if ($this->creditsRemainingToday() <= 0) {
            return [$this->skipped('-', 'newsdata.io daily credit budget reached.')];
        }

        // S-NDI-06 (next sprint) wires the HTTP call + normaliser; until
        // then we surface a clear "not implemented" sentinel so the
        // admin "Run now" button + cron lines fail loudly rather than
        // silently consuming credits.
        Log::info('[GrimbaNewsdataIoFetcher] fetch invoked — HTTP layer not yet implemented (S-NDI-06).', [
            'queries_configured' => count($this->queries()),
            'credits_used_today' => GrimbaProviderCredits::fast(self::PROVIDER),
            'credits_remaining'  => $this->creditsRemainingToday(),
        ]);

        return [$this->skipped('-', 'newsdata.io HTTP fetcher pending (S-NDI-06).')];
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
