<?php

namespace App\Console\Commands;

use App\Support\GrimbaClusterBias;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Wave DDDDDDDDDDD (Vader 2026-05-23) — retroactive bias reclassification
 * of recent article clusters.
 *
 * Vader directive: "do a retroactive analysis of the last 555 articles
 * and retier/recount them so we can populate the middle ground section
 * everywhere."
 *
 * What this does (in dry-run mode by default):
 *   1. Walk the last --limit articles ordered by ID desc.
 *   2. Group by story_cluster_id.
 *   3. For each cluster: recount L/C/R from its member articles, run
 *      GrimbaClusterBias::resolve(), report the resulting label.
 *   4. Print a table of cluster_id → bias_signal → counts → article_count.
 *   5. Emit Middle Ground / Blindspot / Balanced flags.
 *
 * Read-only by default. Pass --persist to denorm onto
 * story_clusters.review_action when a bias signal needs surfacing for
 * /juste-milieu listing (uses values 'middle_ground' or 'mg_*' prefix
 * so existing review_action consumers don't choke).
 */
class GrimbaReclassifyClusters extends Command
{
    protected $signature = 'grimba:reclassify-clusters
        {--limit=555 : Walk this many recent articles by post.id desc}
        {--persist : Denorm bias signal onto story_clusters.review_action prefix mg_}
        {--report-only=all : Filter rows: all, middle_ground, blindspot, balanced, left, center, right}
        {--json : Print results as JSON instead of formatted text (suppresses text headers; ideal for ops pipelines)}';

    protected $description = 'Retroactive cluster bias reclassification + Middle Ground / Blindspot surfacing.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $persist = (bool) $this->option('persist');
        $filter = (string) $this->option('report-only');
        // Wave IIII (Vader 2026-05-26) — JSON output mode for ops pipes.
        // When set, suppress all decorative text so `... --json | jq`
        // works cleanly.
        $jsonMode = (bool) $this->option('json');

        if (! $jsonMode) {
            $this->newLine();
            $this->line(str_repeat('═', 78));
            $this->line(sprintf('  Reclassifying clusters from the last %d articles · %s', $limit, now()->toIso8601String()));
            $this->line(str_repeat('═', 78));
            $this->newLine();
        }

        // 1. Pull the last N article rows with cluster + bias.
        $articles = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('story_cluster_id')
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'story_cluster_id', 'bias_rating']);

        if ($articles->isEmpty()) {
            $this->warn('No published articles with a cluster id in the last ' . $limit . '. Nothing to classify.');
            return self::SUCCESS;
        }

        // 2. Group by cluster + count bias per cluster.
        $byCluster = [];
        foreach ($articles as $a) {
            $cid = (int) $a->story_cluster_id;
            if (! isset($byCluster[$cid])) {
                $byCluster[$cid] = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0, 'count' => 0];
            }
            $rating = (string) ($a->bias_rating ?? 'unknown');
            if (! isset($byCluster[$cid][$rating])) {
                $rating = 'unknown';
            }
            $byCluster[$cid][$rating]++;
            $byCluster[$cid]['count']++;
        }

        // 3. Resolve bias per cluster + classify.
        $rows = [];
        $totals = ['left' => 0, 'center' => 0, 'right' => 0, 'middle_ground' => 0, 'unknown' => 0, 'blindspot' => 0, 'balanced' => 0];
        foreach ($byCluster as $cid => $counts) {
            $signal = GrimbaClusterBias::resolve([
                'left' => $counts['left'],
                'center' => $counts['center'],
                'right' => $counts['right'],
            ]);

            // Detect blindspot: one camp covers 80%+ on at least 3
            // distinct articles (matches the existing Wave/historical
            // blindspot contract).
            $known = $counts['left'] + $counts['center'] + $counts['right'];
            $maxCamp = max($counts['left'], $counts['center'], $counts['right']);
            $isBlindspot = $known >= 3 && $maxCamp >= ($known * 0.8);

            // Detect balanced (no camp > 40%, all 3 present)
            $isBalanced = $counts['left'] > 0 && $counts['center'] > 0 && $counts['right'] > 0
                && (int) round(($maxCamp / max(1, $known)) * 100) <= 40;

            $extraTag = '';
            if ($signal['key'] === 'middle_ground') {
                $totals['middle_ground']++;
                $extraTag = '🟪 MG';
            } elseif ($isBlindspot) {
                $totals['blindspot']++;
                $extraTag = '⚫️ BS';
            } elseif ($isBalanced) {
                $totals['balanced']++;
                $extraTag = '🟢 BAL';
            } else {
                $totals[$signal['key']]++;
            }

            $rows[] = [
                'cid' => $cid,
                'signal' => $signal['key'],
                'label' => $signal['label'],
                'l' => $counts['left'],
                'c' => $counts['center'],
                'r' => $counts['right'],
                'unk' => $counts['unknown'],
                'total' => $counts['count'],
                'tag' => $extraTag,
                'is_blindspot' => $isBlindspot,
                'is_balanced' => $isBalanced,
            ];
        }

        // 4. Filter + render
        $rendered = $rows;
        if ($filter !== 'all') {
            $rendered = array_values(array_filter($rows, function ($r) use ($filter) {
                return match ($filter) {
                    'middle_ground' => $r['signal'] === 'middle_ground',
                    'blindspot' => $r['is_blindspot'],
                    'balanced' => $r['is_balanced'],
                    'left', 'center', 'right' => $r['signal'] === $filter,
                    default => true,
                };
            }));
        }

        // Sort: Middle Ground + Blindspot + Balanced first
        usort($rendered, function ($a, $b) {
            $pri = fn ($r) => match (true) {
                $r['signal'] === 'middle_ground' => 0,
                $r['is_blindspot'] => 1,
                $r['is_balanced'] => 2,
                default => 3,
            };
            return $pri($a) <=> $pri($b) ?: $b['total'] <=> $a['total'];
        });

        if (! $jsonMode) {
            $this->table(
                ['Cluster', 'Signal', 'L', 'C', 'R', '?', '🅣', 'Tag'],
                array_map(fn ($r) => [
                    '#' . $r['cid'],
                    $r['label'],
                    $r['l'],
                    $r['c'],
                    $r['r'],
                    $r['unk'],
                    $r['total'],
                    $r['tag'],
                ], array_slice($rendered, 0, 50))
            );

            if (count($rendered) > 50) {
                $this->line(sprintf('  … and %d more rows (showing first 50).', count($rendered) - 50));
            }

            $this->newLine();
            $this->line('Summary across all reclassified clusters:');
            $this->line(sprintf(
                '  Clusters examined: %d · Middle Ground: %d · Blindspot: %d · Balanced: %d · Left: %d · Center: %d · Right: %d',
                count($byCluster),
                $totals['middle_ground'],
                $totals['blindspot'],
                $totals['balanced'],
                $totals['left'],
                $totals['center'],
                $totals['right']
            ));
        }

        // 5. Persist Middle Ground tag onto story_clusters.review_action
        // (prefix `mg_` so existing review_action consumers stay backward-
        // compatible). Only writes when --persist set.
        $persisted = 0;
        if ($persist) {
            foreach ($rows as $r) {
                if ($r['signal'] !== 'middle_ground') {
                    continue;
                }
                $tag = GrimbaClusterBias::formatMgTag((int) $r['l'], (int) $r['c'], (int) $r['r']);
                DB::table('story_clusters')
                    ->where('id', $r['cid'])
                    ->update([
                        'review_action' => $tag,
                        'reviewed_at' => now(),
                        'updated_at' => now(),
                    ]);
                $persisted++;
            }
            if (! $jsonMode) {
                $this->line(sprintf('  Persisted %d Middle Ground tags onto story_clusters.review_action (prefix mg_).', $persisted));
            }
        } elseif (! $jsonMode) {
            $this->line('  --persist not set; no story_clusters rows touched.');
        }

        if ($jsonMode) {
            // Wave IIII — emit single JSON object with totals + a
            // compact rows array for ops pipes. Suppresses every
            // other text line — caller may safely `... --json | jq`.
            $this->line(json_encode([
                'walked_limit' => $limit,
                'persist' => $persist,
                'totals' => [
                    'left' => $totals['left'],
                    'center' => $totals['center'],
                    'right' => $totals['right'],
                    'middle_ground' => $totals['middle_ground'] ?? 0,
                    'blindspot' => $totals['blindspot'] ?? 0,
                    'balanced' => $totals['balanced'] ?? 0,
                    'unclassified' => $totals['unclassified'] ?? 0,
                ],
                'clusters_touched' => $persisted,
                'generated_at' => now()->toIso8601String(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $this->newLine();
            $this->line(str_repeat('═', 78));
            $this->newLine();
        }

        return self::SUCCESS;
    }
}
