<?php

namespace App\Console\Commands;

use App\Services\GrimbaNewsApiFetcher;
use App\Services\GrimbaUrlCanonicalizer;
use App\Support\GrimbaAutomationMonitor;
use App\Support\GrimbaDatabaseBackups;
use App\Support\GrimbaDedupeReview;
use App\Support\GrimbaEditorialCategoryFreshness;
use App\Support\GrimbaFullArticleCoverage;
use App\Support\GrimbaPublicationPipeline;
use App\Support\GrimbaRssFeedHealth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/*
 * S153 — one-page health summary for the GrimbaNews ingest +
 * editorial pipeline. Designed to be `tail`-able from a cron
 * weekly + manually runnable when checking on the system.
 *
 * Reports:
 *   1. Posts by status (published / draft / total)
 *   2. Bias mix on published posts
 *   3. Region (country) coverage
 *   4. Cluster diversity (singletons vs multi-bias)
 *   5. Source classification gaps (% unknown by ingest volume)
 *   6. Feed health (active / sick / failed)
 *   7. Dedup state (count of duplicate-name groups remaining)
 *   8. Recent ingest rate (last 24h, RSS + NewsAPI separately)
 *   9. Scheduler freshness for the jobs that protect daily articles
 *  10. Full article extraction coverage for in-app reading
 */
class GrimbaHealth extends Command
{
    protected $signature = 'grimba:health
        {--fail-on-risk : return a non-zero exit code when operating floors are breached}
        {--min-free-mb=2048 : minimum free disk space required when failing on risk}
        {--min-published-24h=12 : minimum published posts required in the last 24h when failing on risk}
        {--min-ingested-published-24h= : minimum RSS/NewsAPI-backed published posts required in the last 24h; defaults to min-published-24h}
        {--min-category-published-24h=0 : minimum published posts required per editorial category in the last 24h; 0 observes only}
        {--category-freshness-scope=all : category freshness scope: all, editions, topics, or comma-separated category names}
        {--min-full-content-coverage=0 : minimum percent of recent upstream-backed posts with readable full text; 0 observes only}
        {--full-content-retry-after-hours=24 : retry window used by full article extraction health}
        {--min-middle-ground-clusters=0 : minimum Middle Ground (mg_* tagged) cluster count; 0 observes only}
        {--backup-dir= : database backup directory to inspect; defaults to database/backups}';
    protected $description = 'One-page health summary of the GrimbaNews ingest + editorial pipeline (S153).';

