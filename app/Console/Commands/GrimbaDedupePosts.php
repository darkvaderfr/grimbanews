<?php

namespace App\Console\Commands;

use App\Services\GrimbaUrlCanonicalizer;
use Botble\Blog\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/*
 * S151b — merge duplicate posts.
 *
 * Several RSS feeds re-broadcast the same article with new GUID
 * fragments, so the (feed_id, guid) dedup ledger missed them and
 * we ingested the article 2-5 times. The cluster sweep then
 * grouped these duplicates as 5-article clusters that look like
 * multi-source coverage but are really one story copied N times.
 *
 * This command groups rss_feed_items by canonical_url_hash, picks
 * the OLDEST post per hash as the keeper, and deletes the rest
 * (post + slug + ledger rows + categories pivot). Dry-run by
 * default — pass --apply to actually delete.
 */
class GrimbaDedupePosts extends Command
{
    protected $signature = 'grimba:dedupe-posts
        {--apply : actually delete duplicates (otherwise dry-run)}
        {--limit=2000 : cap groups processed in one run}';

    protected $description = 'Merge duplicate posts that the RSS dedup ledger missed (S151b).';

    public function handle(GrimbaUrlCanonicalizer $canon): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');

        // Build duplicate groups: canonical_url_hash → array of post_ids
        $groups = DB::table('rss_feed_items')
            ->whereNotNull('canonical_url_hash')
            ->whereNotNull('post_id')
            ->select('canonical_url_hash', DB::raw('GROUP_CONCAT(post_id ORDER BY post_id ASC) as post_ids'), DB::raw('COUNT(*) as c'))
            ->groupBy('canonical_url_hash')
            ->having('c', '>', 1)
            ->orderByDesc('c')
            ->limit($limit)
            ->get();

        // Some duplicates lack ledger rows entirely (RSS-seeded
        // posts). Catch those by name+source_id grouping.
        $byName = DB::table('posts')
            ->select('name', 'source_id', DB::raw('GROUP_CONCAT(id ORDER BY id ASC) as post_ids'), DB::raw('COUNT(*) as c'))
            ->groupBy('name', 'source_id')
            ->having('c', '>', 1)
            ->orderByDesc('c')
            ->limit($limit)
            ->get();

        $this->info(sprintf(
            'Duplicate groups: %d (by URL hash) + %d (by name+source) %s',
            $groups->count(), $byName->count(), $apply ? '[APPLY]' : '[DRY RUN]'
        ));

        $totalDeleted = 0;
        $totalKept = 0;

        foreach ([$groups, $byName] as $set) {
            foreach ($set as $g) {
                $ids = array_unique(array_filter(array_map('intval', explode(',', (string) $g->post_ids))));
                if (count($ids) < 2) continue;
                sort($ids);
                $keep = $ids[0];                  // oldest id wins
                $drop = array_slice($ids, 1);

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

        return self::SUCCESS;
    }
}
