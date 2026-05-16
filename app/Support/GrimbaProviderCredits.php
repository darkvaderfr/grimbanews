<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Provider-agnostic daily credit counter — Vader 2026-05-16.
 *
 * Built for newsdata.io's 200-credit/day cap but reusable for any
 * external provider that has a quota. The authoritative number lives
 * in grimba_live_news_provider_runs (counted by provider + day). The
 * cache is a hot-path fast-guard that survives until UTC midnight.
 *
 * Pattern:
 *   GrimbaProviderCredits::fast('newsdata-io')   // pre-flight skip check
 *   GrimbaProviderCredits::bump('newsdata-io')   // after every consumed call
 */
class GrimbaProviderCredits
{
    private const CACHE_TTL_HOURS = 36; // survives DST + timezone slop
    private const CACHE_PREFIX = 'grimba_provider_credits';

    /**
     * Authoritative count from the provider-runs telemetry table for
     * the current UTC day. Falls back to 0 when the table is missing
     * (e.g. fresh install pre-migration).
     */
    public static function used(string $provider, ?Carbon $since = null): int
    {
        if (! Schema::hasTable('grimba_live_news_provider_runs')) {
            return 0;
        }

        $since = $since ?: now()->utc()->startOfDay();

        return (int) DB::table('grimba_live_news_provider_runs')
            ->where('provider', $provider)
            ->where('status', '!=', 'skipped')
            ->where('started_at', '>=', $since)
            ->count();
    }

    /**
     * Best-effort cached daily counter. Each call to bump() increments
     * this for the current UTC date.
     */
    public static function cached(string $provider): int
    {
        return (int) Cache::get(self::key($provider), 0);
    }

    /**
     * Fast pre-flight check used by fetchers before consuming a credit.
     * Returns max(DB, cache) so we never under-count after a cache flush.
     */
    public static function fast(string $provider): int
    {
        return max(self::used($provider), self::cached($provider));
    }

    /**
     * Increment the cached daily counter. Should be called once per
     * consumed upstream request, regardless of success. The DB row in
     * grimba_live_news_provider_runs is what eventually rules; this
     * keeps cheap pre-flight checks accurate within the same minute.
     */
    public static function bump(string $provider): void
    {
        $key = self::key($provider);
        if (! Cache::has($key)) {
            // Seed at 0 with TTL — Cache::increment alone won't set TTL on first call.
            Cache::put($key, 0, now()->addHours(self::CACHE_TTL_HOURS));
        }
        Cache::increment($key);
    }

    /**
     * Test-only reset. Wipes the cache key for the current UTC day.
     */
    public static function reset(string $provider): void
    {
        Cache::forget(self::key($provider));
    }

    private static function key(string $provider): string
    {
        return self::CACHE_PREFIX . ':' . $provider . ':' . now()->utc()->toDateString();
    }
}
