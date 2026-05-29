<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/*
 * Wave SUB-58-CODE (2026-05-29) — Middle Ground daily summary.
 *
 * Complements grimba:health (which exposes one MG signal:
 * count of mg_* clusters in 24h). This command goes deep on
 * the MG signal alone: current totals, 24h / 7d / 30d trend,
 * average cluster size, and the L=C=R distribution inside
 * those mg_<L>_<C>_<R> tags so operators can see whether
 * coverage is symmetric (e.g., mg_2_0_2) or center-heavy
 * (mg_2_3_2 — still MG by our resolver but more centrist).
 *
 * Tags are written by grimba:reclassify-clusters --persist
 * onto story_clusters.review_action with prefix "mg_". The
 * canonical resolver lives in App\Support\GrimbaClusterBias.
 *
 * --json mode emits a single line of compact JSON for pipes
 * (cron → log shipper → dashboards), matching the pattern
 * already used by grimba:reclassify-clusters --json.
 */
class GrimbaMgStats extends Command
{
    protected $signature = 'grimba:mg-stats
        {--json : emit machine-readable JSON instead of the text report}';

    protected $description = 'Middle Ground (mg_*) cluster summary: current totals, 24h/7d/30d trend, L/C/R distribution.';

    public function handle(): int
    {
        $now = now();
        $last24h = $now->copy()->subDay();
        $last7d = $now->copy()->subDays(7);
        $last30d = $now->copy()->subDays(30);

        $total = (int) DB::table('story_clusters')
            ->where('review_action', 'like', 'mg_%')
            ->count();

        $count24h = (int) DB::table('story_clusters')
            ->where('review_action', 'like', 'mg_%')
            ->where('updated_at', '>=', $last24h)
            ->count();

        $count7d = (int) DB::table('story_clusters')
            ->where('review_action', 'like', 'mg_%')
            ->where('updated_at', '>=', $last7d)
            ->count();

        $count30d = (int) DB::table('story_clusters')
            ->where('review_action', 'like', 'mg_%')
            ->where('updated_at', '>=', $last30d)
            ->count();

        $tags = DB::table('story_clusters')
            ->select('review_action', DB::raw('COUNT(*) as c'))
            ->where('review_action', 'like', 'mg_%')
            ->groupBy('review_action')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        $sumL = 0;
        $sumC = 0;
        $sumR = 0;
        $symmetric = 0;
        $centerHeavy = 0;
        foreach (DB::table('story_clusters')->select('review_action')->where('review_action', 'like', 'mg_%')->get() as $row) {
            $parts = explode('_', (string) $row->review_action);
            if (count($parts) !== 4) {
                continue;
            }
            $l = (int) $parts[1];
            $c = (int) $parts[2];
            $r = (int) $parts[3];
            $sumL += $l;
            $sumC += $c;
            $sumR += $r;
            if ($c === 0) {
                $symmetric++;
            } elseif ($c >= $l) {
                $centerHeavy++;
            }
        }

        $avgSize = $total > 0 ? round(($sumL + $sumC + $sumR) / $total, 2) : 0.0;

        if ($this->option('json')) {
            $this->line(json_encode([
                'as_of' => $now->toIso8601String(),
                'total_mg_clusters' => $total,
                'updated_last_24h' => $count24h,
                'updated_last_7d' => $count7d,
                'updated_last_30d' => $count30d,
                'avg_cluster_size' => $avgSize,
                'symmetric_count' => $symmetric,
                'center_heavy_count' => $centerHeavy,
                'sum_left' => $sumL,
                'sum_center' => $sumC,
                'sum_right' => $sumR,
                'top_tags' => $tags->map(fn($t) => ['tag' => $t->review_action, 'count' => (int) $t->c])->all(),
            ], JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $this->newLine();
        $this->line(str_repeat('═', 60));
        $this->line(sprintf('  Middle Ground — daily summary · %s', $now->toIso8601String()));
        $this->line(str_repeat('═', 60));

        $this->newLine();
        $this->line('1. Totals');
        $this->line(sprintf('   ⊕ total MG clusters       %d', $total));
        $this->line(sprintf('   ⊕ updated last 24h        %d', $count24h));
        $this->line(sprintf('   ⊕ updated last 7d         %d', $count7d));
        $this->line(sprintf('   ⊕ updated last 30d        %d', $count30d));

        $this->newLine();
        $this->line('2. Shape');
        $this->line(sprintf('   avg cluster size          %s', number_format($avgSize, 2)));
        $this->line(sprintf('   symmetric (center=0)      %d', $symmetric));
        $this->line(sprintf('   center-heavy (c≥l)        %d', $centerHeavy));

        $this->newLine();
        $this->line('3. Bias bucket totals (across all MG clusters)');
        $this->line(sprintf('   left                       %d', $sumL));
        $this->line(sprintf('   center                     %d', $sumC));
        $this->line(sprintf('   right                      %d', $sumR));

        $this->newLine();
        $this->line('4. Top tag mixes (descending)');
        if ($tags->isEmpty()) {
            $this->line('   (no MG clusters in store)');
        } else {
            foreach ($tags as $t) {
                $this->line(sprintf('   %-14s %d', $t->review_action, (int) $t->c));
            }
        }

        $this->newLine();
        return self::SUCCESS;
    }
}
