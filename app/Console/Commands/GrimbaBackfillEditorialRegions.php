<?php

namespace App\Console\Commands;

use App\Ground\Regions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Classify every post.editorial_region from the source country it was
 * ingested with. One-shot backfill — once the column is populated, the
 * ingest pipelines keep it fresh on each new insert.
 *
 * Runs in batches so a 100k-post archive doesn't hold a single huge
 * transaction. Reports counts per region and post count touched.
 */
class GrimbaBackfillEditorialRegions extends Command
{
    protected $signature = 'grimba:backfill-editorial-regions
        {--batch=500 : posts per batch}
        {--limit= : optional cap on total posts processed}
        {--reclassify : update rows that already have a region set}
        {--dry : count only, no writes}';

    protected $description = 'Backfill posts.editorial_region from each post source country.';

    public function handle(): int
    {
        if (! Schema::hasColumn('posts', 'editorial_region')) {
            $this->error('posts.editorial_region column not present. Run the migration first:');
            $this->line('  php artisan migrate --path=database/migrations/2026_05_16_120000_add_editorial_region_to_posts_table.php');

            return self::FAILURE;
        }

        $batch = max(50, (int) $this->option('batch'));
        $limitOption = $this->option('limit');
        $limit = $limitOption !== null ? max(1, (int) $limitOption) : null;
        $reclassify = (bool) $this->option('reclassify');
        $dry = (bool) $this->option('dry');

        $touched = 0;
        $perRegion = ['africa' => 0, 'europe' => 0, 'americas' => 0, 'international' => 0];

        $lastId = 0;
        while (true) {
            if ($limit !== null && $touched >= $limit) {
                break;
            }

            $take = $limit !== null ? min($batch, $limit - $touched) : $batch;

            $rows = DB::table('posts')
                ->leftJoin('news_sources', 'news_sources.id', '=', 'posts.source_id')
                ->where('posts.id', '>', $lastId)
                ->when(! $reclassify, fn ($q) => $q->whereNull('posts.editorial_region'))
                ->orderBy('posts.id')
                ->limit($take)
                ->get([
                    'posts.id as id',
                    'posts.source_id as source_id',
                    'news_sources.country as src_country',
                ]);

            if ($rows->isEmpty()) {
                break;
            }

            $bucketed = [];
            foreach ($rows as $row) {
                $region = Regions::regionForCountry($row->src_country);
                $bucketed[$region][] = (int) $row->id;
                $perRegion[$region] = ($perRegion[$region] ?? 0) + 1;
                $touched++;
                $lastId = (int) $row->id;
            }

            if (! $dry) {
                foreach ($bucketed as $region => $ids) {
                    DB::table('posts')->whereIn('id', $ids)->update([
                        'editorial_region' => $region,
                    ]);
                }
            }

            $this->line(sprintf(
                '... %d touched (lastId=%d) — africa=%d europe=%d americas=%d international=%d',
                $touched,
                $lastId,
                $perRegion['africa'],
                $perRegion['europe'],
                $perRegion['americas'],
                $perRegion['international']
            ));
        }

        $this->info(sprintf(
            'Done. %s%d post(s) classified — africa=%d europe=%d americas=%d international=%d.',
            $dry ? '[DRY] ' : '',
            $touched,
            $perRegion['africa'],
            $perRegion['europe'],
            $perRegion['americas'],
            $perRegion['international']
        ));

        return self::SUCCESS;
    }
}
