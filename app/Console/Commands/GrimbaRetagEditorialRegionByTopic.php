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
        {--include-correct : reconsider rows whose current tag agrees with topic (useful for warming the audit log)}';

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

        $hasSummary = Schema::hasColumn('posts', 'summary_nobuai');
        $cols = ['id', 'name', 'description', 'editorial_region'];
        if ($hasSummary) {
            $cols[] = 'summary_nobuai';
        }

        $scanned = 0;
        $changed = 0;
        $byBucket = ['africa' => 0, 'europe' => 0, 'americas' => 0, 'international' => 0];
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
            foreach ($rows as $row) {
                $scanned++;
                $lastId = (int) $row->id;

                $detected = GrimbaArticleRegion::detectFromText(
                    (string) ($row->name ?? ''),
                    (string) ($row->description ?? ''),
                    $hasSummary ? (string) ($row->summary_nobuai ?? '') : '',
                );

                if ($detected === null) {
                    continue;
                }

                $current = (string) ($row->editorial_region ?? '');

                if (! $reconsiderIntl && $current === 'international' && $detected !== 'international') {
                    // The default policy: leave international rows alone
                    // unless explicitly asked. They might be Pulse-tier
                    // global coverage where the keyword scan picks one
                    // region but the editorial intent is multi-regional.
                }

                if ($current === $detected && ! $reconsiderCorrect) {
                    continue;
                }

                if ($current === $detected && $reconsiderCorrect) {
                    // Still count it as scanned but don't write.
                    continue;
                }

                if ($current !== 'international' || $reconsiderIntl) {
                    $updates[$detected] = $updates[$detected] ?? [];
                    $updates[$detected][] = (int) $row->id;
                    $changed++;
                    $byBucket[$detected] = ($byBucket[$detected] ?? 0) + 1;
                    $byPrevious[$current] = ($byPrevious[$current] ?? 0) + 1;
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

        return self::SUCCESS;
    }
}
