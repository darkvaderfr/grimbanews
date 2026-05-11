<?php

namespace App\Console\Commands;

use App\Services\GrimbaUrlCanonicalizer;
use Botble\Blog\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/*
 * S151b — merge duplicate posts.
 *
 * Several RSS feeds re-broadcast the same article with new GUID
 * fragments, so the (feed_id, guid) dedup ledger missed them and
 * we ingested the article 2-5 times. The cluster sweep then
 * grouped these duplicates as 5-article clusters that look like
 * multi-source coverage but are really one story copied N times.
 *
 * This command groups rss_feed_items by source + canonical_url_hash,
 * picks the OLDEST post per hash as the keeper, and deletes the rest
 * (post + slug + ledger rows + categories pivot). Dry-run by default
 * — pass --apply to actually delete.
 *
 * Title-only duplicates are reported but skipped by default. Live
 * blogs and evergreen utility stories often reuse the same headline
 * across distinct canonical URLs, so deleting those requires the
 * explicit --include-title-groups flag after editorial review.
 */
class GrimbaDedupePosts extends Command
{
    protected $signature = 'grimba:dedupe-posts
        {--apply : actually delete duplicates (otherwise dry-run)}
        {--limit=2000 : cap groups processed in one run}
        {--source-id= : restrict duplicate scan to one news_sources.id}
        {--review-title-groups : print a non-destructive review report for same-title/same-source groups}
        {--include-title-groups : also process same-title/same-source groups without matching canonical URLs}';

    protected $description = 'Merge duplicate posts that the RSS dedup ledger missed (S151b).';

    public function handle(GrimbaUrlCanonicalizer $canon): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');
        $sourceId = $this->option('source-id') !== null ? (int) $this->option('source-id') : null;
        $reviewTitleGroups = (bool) $this->option('review-title-groups');
        $includeTitleGroups = (bool) $this->option('include-title-groups');

        // Build duplicate groups: source_id + canonical_url_hash → post_ids.
        // Grouping by source avoids folding syndicated cross-source coverage
        // into one article when two outlets legitimately link to the same URL.
        $groups = DB::table('rss_feed_items')
            ->join('posts', 'posts.id', '=', 'rss_feed_items.post_id')
            ->whereNotNull('canonical_url_hash')
            ->whereNotNull('post_id')
            ->when($sourceId, fn ($query) => $query->where('posts.source_id', $sourceId))
            ->select(
                'posts.source_id',
                'canonical_url_hash',
                DB::raw('GROUP_CONCAT(rss_feed_items.post_id ORDER BY rss_feed_items.post_id ASC) as post_ids'),
                DB::raw('COUNT(DISTINCT rss_feed_items.post_id) as c')
            )
            ->groupBy('posts.source_id', 'canonical_url_hash')
            ->having('c', '>', 1)
            ->orderByDesc('c')
            ->limit($limit)
            ->get();

        // Some duplicates lack ledger rows entirely (RSS-seeded
        // posts). Catch those by name+source_id grouping.
        $byName = DB::table('posts')
            ->when($sourceId, fn ($query) => $query->where('source_id', $sourceId))
            ->select(
                'name',
                'source_id',
                DB::raw('GROUP_CONCAT(id ORDER BY id ASC) as post_ids'),
                DB::raw('MIN(id) as first_post_id'),
                DB::raw('MAX(id) as latest_post_id'),
                DB::raw('COUNT(*) as c')
            )
            ->groupBy('name', 'source_id')
            ->having('c', '>', 1)
            ->orderByDesc('c')
            ->limit($limit)
            ->get();

        $this->info(sprintf(
            'Duplicate groups: %d actionable (by source+URL hash) + %d title-only review group(s) %s',
            $groups->count(),
            $byName->count(),
            $apply ? '[APPLY]' : '[DRY RUN]'
        ));
        if ($sourceId) {
            $this->line(sprintf('Scope: source_id=%d', $sourceId));
        }
        if ($reviewTitleGroups) {
            $this->renderTitleGroupReview($byName);

            return self::SUCCESS;
        }
        if ($byName->isNotEmpty() && ! $includeTitleGroups) {
            $this->warn('Title-only groups are skipped.');
            $this->line('Review first: grimba:dedupe-posts --review-title-groups');
        }

        $totalDeleted = 0;
        $totalKept = 0;
        $seenDropIds = [];

        $sets = [$groups];
        if ($includeTitleGroups) {
            $sets[] = $byName;
        }

