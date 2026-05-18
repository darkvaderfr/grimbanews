<?php

namespace App\Console\Commands;

use App\Support\GrimbaArticleRegion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vader 2026-05-18 — retroactive topic-based editorial-region tag.
 *
 * The source-country backfill (GrimbaBackfillEditorialRegions) tags
 * every Le Monde / RFI / France 24 article as `europe`, leaving the
 * /africa page light even when the corpus has 300+ articles ABOUT
 * Africa from French publishers.
 *
 * This command scans every published post's title + description +
 * NobuAI summary for region anchors and overrides the source-country
 * tag when:
 *   - the topical scan finds a single dominant region (3+ weighted
 *     points, 2× margin), AND
 *   - the current region is wrong (different from the topical winner).
 *
 * Stays conservative by default. Pass `--apply` to write; without
 * it, the command reports what WOULD change. Pass `--include-international`
 * to also reconsider `international` posts (these are often
 * misclassified Africa coverage from wire services).
 */
class GrimbaRetagEditorialRegionByTopic extends Command
{
    protected $signature = 'grimba:retag-editorial-region-by-topic
        {--batch=300 : posts per batch}
        {--limit= : optional cap on total posts processed}
        {--apply : write changes (default = dry-run)}
        {--include-international : also reconsider posts tagged `international`}
        {--include-correct : reconsider rows whose current tag agrees with topic (useful for warming the audit log)}
        {--with-secondary : also backfill editorial_secondary_region from detectAllFromText (S-LSAT-18c)}';

    protected $description = 'Re-tag posts.editorial_region by article topic (overrides source-country tag when topical signal is strong).';