    public function handle(GrimbaNewsApiFetcher $newsApiFetcher, GrimbaUrlCanonicalizer $canon): int
    {
        $failOnRisk = (bool) $this->option('fail-on-risk');
        $minFreeMb = max(0, (int) $this->option('min-free-mb'));
        $minPublished24h = max(0, (int) $this->option('min-published-24h'));
        $minIngestedPublishedOption = $this->option('min-ingested-published-24h');
        $minIngestedPublished24h = $minIngestedPublishedOption === null || $minIngestedPublishedOption === ''
            ? $minPublished24h
            : max(0, (int) $minIngestedPublishedOption);
        $minCategoryPublished24h = max(0, (int) $this->option('min-category-published-24h'));
        $categoryFreshnessScope = (string) $this->option('category-freshness-scope');
        $minFullContentCoverage = min(100, max(0, (int) $this->option('min-full-content-coverage')));
        $fullContentRetryAfterHours = max(0, (int) $this->option('full-content-retry-after-hours'));
        $minMiddleGround = max(0, (int) $this->option('min-middle-ground-clusters'));
        $backupDir = (string) ($this->option('backup-dir') ?: GrimbaDatabaseBackups::defaultDir());
        $riskWarnings = [];
        $last24h = now()->subDay();

        $this->newLine();
        $this->line(str_repeat('═', 70));
        $this->line(sprintf('  GrimbaNews — health check · %s', now()->toIso8601String()));
        $this->line(str_repeat('═', 70));

        // 1. Posts by status
        $byStatus = DB::table('posts')->select('status', DB::raw('COUNT(*) as c'))->groupBy('status')->pluck('c', 'status');
        $this->newLine();
        $this->line('1. Posts');
        foreach (['published' => '🟢', 'draft' => '🟡', 'pending' => '⚪'] as $st => $glyph) {
            $this->line(sprintf('   %s %-12s %d', $glyph, $st, $byStatus[$st] ?? 0));
        }
        $this->line(sprintf('     %-14s %d', 'TOTAL', $byStatus->sum()));

        // 2. Bias mix on published
        $bias = DB::table('posts')->where('status', 'published')->select('bias_rating', DB::raw('COUNT(*) as c'))->groupBy('bias_rating')->pluck('c', 'bias_rating');
        $pubTotal = $bias->sum() ?: 1;
        $this->newLine();
        $this->line('2. Bias mix (published)');
        foreach (['left' => 'Gauche  ', 'center' => 'Centre  ', 'right' => 'Droite  ', 'unknown' => 'Inconnu '] as $k => $label) {
            $count = $bias[$k] ?? 0;
            $pct = round($count * 100 / $pubTotal);
            $this->line(sprintf('   %s %4d (%2d%%) %s', $label, $count, $pct, str_repeat('▓', max(1, (int) round($pct / 3)))));
        }

        // 3. Region coverage
        $byCountry = DB::table('posts')
            ->join('news_sources', 'news_sources.id', '=', 'posts.source_id')
            ->where('posts.status', 'published')
            ->select('news_sources.country', DB::raw('COUNT(*) as c'))
            ->groupBy('news_sources.country')
            ->orderByDesc('c')
            ->limit(10)
            ->get();
        $this->newLine();
        $this->line('3. Region coverage (published, top 10 countries)');
        foreach ($byCountry as $r) {
            $this->line(sprintf('   %-8s %d', $r->country ?: '(none)', $r->c));
        }

        // 4. Cluster diversity
        $clusters = DB::table('posts')->whereNotNull('story_cluster_id')->where('status', 'published')
            ->select('story_cluster_id', DB::raw('COUNT(*) as c'))
            ->groupBy('story_cluster_id')->get();
        $multi = 0;
        $allThree = 0;
        foreach ($clusters as $c) {
            $sides = DB::table('posts')
                ->where('story_cluster_id', $c->story_cluster_id)
                ->where('status', 'published')
                ->whereIn('bias_rating', ['left', 'center', 'right'])
                ->distinct('bias_rating')
                ->count('bias_rating');
            if ($sides >= 2) $multi++;
            if ($sides >= 3) $allThree++;
        }
        $totalClusters = $clusters->count();
        $this->newLine();
        $this->line('4. Cluster diversity');
        $this->line(sprintf('   total clusters w/ ≥1 published post: %d', $totalClusters));
        $this->line(sprintf('   ≥2 bias sides:                       %d (%d%%)', $multi, $totalClusters ? round($multi * 100 / $totalClusters) : 0));
        $this->line(sprintf('   all 3 bias sides:                    %d (%d%%)', $allThree, $totalClusters ? round($allThree * 100 / $totalClusters) : 0));

        // 5. Source classification gaps
        $unknown = DB::table('news_sources')
            ->leftJoin('posts', 'posts.source_id', '=', 'news_sources.id')
            ->where('news_sources.bias_rating', 'unknown')
            ->select('news_sources.name', DB::raw('COUNT(posts.id) as c'))
            ->groupBy('news_sources.id', 'news_sources.name')
            ->having('c', '>', 0)
            ->orderByDesc('c')
            ->limit(8)
            ->get();
        $this->newLine();
        $this->line('5. Top unclassified sources (classifier/editor triage)');
        if ($unknown->isEmpty()) {
            $this->line('   ✓ no unclassified sources with ingested articles');
        } else {
            $this->line('   run: php artisan grimba:classify-sources --apply --sync-posts');
            foreach ($unknown as $s) {
                $this->line(sprintf('   %-30s %d articles', \Illuminate\Support\Str::limit($s->name, 30), $s->c));
            }
        }

        // 6. Feed health
        $allFeeds = DB::table('rss_feeds')->get();
        $activeFeeds = $allFeeds->filter(fn ($feed) => (bool) $feed->is_active);
        $inactiveFeeds = $allFeeds->count() - $activeFeeds->count();
        $healthyFeeds = $activeFeeds->filter(fn ($feed) => GrimbaRssFeedHealth::score($feed) >= 85)->count();
        $watchFeeds = $activeFeeds->filter(fn ($feed) => GrimbaRssFeedHealth::score($feed) >= 65 && GrimbaRssFeedHealth::score($feed) < 85)->count();
        $staleFeeds = $activeFeeds->filter(fn ($feed) => GrimbaRssFeedHealth::isStale($feed))->count();
        $sickFeeds = $activeFeeds->filter(fn ($feed) => GrimbaRssFeedHealth::isSick($feed))->count();
        $averageHealth = $activeFeeds->isEmpty()
            ? 0
            : (int) round($activeFeeds->avg(fn ($feed) => GrimbaRssFeedHealth::score($feed)));
        $this->newLine();
        $this->line('6. RSS feed health');
        $this->line(sprintf('   average score %d%%', $averageHealth));
        $this->line(sprintf('   🟢 healthy    %d  (score ≥85)', $healthyFeeds));
        $this->line(sprintf('   🟡 watch      %d  (score 65-84)', $watchFeeds));
        $this->line(sprintf('   🟠 stale      %d  (no success in 24h)', $staleFeeds));
        $this->line(sprintf('   🔴 sick       %d  (≥5 consecutive failures)', $sickFeeds));
        $this->line(sprintf('   ⚫ inactive   %d', $inactiveFeeds));

        // 7. Dedup state
        $duplicateUrlGroupsQuery = DB::table('rss_feed_items')
            ->join('posts', 'posts.id', '=', 'rss_feed_items.post_id')
            ->whereNotNull('rss_feed_items.canonical_url_hash')
            ->whereNotNull('rss_feed_items.post_id')
            ->select('posts.source_id', 'rss_feed_items.canonical_url_hash')
            ->groupBy('posts.source_id', 'rss_feed_items.canonical_url_hash')
            ->havingRaw('COUNT(DISTINCT rss_feed_items.post_id) > 1');
        $duppedUrls = (int) DB::query()->fromSub($duplicateUrlGroupsQuery, 'dupes')->count();

        $titleGroupPartitions = GrimbaDedupeReview::partitionTitleGroups(
            GrimbaDedupeReview::titleGroups(),
            $canon
        );
        $duppedNames = $titleGroupPartitions['unresolved']->count();
        $ignoredTitleGroups = $titleGroupPartitions['ignored']->count();
        $this->newLine();
        $this->line('7. Dedup state');
        if ($duppedUrls === 0 && $duppedNames === 0) {
            $this->line('   ✓ no duplicate groups remaining');
        } else {
            if ($duppedUrls > 0) {
                $this->line(sprintf('   ⚠ %d duplicate URL group(s) — run grimba:dedupe-posts --apply', $duppedUrls));
            }
            if ($duppedNames > 0) {
                $this->line(sprintf('   ⚠ %d title-only group(s) need grimba:dedupe-posts --review-title-groups before --include-title-groups', $duppedNames));
            }
        }
        if ($ignoredTitleGroups > 0) {
            $this->line(sprintf(
                '   ✓ %d known recurring media title group(s) ignored',
                $ignoredTitleGroups
            ));
        }

        // 8. Last 24h ingest
        $rss24 = DB::table('rss_feed_items')->where('seen_at', '>=', $last24h)->count();
        $api24 = DB::table('newsapi_items')->where('fetched_at', '>=', $last24h)->count();
        $live24 = DB::table('grimba_live_news_items')->where('fetched_at', '>=', $last24h)->count();
        $this->newLine();
        $this->line('8. Ingest last 24h');
        $this->line(sprintf('   RSS poller    : %d items', $rss24));
        $this->line(sprintf('   NewsAPI fetch : %d items', $api24));
        $this->line(sprintf('   Live providers: %d items', $live24));
        $this->line(sprintf('   Combined      : %d items', $rss24 + $api24 + $live24));

        $newsApiConfigured = $newsApiFetcher->isConfigured();
        $newsApiActive = (bool) setting('grimba_newsapi_active', $newsApiConfigured);
        $this->line(sprintf(
            '   NewsAPI state : %s / %s',
            $newsApiActive ? 'active' : 'inactive',
            $newsApiConfigured ? 'configured' : 'missing key'
        ));

        if (($rss24 + $api24) === 0) {
            $riskWarnings[] = 'no RSS or NewsAPI intake in the last 24h';
        }
        if ($newsApiActive && ! $newsApiConfigured) {
            $riskWarnings[] = 'NewsAPI is active but no key is configured';
        }

        // 9. Scheduler freshness
        $this->newLine();
        $this->line('9. Scheduler freshness');
        if (! GrimbaAutomationMonitor::ready()) {
            $this->warn('   ⚠ automation monitor table is unavailable; run migrations before relying on scheduler health');
            $riskWarnings[] = 'automation monitor table unavailable';
        } else {
            $healthJobKeys = array_values(array_filter(
                GrimbaAutomationMonitor::healthJobKeys(),
                fn (string $jobKey): bool => $jobKey !== 'ops_health'
            ));
            $automationStatus = GrimbaAutomationMonitor::status($healthJobKeys);

            foreach ($automationStatus as $job) {
                $glyph = $job->is_failed || $job->is_stale ? '⚠' : '✓';
                $lastCheckpoint = $job->last_success_at ?: $job->last_observed_at;
                $lastCheckpointLabel = $lastCheckpoint
                    ? $lastCheckpoint->diffForHumans()
                    : 'never';
                $checkpointKind = $job->last_success_at ? 'last success' : 'last observed';

                $this->line(sprintf(
                    '   %s %-22s %-8s %s %s',
                    $glyph,
                    $job->label,
                    $job->status,
                    $checkpointKind,
                    $lastCheckpointLabel
                ));

                if ($job->is_failed || $job->is_stale) {
                    $problems = [];

                    if ($job->is_failed) {
                        $problems[] = $job->is_stuck ? 'latest run is stuck' : 'latest run failed';
                    }

                    if ($job->is_stale) {
                        $problems[] = $checkpointKind . ' ' . $lastCheckpointLabel;
                    }

                    $riskWarnings[] = sprintf(
                        'automation job unhealthy: %s (%s)',
                        $job->label,
                        implode(', ', $problems)
                    );
                }
            }
        }

        // 10. Full article readability coverage
        $fullArticleCoverage = GrimbaFullArticleCoverage::recent($last24h, $fullContentRetryAfterHours);
        $this->newLine();
        $this->line('10. Full article readability');
        if (! $fullArticleCoverage->available) {
            $this->line('   observed only         : ' . $fullArticleCoverage->reason);
        } else {
            $this->line(sprintf(
                '   readable bodies       : %d/%d (%d%%, floor %d%%)',
                $fullArticleCoverage->readable,
                $fullArticleCoverage->total,
                $fullArticleCoverage->coverage_pct,
                $minFullContentCoverage
            ));
            if (($fullArticleCoverage->ingest_fallback_readable ?? 0) > 0) {
                $this->line(sprintf(
                    '   feed body fallback    : %d readable post(s)',
                    $fullArticleCoverage->ingest_fallback_readable
                ));
            }
            $this->line(sprintf(
                '   missing bodies        : %d (%d never attempted · %d failed · %d retry-ready · %d deferred)',
                $fullArticleCoverage->missing,
                $fullArticleCoverage->never_attempted,
                $fullArticleCoverage->failed,
                $fullArticleCoverage->retry_ready,
                $fullArticleCoverage->retry_deferred
            ));
            $this->line(sprintf(
                '   latest extraction     : %s',
                $fullArticleCoverage->latest_fetched_at
                    ? $fullArticleCoverage->latest_fetched_at->diffForHumans()
                    : 'never'
            ));

            if ($minFullContentCoverage > 0 && $fullArticleCoverage->coverage_pct < $minFullContentCoverage) {
                $riskWarnings[] = sprintf(
                    'full-article coverage below floor: %d%%/%d%% (%d/%d readable recent upstream-backed posts)',
                    $fullArticleCoverage->coverage_pct,
                    $minFullContentCoverage,
                    $fullArticleCoverage->readable,
                    $fullArticleCoverage->total
                );
            }
        }

        $publicationPipeline = GrimbaPublicationPipeline::since($last24h);
        if ($publicationPipeline->published24 < $minPublished24h) {
            $riskWarnings[] = sprintf('publication freshness below floor: %d/%d posts in the last 24h', $publicationPipeline->published24, $minPublished24h);
        }
        if ($publicationPipeline->ingestedPublished24 < $minIngestedPublished24h) {
            $riskWarnings[] = sprintf(
                'ingest-to-public freshness below floor: %d/%d RSS/NewsAPI-backed posts in the last 24h',
                $publicationPipeline->ingestedPublished24,
                $minIngestedPublished24h
            );
        }

        $categoryFreshness = GrimbaEditorialCategoryFreshness::counts($last24h, $categoryFreshnessScope);
        $staleCategories = $categoryFreshness
            ->filter(fn (object $category): bool => (int) $category->recent_count < $minCategoryPublished24h)
            ->values();

        if ($minCategoryPublished24h > 0) {
            if ($categoryFreshness->isEmpty()) {
                $riskWarnings[] = sprintf('category freshness scope has no published categories: %s', $categoryFreshnessScope);
            } elseif ($staleCategories->isNotEmpty()) {
                $riskWarnings[] = sprintf(
                    'category freshness below floor: %s',
                    $staleCategories
                        ->map(fn (object $category): string => sprintf('%s %d/%d', $category->name, (int) $category->recent_count, $minCategoryPublished24h))
                        ->implode(', ')
                );
            }
        }

        $freeBytes = @disk_free_space(base_path());
        $totalBytes = @disk_total_space(base_path());
        $freeMb = is_int($freeBytes) || is_float($freeBytes) ? (int) floor($freeBytes / 1048576) : null;
        $totalGb = is_int($totalBytes) || is_float($totalBytes) ? round($totalBytes / 1073741824, 1) : null;
        if ($freeMb !== null && $freeMb < $minFreeMb) {
            $riskWarnings[] = sprintf('disk free space below floor: %dMB/%dMB', $freeMb, $minFreeMb);
        }

        $backupHealth = GrimbaDatabaseBackups::health($backupDir);
        if ($backupHealth->available && $backupHealth->invalid !== []) {
            $riskWarnings[] = 'invalid database backup artifacts: ' . implode(', ', $backupHealth->invalid);
        }

        // Wave MMMMMMMMMMM (Vader 2026-05-26) — Middle Ground floor.
        // Operator can set --min-middle-ground-clusters=N to alert
        // when the corpus's editorial-balance signal dries up
        // (e.g. all stories pulling left or right; no truly bilateral
        // coverage). At 0 (default) only observes — printed in
        // section 12 below.
        $middleGroundCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('story_clusters')) {
                $middleGroundCount = (int) \Illuminate\Support\Facades\DB::table('story_clusters')
                    ->where('review_action', 'like', \App\Support\GrimbaClusterBias::MG_TAG_SQL_LIKE)
                    ->count();
            }
        } catch (\Throwable) {
            // schema may not exist in test envs; observe-silent
        }
        if ($minMiddleGround > 0 && $middleGroundCount < $minMiddleGround) {
            $riskWarnings[] = sprintf(
                'Middle Ground cluster floor breached: %d tagged / %d minimum required',
                $middleGroundCount,
                $minMiddleGround
            );
        }

        $this->newLine();
        $this->line('11. Freshness + disk + backup guard');
        $this->line(sprintf('   published 24h        : %d post(s) (floor %d)', $publicationPipeline->published24, $minPublished24h));
        $this->line(sprintf(
            '   ingest-published 24h : %d post(s) (RSS %d / NewsAPI %d / manual %d, floor %d)',
            $publicationPipeline->ingestedPublished24,
            $publicationPipeline->rssPublished24,
            $publicationPipeline->newsApiPublished24,
            $publicationPipeline->manualPublished24,
            $minIngestedPublished24h
        ));
        $this->line(sprintf(
            '   latest public        : %s',
            $publicationPipeline->latestPublishedAt ?: 'never'
        ));
        if ($minCategoryPublished24h > 0) {
            $this->line(sprintf(
                '   category freshness  : %d stale / %d tracked (floor %d)',
                $staleCategories->count(),
                $categoryFreshness->count(),
                $minCategoryPublished24h
            ));
        }
        $this->line(sprintf(
            '   disk free            : %s%s (floor %dMB)',
            $freeMb === null ? 'unknown' : $freeMb . 'MB',
            $totalGb === null ? '' : ' / ' . $totalGb . 'GB',
            $minFreeMb
        ));
        if (! $backupHealth->available) {
            $this->line(sprintf('   backups              : not found (%s)', $backupHealth->dir));
        } else {
            $latestBackup = $backupHealth->latest_at !== null
                ? \Illuminate\Support\Carbon::createFromTimestamp($backupHealth->latest_at)->diffForHumans()
                : 'none';
            $this->line(sprintf(
                '   backups              : %d valid / %d invalid · %s · latest %s',
                $backupHealth->valid,
                count($backupHealth->invalid),
                GrimbaDatabaseBackups::formatBytes($backupHealth->size_bytes),
                $latestBackup
            ));
        }
        // Wave MMMMMMMMMMM (Vader 2026-05-26) — Middle Ground editorial
        // signal row. Floor is 0 by default (observe-only); operator
        // sets --min-middle-ground-clusters=N to alert.
        $this->line(sprintf(
            '   middle ground clusters : %d tagged (floor %d%s)',
            $middleGroundCount,
            $minMiddleGround,
            $minMiddleGround === 0 ? ', observe-only' : ''
        ));
        if ($middleGroundCount > 0) {
            $this->line('     ↳ for MG trend + tag-mix breakdown: php artisan grimba:mg-stats');
        }

        if ($riskWarnings === []) {
            $this->line('   ✓ operating floors are clear');
        } else {
            foreach ($riskWarnings as $warning) {
                $this->warn('   ⚠ ' . $warning);
            }
            $this->notifySlackOnRisk($riskWarnings);
        }

        $this->newLine();
        $this->line(str_repeat('═', 70));
        $this->newLine();

        return $failOnRisk && $riskWarnings !== []
            ? self::FAILURE
            : self::SUCCESS;
    }

    /**
     * Wave YYYYYYYYYY (Vader 2026-05-23) — Slack webhook fallback.
     *
     * If GRIMBA_HEALTH_SLACK_WEBHOOK is set in the environment, post
     * a summary line per failure run. Operator sets the URL in .env;
     * no other config needed. Silent no-op if the env var is unset
     * (preserves today's behavior). 5s timeout so a hung endpoint
     * never blocks the hourly cron.
     */
    private function notifySlackOnRisk(array $warnings): void
    {
        $webhook = (string) env('GRIMBA_HEALTH_SLACK_WEBHOOK', '');
        if ($webhook === '' || ! filter_var($webhook, FILTER_VALIDATE_URL)) {
            return;
        }
        $payload = [
            'text' => sprintf(
                ":rotating_light: *GrimbaNews health: %d operating floor%s breached*\n*env:* %s · *host:* %s · *time:* %s\n%s",
                count($warnings),
                count($warnings) === 1 ? '' : 's',
                (string) env('APP_ENV', 'unknown'),
                (string) gethostname(),
                now()->toIso8601String(),
                implode("\n", array_map(static fn (string $w): string => '• ' . $w, $warnings))
            ),
        ];
        // Wave FFFFFFFFFFF (Vader 2026-05-26, Zen MEDIUM-2 follow-up) —
        // self-monitoring the paging path. Without this, a Slack webhook
        // that returns 410 (channel deleted) / 404 (URL revoked) /
        // resolves but 5xxs for a week silently degrades: operator
        // believes paging works but no message ever arrives. Now: every
        // webhook attempt registers a synthetic GrimbaAutomationMonitor
        // run keyed 'slack_webhook'. The status surfaces in the next
        // hourly grimba:health probe (section 9, scheduler freshness)
        // so silent paging stops being silent.
        $runId = GrimbaAutomationMonitor::start('slack_webhook', 'grimba:health → slack-webhook POST');
        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(5)
                ->connectTimeout(3)
                ->retry(0)
                ->asJson()
                ->post($webhook, $payload);
            if ($resp->successful()) {
                $this->line('   📡 slack-webhook POST ok');
                GrimbaAutomationMonitor::finish($runId, 'success', 0);
            } else {
                $this->line('   📡 slack-webhook POST failed (' . $resp->status() . ')');
                GrimbaAutomationMonitor::finish(
                    $runId,
                    'failed',
                    1,
                    'Slack webhook returned ' . $resp->status() . ' (host ' . (parse_url($webhook, PHP_URL_HOST) ?: 'unknown') . ')'
                );
            }
        } catch (\Throwable $e) {
            // Wave BBBBBBBBBBB (Zen LOW) — don't echo $e->getMessage() because
            // cURL errors may include the full webhook URL (with token).
            // Only surface the host, never the path/token.
            $host = (string) (parse_url($webhook, PHP_URL_HOST) ?: 'unknown-host');
            $this->line('   📡 slack-webhook POST failed (host=' . $host . ', ' . get_class($e) . ')');
            GrimbaAutomationMonitor::finish(
                $runId,
                'failed',
                1,
                'Slack webhook threw ' . get_class($e) . ' (host ' . $host . ')'
            );
        }
    }
}
