<?php

namespace App\Console\Commands;

use App\Support\GrimbaPostPublisher;
use App\Support\GrimbaPostRecency;
use App\Support\GrimbaEditorialCategoryFreshness;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrimbaEnsureDailyPublish extends Command
{
    protected $signature = 'grimba:ensure-daily-publish
        {--min=12 : minimum published posts required in the freshness window}
        {--window-hours=24 : freshness window in hours}
        {--lookback-hours=168 : only promote drafts whose upstream date is within this many hours}
        {--threshold= : trusted credibility_score minimum (default: setting / 70)}
        {--min-age-minutes=10 : minimum draft age before emergency promotion}
        {--max-publish=60 : maximum posts to publish in one remediation run}
        {--per-category-min=0 : minimum published posts required per tracked editorial category, 0 disables}
        {--category-window-hours= : editorial category freshness window in hours, default: window-hours}
        {--categories=all : category scope: all, editions, topics, or comma-separated category names}
        {--max-publish-per-category=5 : maximum posts to publish per stale category in one remediation run}
        {--dry-run : preview without writing}';

    protected $description = 'Freshness watchdog: publish trusted recent drafts when the public feed falls below the daily minimum.';

    public function handle(): int
    {
        $start = microtime(true);
        $dry = (bool) $this->option('dry-run');
        $active = (bool) setting('grimba_autopub_active', true);

        $minimum = max(1, (int) $this->option('min'));
        $windowHours = max(1, (int) $this->option('window-hours'));
        $lookbackHours = max(1, (int) $this->option('lookback-hours'));
        $minAgeMinutes = max(0, (int) $this->option('min-age-minutes'));
        $maxPublish = max(1, (int) $this->option('max-publish'));
        $perCategoryMin = max(0, (int) $this->option('per-category-min'));
        $categoryWindowHours = $this->option('category-window-hours') !== null && $this->option('category-window-hours') !== ''
            ? max(1, (int) $this->option('category-window-hours'))
            : $windowHours;
        $categoryScope = (string) $this->option('categories');
        $maxPublishPerCategory = max(1, (int) $this->option('max-publish-per-category'));
        $threshold = $this->option('threshold') !== null
            ? (int) $this->option('threshold')
            : (int) setting('grimba_autopub_min_credibility', 70);

        $since = now()->subHours($windowHours);
        $recent = $this->recentPublishedCount($since);
        $needed = max(0, $minimum - $recent);

        if (! $active && ! $dry) {
            $this->warn('grimba_autopub_active=false; freshness watchdog observed only.');
            $this->logRun([
                'active' => false,
                'recent' => $recent,
                'minimum' => $minimum,
                'published' => 0,
                'dry' => false,
                'duration_s' => round(microtime(true) - $start, 2),
            ]);

            return $recent >= $minimum ? self::SUCCESS : self::FAILURE;
        }

        if ($needed === 0) {
            $this->info(sprintf(
                'Freshness OK: %d published post(s) in the last %dh (minimum %d).',
                $recent,
                $windowHours,
                $minimum
            ));

            $published = 0;
            $candidateCount = 0;
            $after = $recent;
            $ok = true;
        } else {
            $limit = min($needed, $maxPublish);
            $candidates = $this->candidates($threshold, $minAgeMinutes, $lookbackHours, $limit);
            $ids = $candidates->pluck('id')->map(fn ($id) => (int) $id)->all();

            $this->table(['Metric', 'Value'], [
                ['recent_published', $recent],
                ['minimum', $minimum],
                ['needed', $needed],
                ['candidate_limit', $limit],
                ['candidates', count($ids)],
            ]);

            $published = 0;
            if ($dry) {
                $this->warn('DRY RUN - no posts were promoted.');
            } elseif ($ids !== []) {
                $published = GrimbaPostPublisher::publishDrafts($ids);
                $this->info(sprintf('Published %d freshness post(s).', $published));
            }

            $after = $dry ? ($recent + count($ids)) : $this->recentPublishedCount($since);
            $ok = $after >= $minimum;
            $candidateCount = count($ids);

            if (! $ok) {
                $this->error(sprintf(
                    'Freshness below target after remediation: %d/%d published in the last %dh.',
                    $after,
                    $minimum,
                    $windowHours
                ));
            } else {
                $this->info(sprintf(
                    'Freshness OK after remediation: %d/%d published in the last %dh.',
                    $after,
                    $minimum,
                    $windowHours
                ));
            }
        }

        $categoryResult = $perCategoryMin > 0
            ? $this->ensureCategoryFreshness(
                $perCategoryMin,
                $categoryWindowHours,
                $categoryScope,
                $threshold,
                $minAgeMinutes,
                $lookbackHours,
                $maxPublishPerCategory,
                $dry
            )
            : ['ok' => true, 'stale' => [], 'published' => 0, 'candidate_count' => 0];

        $stats = [
            'active' => true,
            'recent_before' => $recent,
            'recent_after' => $after,
            'minimum' => $minimum,
            'window_hours' => $windowHours,
            'lookback_hours' => $lookbackHours,
            'threshold' => $threshold,
            'published' => $published + (int) $categoryResult['published'],
            'candidate_count' => $candidateCount + (int) $categoryResult['candidate_count'],
            'category_min' => $perCategoryMin,
            'category_window_hours' => $categoryWindowHours,
            'category_stale' => $categoryResult['stale'],
            'dry' => $dry,
            'duration_s' => round(microtime(true) - $start, 2),
        ];
        $this->logRun($stats);

        return $ok && $categoryResult['ok'] ? self::SUCCESS : self::FAILURE;
    }

    private function recentPublishedCount(mixed $since): int
    {
        return (int) GrimbaPostRecency::wherePublishedSince(
            DB::table('posts')->where('status', 'published'),
            $since
        )->count();
    }

    private function candidates(int $threshold, int $minAgeMinutes, int $lookbackHours, int $limit): \Illuminate\Support\Collection
    {
        return DB::table('posts')
            ->join('news_sources', 'news_sources.id', '=', 'posts.source_id')
            ->where('posts.status', 'draft')
            ->whereIn('news_sources.bias_rating', ['left', 'center', 'right'])
            ->where('news_sources.credibility_score', '>=', $threshold)
            ->where('posts.created_at', '<=', now()->subMinutes($minAgeMinutes))
            ->where('posts.created_at', '>=', now()->subHours($lookbackHours))
            ->orderByDesc('posts.created_at')
            ->orderByDesc('posts.id')
            ->limit($limit)
            ->get([
                'posts.id',
                'posts.name',
                'posts.created_at',
                'news_sources.name as source_name',
                'news_sources.bias_rating',
                'news_sources.credibility_score',
            ]);
    }

    /**
     * @return array{ok: bool, stale: array<int, string>, published: int, candidate_count: int}
     */
    private function ensureCategoryFreshness(
        int $minimum,
        int $windowHours,
        string $scope,
        int $threshold,
        int $minAgeMinutes,
        int $lookbackHours,
        int $maxPublishPerCategory,
        bool $dry
    ): array {
        $since = now()->subHours($windowHours);
        $categories = GrimbaEditorialCategoryFreshness::counts($since, $scope);

        if ($categories->isEmpty()) {
            $this->error(sprintf('Category freshness could not find published editorial categories for scope "%s".', $scope));

            return ['ok' => false, 'stale' => ['no-categories'], 'published' => 0, 'candidate_count' => 0];
        }

        $stale = $categories
            ->filter(fn (object $category): bool => (int) $category->recent_count < $minimum)
            ->values();

        if ($stale->isEmpty()) {
            $this->info(sprintf(
                'Category freshness OK: %d category/categories have at least %d published post(s) in the last %dh.',
                $categories->count(),
                $minimum,
                $windowHours
            ));

            return ['ok' => true, 'stale' => [], 'published' => 0, 'candidate_count' => 0];
        }

        $this->warn(sprintf(
            'Category freshness needs remediation: %d stale category/categories below %d post(s) in %dh.',
            $stale->count(),
            $minimum,
            $windowHours
        ));

        $published = 0;
        $candidateCount = 0;
        $remainingStale = [];

        foreach ($stale as $category) {
            $needed = max(0, $minimum - (int) $category->recent_count);
            $limit = min($maxPublishPerCategory, max(1, $needed));
            $candidates = $this->categoryCandidates((int) $category->id, $threshold, $minAgeMinutes, $lookbackHours, $limit);
            $ids = $candidates->pluck('id')->map(fn ($id) => (int) $id)->all();
            $candidateCount += count($ids);

            $this->line(sprintf(
                '  - %s: %d/%d recent, %d candidate(s)',
                $category->name,
                (int) $category->recent_count,
                $minimum,
                count($ids)
            ));

            if ($dry) {
                $after = (int) $category->recent_count + count($ids);
            } else {
                if ($ids !== []) {
                    $published += GrimbaPostPublisher::publishDrafts($ids);
                }

                $after = GrimbaEditorialCategoryFreshness::recentCount((int) $category->id, $since);
            }

            if ($after < $minimum) {
                $remainingStale[] = sprintf('%s %d/%d', $category->name, $after, $minimum);
            }
        }

        if ($remainingStale !== []) {
            foreach ($remainingStale as $categoryLabel) {
                $this->error('Category freshness below target after remediation: ' . $categoryLabel);
            }

            return [
                'ok' => false,
                'stale' => $remainingStale,
                'published' => $published,
                'candidate_count' => $candidateCount,
            ];
        }

        $this->info(sprintf('Category freshness OK after remediation; published %d category repair post(s).', $published));

        return [
            'ok' => true,
            'stale' => [],
            'published' => $published,
            'candidate_count' => $candidateCount,
        ];
    }

    private function categoryCandidates(int $categoryId, int $threshold, int $minAgeMinutes, int $lookbackHours, int $limit): \Illuminate\Support\Collection
    {
        return DB::table('posts')
            ->join('post_categories', 'post_categories.post_id', '=', 'posts.id')
            ->join('news_sources', 'news_sources.id', '=', 'posts.source_id')
            ->where('post_categories.category_id', $categoryId)
            ->where('posts.status', 'draft')
            ->whereIn('news_sources.bias_rating', ['left', 'center', 'right'])
            ->where('news_sources.credibility_score', '>=', $threshold)
            ->where('posts.created_at', '<=', now()->subMinutes($minAgeMinutes))
            ->where('posts.created_at', '>=', now()->subHours($lookbackHours))
            ->orderByDesc('posts.created_at')
            ->orderByDesc('posts.id')
            ->limit($limit)
            ->get([
                'posts.id',
                'posts.name',
                'posts.created_at',
                'news_sources.name as source_name',
                'news_sources.bias_rating',
                'news_sources.credibility_score',
            ]);
    }

    private function logRun(array $stats): void
    {
        $line = sprintf(
            "[%s] grimba:ensure-daily-publish recent=%d after=%d min=%d published=%d candidates=%d category_min=%d category_stale=%s dry=%s duration=%ss\n",
            now()->toIso8601String(),
            (int) ($stats['recent_before'] ?? $stats['recent'] ?? 0),
            (int) ($stats['recent_after'] ?? $stats['recent'] ?? 0),
            (int) ($stats['minimum'] ?? 0),
            (int) ($stats['published'] ?? 0),
            (int) ($stats['candidate_count'] ?? 0),
            (int) ($stats['category_min'] ?? 0),
            implode('|', array_map('strval', $stats['category_stale'] ?? [])) ?: '-',
            (! empty($stats['dry'])) ? 'true' : 'false',
            $stats['duration_s'] ?? 0
        );

        @file_put_contents(storage_path('logs/grimba-freshness.log'), $line, FILE_APPEND | LOCK_EX);
        Log::info('[grimba:ensure-daily-publish] run complete', $stats);
    }
}