    public function handle(): int
    {
        if (! Schema::hasColumn('posts', 'editorial_region')) {
            $this->error('posts.editorial_region column not present.');
            return self::FAILURE;
        }

        $batch = max(50, (int) $this->option('batch'));
        $limitOption = $this->option('limit');
        $limit = $limitOption !== null ? max(1, (int) $limitOption) : null;
        $apply = (bool) $this->option('apply');
        $reconsiderIntl = (bool) $this->option('include-international');
        $reconsiderCorrect = (bool) $this->option('include-correct');
        $withSecondary = (bool) $this->option('with-secondary');

        $hasSummary = Schema::hasColumn('posts', 'summary_nobuai');
        $hasSecondaryCol = Schema::hasColumn('posts', 'editorial_secondary_region');
        if ($withSecondary && ! $hasSecondaryCol) {
            $this->error('--with-secondary requested but posts.editorial_secondary_region column not present. Run the S-LSAT-18b migration first.');
            return self::FAILURE;
        }

        $cols = ['id', 'name', 'description', 'editorial_region'];
        if ($hasSummary) {
            $cols[] = 'summary_nobuai';
        }
        if ($hasSecondaryCol) {
            $cols[] = 'editorial_secondary_region';
        }

        $scanned = 0;
        $changed = 0;
        $secondaryChanged = 0;
        $byBucket = ['africa' => 0, 'europe' => 0, 'americas' => 0, 'international' => 0];
        $bySecondaryBucket = ['africa' => 0, 'europe' => 0, 'americas' => 0];
        $byPrevious = [];
        $lastId = 0;

        while (true) {
            if ($limit !== null && $scanned >= $limit) {
                break;
            }
            $take = $limit !== null ? min($batch, $limit - $scanned) : $batch;

            $rows = DB::table('posts')
                ->where('status', 'published')
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->limit($take)
                ->get($cols);

            if ($rows->isEmpty()) {
                break;
            }

            $updates = [];
            $secondaryUpdates = [];  // [secondaryRegion => [postId, ...]]
            $secondaryClears = [];   // post IDs to NULL out
            foreach ($rows as $row) {
                $scanned++;
                $lastId = (int) $row->id;

                $all = GrimbaArticleRegion::detectAllFromText(
                    (string) ($row->name ?? ''),
                    (string) ($row->description ?? ''),
                    $hasSummary ? (string) ($row->summary_nobuai ?? '') : '',
                );
                $detected = $all['primary'] ?? null;

                if ($detected === null) {
                    continue;
                }

                $current = (string) ($row->editorial_region ?? '');
                $currentSecondary = $hasSecondaryCol ? (string) ($row->editorial_secondary_region ?? '') : '';

                if (! $reconsiderIntl && $current === 'international' && $detected !== 'international') {
                    // The default policy: leave international rows alone
                    // unless explicitly asked. They might be Pulse-tier
                    // global coverage where the keyword scan picks one
                    // region but the editorial intent is multi-regional.
                }

                // Primary update branch (unchanged contract).
                $primaryNeedsWrite = false;
                if ($current === $detected) {
                    if ($reconsiderCorrect) {
                        // Still count as scanned but don't bump $changed.
                    } else {
                        // Skip primary write — already correct.
                    }
                } elseif ($current !== 'international' || $reconsiderIntl) {
                    $primaryNeedsWrite = true;
                }

                if ($primaryNeedsWrite) {
                    $updates[$detected] = $updates[$detected] ?? [];
                    $updates[$detected][] = (int) $row->id;
                    $changed++;
                    $byBucket[$detected] = ($byBucket[$detected] ?? 0) + 1;
                    $byPrevious[$current] = ($byPrevious[$current] ?? 0) + 1;
                }

                // S-LSAT-18c — secondary backfill branch.
                if ($withSecondary) {
                    $newSecondary = $all['secondary'] ?? null;
                    if ($newSecondary === null) {
                        if ($currentSecondary !== '') {
                            $secondaryClears[] = (int) $row->id;
                            $secondaryChanged++;
                        }
                    } elseif ($newSecondary !== $currentSecondary) {
                        $secondaryUpdates[$newSecondary] = $secondaryUpdates[$newSecondary] ?? [];
                        $secondaryUpdates[$newSecondary][] = (int) $row->id;
                        $secondaryChanged++;
                        $bySecondaryBucket[$newSecondary] = ($bySecondaryBucket[$newSecondary] ?? 0) + 1;
                    }
                }
            }

            if ($apply && ! empty($updates)) {
                foreach ($updates as $region => $ids) {
                    DB::table('posts')->whereIn('id', $ids)->update([
                        'editorial_region' => $region,
                        'updated_at' => now(),
                    ]);
                }
            }
            if ($apply && $withSecondary) {
                foreach ($secondaryUpdates as $region => $ids) {
                    DB::table('posts')->whereIn('id', $ids)->update([
                        'editorial_secondary_region' => $region,
                        'updated_at' => now(),
                    ]);
                }
                if (! empty($secondaryClears)) {
                    DB::table('posts')->whereIn('id', $secondaryClears)->update([
                        'editorial_secondary_region' => null,
                        'updated_at' => now(),
                    ]);
                }
            }

            $this->line(sprintf(
                '... scanned=%d changed=%d lastId=%d — africa=+%d europe=+%d americas=+%d',
                $scanned,
                $changed,
                $lastId,
                $byBucket['africa'] ?? 0,
                $byBucket['europe'] ?? 0,
                $byBucket['americas'] ?? 0,
            ));
        }

        $this->newLine();
        $this->info(sprintf(
            '%sScanned %d post(s). Topic-driven retag: %d row(s) %s.',
            $apply ? '' : '[DRY-RUN] ',
            $scanned,
            $changed,
            $apply ? 'updated' : 'would have moved',
        ));
        $this->info(sprintf(
            'New buckets: africa=+%d europe=+%d americas=+%d',
            $byBucket['africa'] ?? 0,
            $byBucket['europe'] ?? 0,
            $byBucket['americas'] ?? 0,
        ));
        if (! empty($byPrevious)) {
            $this->info('Source tags overridden:');
            foreach ($byPrevious as $prev => $count) {
                $this->line('  ' . ($prev ?: '(null)') . ' → topic: ' . $count);
            }
        }

        if ($withSecondary) {
            $this->info(sprintf(
                '%sSecondary tags: %d row(s) %s.',
                $apply ? '' : '[DRY-RUN] ',
                $secondaryChanged,
                $apply ? 'updated' : 'would have moved',
            ));
            $this->info(sprintf(
                'New secondary buckets: africa=+%d europe=+%d americas=+%d',
                $bySecondaryBucket['africa'] ?? 0,
                $bySecondaryBucket['europe'] ?? 0,
                $bySecondaryBucket['americas'] ?? 0,
            ));
        }

        return self::SUCCESS;
    }
}
