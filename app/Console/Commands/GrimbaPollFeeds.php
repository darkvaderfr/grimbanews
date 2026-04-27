<?php

namespace App\Console\Commands;

use App\Services\GrimbaRssPoller;
use Botble\Blog\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrimbaPollFeeds extends Command
{
    protected $signature = 'grimba:poll-feeds {--feed= : Only poll a specific rss_feeds.id}';

    protected $description = 'Poll active RSS/Atom feeds, dedup items, and create draft posts for editor review.';

    public function handle(GrimbaRssPoller $poller): int
    {
        $this->info('Polling GrimbaNews RSS feeds…');

        if ($feedId = $this->option('feed')) {
            $feed = \DB::table('rss_feeds')
                ->join('news_sources', 'news_sources.id', '=', 'rss_feeds.source_id')
                ->where('rss_feeds.id', (int) $feedId)
                ->first([
                    'rss_feeds.*',
                    'news_sources.name as source_name',
                    'news_sources.bias_rating as source_bias',
                ]);
            if (! $feed) {
                $this->error("No feed with id {$feedId}.");
                return self::FAILURE;
            }
            $summary = [$poller->pollOne($feed)];
        } else {
            $summary = $poller->pollAll();
        }

        $rows = [];
        $totalIngested = 0;
        foreach ($summary as $s) {
            $rows[] = [
                $s['feed_id'],
                $s['source_name'],
                $s['status'],
                $s['ingested'],
                $s['error'] ? \Illuminate\Support\Str::limit($s['error'], 60) : '',
            ];
            $totalIngested += $s['ingested'];
        }

        $this->table(['Feed', 'Source', 'Status', 'New drafts', 'Error'], $rows);
        $this->info(sprintf('Done. %d feed(s), %d new draft post(s).', count($summary), $totalIngested));

        if ($totalIngested > 0) {
            $this->retroCluster();
        }

        $this->flagUnhealthyFeeds();

        return self::SUCCESS;
    }

    /**
     * S95 — after each poll tick, retroactively attach any still
     * un-clustered drafts (from THIS tick or prior ticks) to an
     * existing story_cluster when title similarity crosses the
     * 0.35 Jaccard threshold. Zero HTTP cost; just DB + tokenising.
     *
     * This catches the "first match" lag: post A arrives, no
     * cluster yet. Post B arrives 30 min later on the same story,
     * gets clustered and creates the cluster. Without this step,
     * A stays orphaned until the nightly recluster.
     */
    private function retroCluster(): void
    {
        $orphans = Post::query()
            ->whereNull('story_cluster_id')
            ->whereIn('status', ['draft', 'published'])
            ->where('created_at', '>=', now()->subDays(3))
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'name']);

        if ($orphans->isEmpty()) {
            return;
        }

        $attached = 0;
        foreach ($orphans as $p) {
            // S132 — findOrFormCluster handles both existing-cluster
            // match AND orphan-orphan formation. Note: the latter
            // already updates DB rows itself (atomically inside the
            // helper's transaction), so we only need to update THIS
            // post's row when the helper returned a non-null id and
            // we haven't been moved already.
            $cluster = GrimbaRssPoller::findOrFormCluster(
                (string) $p->name,
                30,
                0.30,
                false,
                null,
                (int) $p->id,
            );
            if ($cluster === null) continue;

            // Re-read story_cluster_id — findOrFormCluster may have
            // already attached this post via the orphan sweep.
            $current = DB::table('posts')->where('id', $p->id)->value('story_cluster_id');
            if ($current === null) {
                DB::table('posts')
                    ->where('id', $p->id)
                    ->update(['story_cluster_id' => $cluster, 'updated_at' => now()]);
            }
            $attached++;
        }

        if ($attached > 0) {
            $this->line(sprintf('  ↳ retro-clustered %d draft(s) to existing story_clusters.', $attached));
        }
    }

    /**
     * Log + echo a warning for feeds that have missed 5+ polls in a row.
     * A scheduled run still returns success so a single upstream flap
     * doesn't mark the whole schedule:run as failed; an editor can react
     * via the admin UI shipped in S72.
     */
    private function flagUnhealthyFeeds(int $threshold = 5): void
    {
        $sick = DB::table('rss_feeds')
            ->join('news_sources', 'news_sources.id', '=', 'rss_feeds.source_id')
            ->where('rss_feeds.is_active', true)
            ->where('rss_feeds.consecutive_failures', '>=', $threshold)
            ->orderByDesc('rss_feeds.consecutive_failures')
            ->get([
                'rss_feeds.id',
                'rss_feeds.url',
                'rss_feeds.consecutive_failures',
                'rss_feeds.last_error',
                'news_sources.name as source_name',
            ]);

        if ($sick->isEmpty()) {
            return;
        }

        $this->warn(sprintf(
            '⚠ %d feed(s) have failed %d+ times in a row — editor action needed:',
            $sick->count(),
            $threshold
        ));

        foreach ($sick as $f) {
            $msg = sprintf(
                '  feed #%d (%s) — %d consecutive failures — %s',
                $f->id,
                $f->source_name,
                $f->consecutive_failures,
                \Illuminate\Support\Str::limit((string) $f->last_error, 80)
            );
            $this->line($msg);
            Log::warning('[grimba:poll-feeds] unhealthy feed', [
                'feed_id'              => $f->id,
                'source'               => $f->source_name,
                'url'                  => $f->url,
                'consecutive_failures' => $f->consecutive_failures,
                'last_error'           => $f->last_error,
            ]);
        }
    }
}
