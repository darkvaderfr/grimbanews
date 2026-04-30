<?php

namespace App\Console\Commands;

use App\Services\GrimbaCategoryClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/*
 * S165/S007 — backfill post_categories pivots using GrimbaCategoryClassifier.
 *
 * Walks all posts and runs each through the Afrique / International
 * edition classifier.
 * Default behaviour: only re-classify posts that have ZERO category
 * pivots (treats existing categorisation as sacred). --force re-runs
 * against everything, replacing prior category pivots.
 */
class GrimbaClassifyCategories extends Command
{
    protected $signature = 'grimba:classify-categories
        {--force : replace existing category pivots}
        {--limit=0 : cap posts per run (0 = no cap)}';

    protected $description = 'Classify posts into Afrique / International editorial categories.';

    public function handle(GrimbaCategoryClassifier $classifier): int
    {
        $force = (bool) $this->option('force');
        $limit = (int) $this->option('limit');

        $query = DB::table('posts')
            ->where('status', '!=', 'trash')
            ->orderByDesc('id');

        if (! $force) {
            $query->whereNotIn('id', function ($q) {
                $q->select('post_id')->from('post_categories');
            });
        }

        if ($limit > 0) $query->limit($limit);

        $posts = $query->get(['id', 'name', 'description', 'source_name']);

        if ($posts->isEmpty()) {
            $this->info('Nothing to classify.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Classifying %d post(s)%s…',
            $posts->count(),
            $force ? ' [FORCE — replacing existing pivots]' : ''
        ));

        $applied = 0;
        $skipped = 0;
        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        foreach ($posts as $p) {
            $catIds = $classifier->classify(
                (string) $p->name,
                $p->description,
                $p->source_name
            );

            if (empty($catIds)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            DB::transaction(function () use ($p, $catIds, $force): void {
                if ($force) {
                    DB::table('post_categories')->where('post_id', $p->id)->delete();
                }
                foreach ($catIds as $cid) {
                    DB::table('post_categories')->insertOrIgnore([
                        'category_id' => $cid,
                        'post_id'     => $p->id,
                    ]);
                }
            });

            $applied++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info(sprintf('Done. %d classified · %d skipped (no match).', $applied, $skipped));
        return self::SUCCESS;
    }
}