        foreach ($sets as $set) {
            foreach ($set as $g) {
                $ids = array_unique(array_filter(array_map('intval', explode(',', (string) $g->post_ids))));
                $ids = array_values(array_filter($ids, fn (int $id): bool => ! isset($seenDropIds[$id])));
                if (count($ids) < 2) continue;
                sort($ids);
                $keep = $ids[0];                  // oldest id wins
                $drop = array_slice($ids, 1);

                foreach ($drop as $dropId) {
                    $seenDropIds[$dropId] = true;
                }

                $totalKept++;
                $totalDeleted += count($drop);

                if (! $apply) continue;

                DB::transaction(function () use ($keep, $drop): void {
                    // Re-point ledger rows that pointed at any of the
                    // dropped posts. Keep the keeper post linked.
                    DB::table('rss_feed_items')
                        ->whereIn('post_id', $drop)
                        ->update(['post_id' => $keep, 'updated_at' => now()]);

                    DB::table('newsapi_items')
                        ->whereIn('post_id', $drop)
                        ->update(['post_id' => $keep, 'updated_at' => now()]);

                    // Botble pivots + slugs + the post row itself.
                    DB::table('post_categories')->whereIn('post_id', $drop)->delete();
                    DB::table('post_tags')->whereIn('post_id', $drop)->delete();
                    DB::table('slugs')
                        ->whereIn('reference_id', $drop)
                        ->where('reference_type', Post::class)
                        ->delete();

                    // Finally drop the duplicate post rows.
                    DB::table('posts')->whereIn('id', $drop)->delete();
                });
            }
        }

        $this->info(sprintf(
            '%s %d duplicate post(s) across %d group(s).',
            $apply ? 'Deleted' : 'Would delete',
            $totalDeleted, $totalKept
        ));
        if (! $includeTitleGroups && $byName->isNotEmpty()) {
            $this->line(sprintf('Skipped %d title-only review group(s).', $byName->count()));
        }

        return self::SUCCESS;
    }

    private function renderTitleGroupReview(\Illuminate\Support\Collection $groups): void
    {
        $this->newLine();
        $this->info(sprintf('Title-only duplicate review: %d group(s) [DRY REVIEW]', $groups->count()));

        if ($groups->isEmpty()) {
            $this->line('No title-only duplicate groups found.');

            return;
        }

        $rows = $groups->map(function (object $group): array {
            $ids = DB::table('posts')
                ->where('name', $group->name)
                ->when(
                    $group->source_id,
                    fn ($query) => $query->where('source_id', $group->source_id),
                    fn ($query) => $query->whereNull('source_id')
                )
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();
            foreach ([(int) ($group->first_post_id ?? 0), (int) ($group->latest_post_id ?? 0)] as $fallbackId) {
                if ($fallbackId > 0 && ! in_array($fallbackId, $ids, true)) {
                    $ids[] = $fallbackId;
                }
            }
            sort($ids);
            $sourceName = $group->source_id
                ? (string) (DB::table('news_sources')->where('id', $group->source_id)->value('name') ?: 'source #' . $group->source_id)
                : '(no source)';

            return [
                'source_id' => $group->source_id ?: '-',
                'source' => Str::limit($sourceName, 28),
                'count' => (int) $group->c,
                'title' => Str::limit((string) $group->name, 54),
                'post_ids' => implode(',', $ids),
                'sample_urls' => Str::limit($this->sampleUrlsForPosts($ids)->implode(' | '), 120),
            ];
        })->all();

        $this->table(['source_id', 'source', 'count', 'title', 'post_ids', 'sample_urls'], $rows);
        foreach ($groups as $group) {
            $reviewIds = array_values(array_unique(array_filter([
                (int) ($group->first_post_id ?? 0),
                (int) ($group->latest_post_id ?? 0),
            ])));
            if (! empty($group->first_post_id)) {
                $this->line('review_post_id ' . (int) $group->first_post_id);
            }
            if (! empty($group->latest_post_id) && (int) $group->latest_post_id !== (int) $group->first_post_id) {
                $this->line('review_post_id ' . (int) $group->latest_post_id);
            }
            foreach ($this->sampleUrlsForPosts($reviewIds) as $url) {
                $this->line('review_url ' . $url);
            }
        }
        foreach ($rows as $row) {
            $this->line(sprintf(
                'review_group source_id=%s count=%d post_ids=%s sample_urls=%s',
                $row['source_id'],
                $row['count'],
                $row['post_ids'],
                $row['sample_urls'] ?: '-'
            ));
        }
        $this->warn('Review only. No posts were deleted. Use --include-title-groups only after confirming the URLs are true duplicates.');
    }

    /**
     * @param array<int, int> $postIds
     * @return \Illuminate\Support\Collection<int, string>
     */
    private function sampleUrlsForPosts(array $postIds): \Illuminate\Support\Collection
    {
        $urls = collect();

        if (Schema::hasTable('rss_feed_items')) {
            $urls = $urls->merge(
                DB::table('rss_feed_items')
                    ->whereIn('post_id', $postIds)
                    ->whereNotNull('link')
                    ->pluck('link')
            );
        }

        if (Schema::hasTable('newsapi_items')) {
            $urls = $urls->merge(
                DB::table('newsapi_items')
                    ->whereIn('post_id', $postIds)
                    ->whereNotNull('article_url')
                    ->pluck('article_url')
            );
        }

        return $urls
            ->map(fn ($url): string => (string) $url)
            ->filter()
            ->unique()
            ->take(3)
            ->values();
    }
}
