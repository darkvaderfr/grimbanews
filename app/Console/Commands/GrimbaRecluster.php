<?php

namespace App\Console\Commands;

use App\Services\GrimbaRssPoller;
use Botble\Blog\Models\Post;
use Illuminate\Console\Command;

class GrimbaRecluster extends Command
{
    protected $signature = 'grimba:recluster
        {--dry-run : Show what would change without writing}
        {--threshold=0.35 : Minimum Jaccard score to auto-attach}
        {--lookback=30 : Days back to scan for candidate clusters}';

    protected $description = 'Retroactively attach un-clustered draft / published posts to an existing story_cluster_id when title similarity crosses threshold.';

    public function handle(): int
    {
        $dry       = (bool) $this->option('dry-run');
        $threshold = (float) $this->option('threshold');
        $lookback  = (int) $this->option('lookback');

        $candidates = Post::query()
            ->whereNull('story_cluster_id')
            ->whereIn('status', ['draft', 'published'])
            ->orderBy('id')
            ->get(['id', 'name', 'status', 'source_name']);

        $this->info(sprintf(
            'Scanning %d un-clustered post(s), threshold=%.2f, lookback=%d days%s.',
            $candidates->count(),
            $threshold,
            $lookback,
            $dry ? ' (DRY-RUN)' : ''
        ));

        $attached = 0;
        $rows = [];

        foreach ($candidates as $p) {
            // S132 — try existing-cluster match AND orphan-orphan
            // formation. dryRun honors --dry-run on the command so
            // we don't write story_clusters during a preview.
            $cluster = GrimbaRssPoller::findOrFormCluster($p->name, $lookback, $threshold, $dry);
            if ($cluster === null) continue;
            if ($cluster < 0) {
                // Dry preview sentinel — note "would form/attach"
                // without resolving to a real cluster id.
                $cluster = 0;
            }

            $rows[] = [
                $p->id,
                $p->status,
                \Illuminate\Support\Str::limit($p->source_name ?? '—', 12),
                \Illuminate\Support\Str::limit($p->name, 60),
                '#' . $cluster,
            ];

            if (! $dry) {
                $p->story_cluster_id = $cluster;
                $p->save();
            }
            $attached++;
        }

        if (! empty($rows)) {
            $this->table(['Post', 'Status', 'Source', 'Title', 'Cluster'], $rows);
        }

        $this->info($dry
            ? "Would have attached {$attached} post(s). Re-run without --dry-run to apply."
            : "Attached {$attached} post(s) to existing clusters."
        );

        return self::SUCCESS;
    }
}
