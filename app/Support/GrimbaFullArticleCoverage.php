<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GrimbaFullArticleCoverage
{
    public static function recent(mixed $since, int $retryAfterHours = 24): object
    {
        if (! Schema::hasTable('posts') || ! Schema::hasColumn('posts', 'full_content')) {
            return self::empty('full article columns unavailable');
        }

        $postIds = self::recentUpstreamPostIds($since);
        $total = $postIds->count();

        if ($total === 0) {
            return self::empty('no recent upstream-backed publications');
        }

        $rows = DB::table('posts')
            ->whereIn('id', $postIds->all())
            ->get(['id', 'name', 'content', 'full_content', 'full_fetched_at', 'full_extract_error']);

        $retryCutoff = now()->subHours(max(0, $retryAfterHours));
        $readable = 0;
        $ingestFallbackReadable = 0;
        $missing = 0;
        $neverAttempted = 0;
        $failed = 0;
        $retryDeferred = 0;
        $retryReady = 0;
        $latestFetchedAt = null;
        $missingSamples = [];

        foreach ($rows as $row) {
            $body = GrimbaArticleText::readableBody($row);
            if ($body) {
                $readable++;
                if ($body->source !== 'full') {
                    $ingestFallbackReadable++;
                }
                continue;
            }

            $missing++;
            $missingSamples[] = [
                'id' => (int) $row->id,
                'name' => (string) ($row->name ?? ''),
                'error' => $row->full_extract_error ?: null,
            ];

            if (! $row->full_fetched_at) {
                $neverAttempted++;
                continue;
            }

            $fetchedAt = self::parseCarbon($row->full_fetched_at);
            if ($fetchedAt && ($latestFetchedAt === null || $fetchedAt->gt($latestFetchedAt))) {
                $latestFetchedAt = $fetchedAt;
            }

            if ($row->full_extract_error) {
                $failed++;
            }

            if ($fetchedAt && $retryAfterHours > 0 && $fetchedAt->gt($retryCutoff)) {
                $retryDeferred++;
            } else {
                $retryReady++;
            }
        }

        return (object) [
            'available' => true,
            'reason' => null,
            'window_since' => $since,
            'total' => $total,
            'readable' => $readable,
            'ingest_fallback_readable' => $ingestFallbackReadable,
            'missing' => $missing,
            'coverage_pct' => (int) round($readable * 100 / max(1, $total)),
            'never_attempted' => $neverAttempted,
            'failed' => $failed,
            'retry_deferred' => $retryDeferred,
            'retry_ready' => $retryReady,
            'latest_fetched_at' => $latestFetchedAt,
            'missing_samples' => array_slice($missingSamples, 0, 3),
            'min_readable_chars' => GrimbaArticleText::MIN_READABLE_CHARS,
            'retry_after_hours' => $retryAfterHours,
        ];
    }

    private static function empty(string $reason): object
    {
        return (object) [
            'available' => false,
            'reason' => $reason,
            'total' => 0,
            'readable' => 0,
            'ingest_fallback_readable' => 0,
            'missing' => 0,
            'coverage_pct' => null,
            'never_attempted' => 0,
            'failed' => 0,
            'retry_deferred' => 0,
            'retry_ready' => 0,
            'latest_fetched_at' => null,
            'missing_samples' => [],
            'min_readable_chars' => GrimbaArticleText::MIN_READABLE_CHARS,
            'retry_after_hours' => 0,
        ];
    }

    private static function recentUpstreamPostIds(mixed $since): Collection
    {
        $queries = [];

        if (Schema::hasTable('rss_feed_items')) {
            $rss = DB::table('posts')
                ->join('rss_feed_items', 'rss_feed_items.post_id', '=', 'posts.id')
                ->where('posts.status', 'published')
                ->whereNotNull('rss_feed_items.post_id')
                ->whereNotNull('rss_feed_items.link')
                ->select('posts.id');

            GrimbaPostRecency::wherePublishedSince($rss, $since);
            $queries[] = $rss;
        }

        if (Schema::hasTable('newsapi_items')) {
            $newsApi = DB::table('posts')
                ->join('newsapi_items', 'newsapi_items.post_id', '=', 'posts.id')
                ->where('posts.status', 'published')
                ->whereNotNull('newsapi_items.post_id')
                ->whereNotNull('newsapi_items.article_url')
                ->select('posts.id');

            GrimbaPostRecency::wherePublishedSince($newsApi, $since);
            $queries[] = $newsApi;
        }

        if ($queries === []) {
            return collect();
        }

        $union = array_shift($queries);
        foreach ($queries as $query) {
            $union->union($query);
        }

        return DB::query()
            ->fromSub($union, 'full_article_posts')
            ->distinct()
            ->pluck('id');
    }

    private static function parseCarbon(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
