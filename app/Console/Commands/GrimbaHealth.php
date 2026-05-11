<?php

namespace App\Console\Commands;

use App\Support\GrimbaAutomationMonitor;
use App\Support\GrimbaPostRecency;
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
 */
class GrimbaHealth extends Command
{
    protected $signature = 'grimba:health
        {--fail-on-risk : return a non-zero exit code when operating floors are breached}
        {--min-free-mb=2048 : minimum free disk space required when failing on risk}
        {--min-published-24h=12 : minimum published posts required in the last 24h when failing on risk}';
    protected $description = 'One-page health summary of the GrimbaNews ingest + editorial pipeline (S153).';

    public function handle(): int
    {
        $failOnRisk = (bool) $this->option('fail-on-risk');
        $minFreeMb = max(0, (int) $this->option('min-free-mb'));
        $minPublished24h = max(0, (int) $this->option('min-published-24h'));
        $riskWarnings = [];

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
        $this->line('5. Top unclassified sources (need editor triage)');
        if ($unknown->isEmpty()) {
            $this->line('   ✓ no unclassified sources with ingested articles');
        } else {
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

        $duppedNames = DB::table('posts')
            ->select('name', 'source_id', DB::raw('COUNT(*) as c'))
            ->groupBy('name', 'source_id')
            ->having('c', '>', 1)
            ->count();
        $this->newLine();
        $this->line('7. Dedup state');
        if ($duppedUrls === 0 && $duppedNames === 0) {
            $this->line('   ✓ no duplicate groups remaining');
        } else {
            if ($duppedUrls > 0) {
                $this->line(sprintf('   ⚠ %d duplicate URL group(s) — run grimba:dedupe-posts --apply', $duppedUrls));
            }
            if ($duppedNames > 0) {
                $this->line(sprintf('   ⚠ %d title-only group(s) need review before --include-title-groups', $duppedNames));
            }
        }

        // 8. Last 24h ingest
        $rss24 = DB::table('rss_feed_items')->where('seen_at', '>=', now()->subDay())->count();
        $api24 = DB::table('newsapi_items')->where('fetched_at', '>=', now()->subDay())->count();
        $this->newLine();
        $this->line('8. Ingest last 24h');
        $this->line(sprintf('   RSS poller    : %d items', $rss24));
        $this->line(sprintf('   NewsAPI fetch : %d items', $api24));
        $this->line(sprintf('   Combined      : %d items', $rss24 + $api24));

        if (($rss24 + $api24) === 0) {
            $riskWarnings[] = 'no RSS or NewsAPI intake in the last 24h';
        }

        // 9. Scheduler freshness
        $this->newLine();
        $this->line('9. Scheduler freshness');
        if (! GrimbaAutomationMonitor::ready()) {
            $this->warn('   ⚠ automation monitor table is unavailable; run migrations before relying on scheduler health');
            $riskWarnings[] = 'automation monitor table unavailable';
        } else {
            $automationStatus = GrimbaAutomationMonitor::status(GrimbaAutomationMonitor::freshnessJobKeys());

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

        $published24 = (int) GrimbaPostRecency::wherePublishedSince(
            DB::table('posts')->where('status', 'published'),
            now()->subDay()
        )->count();
        if ($published24 < $minPublished24h) {
            $riskWarnings[] = sprintf('publication freshness below floor: %d/%d posts in the last 24h', $published24, $minPublished24h);
        }

        $freeBytes = @disk_free_space(base_path());
        $totalBytes = @disk_total_space(base_path());
        $freeMb = is_int($freeBytes) || is_float($freeBytes) ? (int) floor($freeBytes / 1048576) : null;
        $totalGb = is_int($totalBytes) || is_float($totalBytes) ? round($totalBytes / 1073741824, 1) : null;
        if ($freeMb !== null && $freeMb < $minFreeMb) {
            $riskWarnings[] = sprintf('disk free space below floor: %dMB/%dMB', $freeMb, $minFreeMb);
        }

        $this->newLine();
        $this->line('10. Freshness + disk guard');
        $this->line(sprintf('   published 24h : %d post(s) (floor %d)', $published24, $minPublished24h));
        $this->line(sprintf(
            '   disk free     : %s%s (floor %dMB)',
            $freeMb === null ? 'unknown' : $freeMb . 'MB',
            $totalGb === null ? '' : ' / ' . $totalGb . 'GB',
            $minFreeMb
        ));

        if ($riskWarnings === []) {
            $this->line('   ✓ operating floors are clear');
        } else {
            foreach ($riskWarnings as $warning) {
                $this->warn('   ⚠ ' . $warning);
            }
        }

        $this->newLine();
        $this->line(str_repeat('═', 70));
        $this->newLine();

        return $failOnRisk && $riskWarnings !== []
            ? self::FAILURE
            : self::SUCCESS;
    }
}
