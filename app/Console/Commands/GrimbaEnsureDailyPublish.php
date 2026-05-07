<?php

namespace App\Console\Commands;

use App\Support\GrimbaPostPublisher;
use App\Support\GrimbaPostRecency;
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
            $this->logRun([
                'active' => true,
                'recent' => $recent,
                'minimum' => $minimum,
                'published' => 0,
                'dry' => $dry,
                'duration_s' => round(microtime(true) - $start, 2),
            ]);

            return self::SUCCESS;
        }

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

        $stats = [
            'active' => true,
            'recent_before' => $recent,
            'recent_after' => $after,
            'minimum' => $minimum,
            'window_hours' => $windowHours,
            'lookback_hours' => $lookbackHours,
            'threshold' => $threshold,
            'published' => $published,
            'candidate_count' => count($ids),
            'dry' => $dry,
            'duration_s' => round(microtime(true) - $start, 2),
        ];
        $this->logRun($stats);

        if (! $ok) {
            $this->error(sprintf(
                'Freshness below target after remediation: %d/%d published in the last %dh.',
                $after,
                $minimum,
                $windowHours
            ));

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Freshness OK after remediation: %d/%d published in the last %dh.',
            $after,
            $minimum,
            $windowHours
        ));

        return self::SUCCESS;
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

    private function logRun(array $stats): void
    {
        $line = sprintf(
            "[%s] grimba:ensure-daily-publish recent=%d after=%d min=%d published=%d candidates=%d dry=%s duration=%ss\n",
            now()->toIso8601String(),
            (int) ($stats['recent_before'] ?? $stats['recent'] ?? 0),
            (int) ($stats['recent_after'] ?? $stats['recent'] ?? 0),
            (int) ($stats['minimum'] ?? 0),
            (int) ($stats['published'] ?? 0),
            (int) ($stats['candidate_count'] ?? 0),
            (! empty($stats['dry'])) ? 'true' : 'false',
            $stats['duration_s'] ?? 0
        );

        @file_put_contents(storage_path('logs/grimba-freshness.log'), $line, FILE_APPEND | LOCK_EX);
        Log::info('[grimba:ensure-daily-publish] run complete', $stats);
    }
}
