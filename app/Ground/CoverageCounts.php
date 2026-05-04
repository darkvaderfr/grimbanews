<?php

namespace App\Ground;

use Illuminate\Support\Facades\DB;

/**
 * S330 — coverage counts memoization.
 *
 * The home page's coverage-bar partial used to run one
 * `posts WHERE story_cluster_id = ?` query per card render. On a
 * /blog/{topic} page with 24 cards that's 24 round-trips. This helper
 * batches the lookup: first call for a given cluster_id triggers a
 * single query that fetches counts for that cluster AND any other
 * clusters the request has touched in the same memory window. Subsequent
 * calls are O(1) hash lookups.
 *
 * The memoization is process-local, not cached — counts must be
 * accurate within a request. Multi-request consistency is fine because
 * the counts only change on ingest, which is fast enough to invalidate
 * naturally within the cache windows of upstream features (briefing
 * card etc).
 */
class CoverageCounts
{
    /** @var array<int, array{left:int,center:int,right:int}> */
    private static array $cache = [];

    /** @var int[] cluster IDs queued by `for()` calls but not yet flushed. */
    private static array $queue = [];

    /**
     * Return a counts row for the cluster, fetching it (with any queued
     * companions) lazily on first read. After this call the row is in
     * the cache; later calls for the same id are O(1).
     *
     * @return array{left:int, center:int, right:int}
     */
    public static function get(?int $clusterId): array
    {
        if (! $clusterId) {
            return ['left' => 0, 'center' => 0, 'right' => 0];
        }

        if (! isset(self::$cache[$clusterId])) {
            self::$queue[$clusterId] = $clusterId;
            self::flush();
        }

        return self::$cache[$clusterId] ?? ['left' => 0, 'center' => 0, 'right' => 0];
    }

    /**
     * Hint that a set of cluster ids will be needed soon. Lets a
     * controller pre-warm the cache with one bulk query before the
     * card loop runs. Optional — falls back to per-cluster queueing.
     *
     * @param  iterable<int|string|null>  $clusterIds
     */
    public static function warm(iterable $clusterIds): void
    {
        foreach ($clusterIds as $cid) {
            $cid = (int) $cid;
            if ($cid > 0 && ! isset(self::$cache[$cid])) {
                self::$queue[$cid] = $cid;
            }
        }
        self::flush();
    }

    private static function flush(): void
    {
        if (self::$queue === []) {
            return;
        }

        $ids = array_values(self::$queue);
        self::$queue = [];

        $rows = DB::table('posts')
            ->whereIn('story_cluster_id', $ids)
            ->where('status', 'published')
            ->whereIn('bias_rating', ['left', 'center', 'right'])
            ->select('story_cluster_id', 'bias_rating', DB::raw('count(*) as c'))
            ->groupBy('story_cluster_id', 'bias_rating')
            ->get();

        // Seed every requested id with zeros so unmatched clusters
        // don't trigger another flush.
        foreach ($ids as $id) {
            self::$cache[$id] = ['left' => 0, 'center' => 0, 'right' => 0];
        }

        foreach ($rows as $row) {
            $id = (int) $row->story_cluster_id;
            if (! isset(self::$cache[$id])) {
                self::$cache[$id] = ['left' => 0, 'center' => 0, 'right' => 0];
            }
            $bias = $row->bias_rating;
            if (in_array($bias, ['left', 'center', 'right'], true)) {
                self::$cache[$id][$bias] = (int) $row->c;
            }
        }
    }
}
