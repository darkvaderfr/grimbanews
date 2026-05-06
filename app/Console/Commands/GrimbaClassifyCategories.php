<?php

namespace App\Console\Commands;

use App\Services\GrimbaCategoryClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        {--category= : re-classify posts currently attached to this category id}
        {--limit=0 : cap posts per run (0 = no cap)}';

    protected $description = 'Classify posts into Afrique / International editorial categories.';

    public function handle(GrimbaCategoryClassifier $classifier): int
    {
        $force = (bool) $this->option('force');
        $limit = (int) $this->option('limit');
        $categoryId = max(0, (int) $this->option('category'));
        $category = null;
        $replace = $force || $categoryId > 0;

        $query = DB::table('posts')
            ->where('status', '!=', 'trash')
            ->orderByDesc('id');

        if ($categoryId > 0) {
            $category = DB::table('categories')->where('id', $categoryId)->first(['id', 'name']);
            if (! $category) {
                $this->error("Category {$categoryId} was not found.");
                return self::FAILURE;
            }

            $query->whereIn('id', function ($q) use ($categoryId): void {
                $q->select('post_id')
                    ->from('post_categories')
                    ->where('category_id', $categoryId);
            });
        } elseif (! $force) {
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
            $replace ? ' [replacing existing pivots]' : ''
        ));
        if ($category) {
            $this->line(sprintf('Category scope: %s (#%d)', $category->name, $category->id));
        }

        $applied = 0;
        $changed = 0;
        $unchanged = 0;
        $skipped = 0;
        $changes = [];
        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        foreach ($posts as $p) {
            $beforeIds = $this->postCategoryIds((int) $p->id);
            $catIds = $classifier->classify(
                (string) $p->name,
                $p->description,
                $p->source_name
            );
            $catIds = $this->normalizeCategoryIds($catIds);

            if (empty($catIds)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $afterIds = $replace
                ? $catIds
                : $this->normalizeCategoryIds(array_merge($beforeIds, $catIds));

            DB::transaction(function () use ($p, $catIds, $replace): void {
                if ($replace) {
                    DB::table('post_categories')->where('post_id', $p->id)->delete();
                }

                foreach ($catIds as $cid) {
                    DB::table('post_categories')->insertOrIgnore([
                        'category_id' => $cid,
                        'post_id'     => $p->id,
                    ]);
                }
            });

            if ($beforeIds !== $afterIds) {
                $changed++;
                if (count($changes) < 12) {
                    $changes[] = [
                        $p->id,
                        Str::limit((string) $p->name, 52),
                        $this->categoryList($beforeIds),
                        $this->categoryList($afterIds),
                    ];
                }
            } else {
                $unchanged++;
            }

            $applied++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($changes !== []) {
            $this->table(['Post', 'Title', 'Before', 'After'], $changes);
        }

        $this->info(sprintf(
            'Done. %d classified · %d changed · %d unchanged · %d skipped (no match).',
            $applied,
            $changed,
            $unchanged,
            $skipped
        ));
        return self::SUCCESS;
    }

    /**
     * @param array<int, int> $categoryIds
     * @return array<int, int>
     */
    private function normalizeCategoryIds(array $categoryIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $categoryIds)));
        sort($ids);

        return $ids;
    }

    /**
     * @return array<int, int>
     */
    private function postCategoryIds(int $postId): array
    {
        return $this->normalizeCategoryIds(
            DB::table('post_categories')
                ->where('post_id', $postId)
                ->pluck('category_id')
                ->all()
        );
    }

    /**
     * @param array<int, int> $categoryIds
     */
    private function categoryList(array $categoryIds): string
    {
        if ($categoryIds === []) {
            return 'none';
        }

        $names = DB::table('categories')
            ->whereIn('id', $categoryIds)
            ->pluck('name', 'id');

        return collect($categoryIds)
            ->map(fn (int $id): string => ($names[$id] ?? 'Category') . " #{$id}")
            ->implode(', ');
    }
}
