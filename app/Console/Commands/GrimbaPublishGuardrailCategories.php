<?php

namespace App\Console\Commands;

use App\Support\GrimbaPostPublisher;
use Botble\Blog\Models\Category;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GrimbaPublishGuardrailCategories extends Command
{
    protected $signature = 'grimba:publish-guardrail-categories
        {--threshold= : credibility_score minimum used by trusted auto-publish}
        {--limit=0 : cap posts per bucket, 0 = no cap}
        {--dry-run : preview without writing}';

    protected $description = 'Publish low-credibility and unclassified-bias draft posts into explicit review categories.';

    public function handle(): int
    {
        $threshold = $this->option('threshold') !== null
            ? (int) $this->option('threshold')
            : (int) setting('grimba_autopub_min_credibility', 70);
        $limit = max(0, (int) $this->option('limit'));
        $dry = (bool) $this->option('dry-run');

        $buckets = [
            'trusted_source_credibility' => [
                'name' => 'Trusted Source Credibility',
                'description' => 'Published from sources below the trusted-source credibility threshold; review credibility before featuring.',
                'order' => 16,
                'icon' => 'ti ti-shield-question',
                'query' => fn () => DB::table('posts')
                    ->join('news_sources', 'news_sources.id', '=', 'posts.source_id')
                    ->where('posts.status', 'draft')
                    ->whereIn('news_sources.bias_rating', ['left', 'center', 'right'])
                    ->where(function ($q) use ($threshold): void {
                        $q->whereNull('news_sources.credibility_score')
                            ->orWhere('news_sources.credibility_score', '<', $threshold);
                    }),
            ],
            'unclassified_source_bias' => [
                'name' => 'Unclassified Source Bias',
                'description' => 'Published from sources whose political/source bias is not yet classified; review source metadata.',
                'order' => 17,
                'icon' => 'ti ti-scale-off',
                'query' => fn () => DB::table('posts')
                    ->leftJoin('news_sources', 'news_sources.id', '=', 'posts.source_id')
                    ->where('posts.status', 'draft')
                    ->where(function ($q): void {
                        $q->whereNull('posts.source_id')
                            ->orWhereNull('news_sources.bias_rating')
                            ->orWhereNotIn('news_sources.bias_rating', ['left', 'center', 'right']);
                    }),
            ],
        ];

        $total = 0;
        $rows = [];

        foreach ($buckets as $bucket) {
            $query = $bucket['query']()
                ->orderByDesc('posts.id')
                ->select('posts.id');

            if ($limit > 0) {
                $query->limit($limit);
            }

            $ids = $query->pluck('posts.id')->map(fn ($id) => (int) $id)->all();
            $rows[] = [$bucket['name'], count($ids)];

            if ($dry || empty($ids)) {
                $total += count($ids);
                continue;
            }

            $categoryId = $this->ensureCategory($bucket);

            DB::transaction(function () use ($ids, $categoryId): void {
                foreach ($ids as $postId) {
                    $exists = DB::table('post_categories')
                        ->where('post_id', $postId)
                        ->where('category_id', $categoryId)
                        ->exists();

                    if (! $exists) {
                        DB::table('post_categories')->insert([
                            'post_id' => $postId,
                            'category_id' => $categoryId,
                        ]);
                    }
                }

                GrimbaPostPublisher::publishDrafts($ids);
            });

            $total += count($ids);
        }

        $this->table(['Category', $dry ? 'Would publish' : 'Published'], $rows);
        $this->info(($dry ? 'Matched ' : 'Published ') . $total . ' post(s).');

        return self::SUCCESS;
    }

    /**
     * @param array{name:string, description:string, order:int, icon:string} $bucket
     */
    private function ensureCategory(array $bucket): int
    {
        $now = now();
        $existing = DB::table('categories')->where('name', $bucket['name'])->first();

        if ($existing) {
            DB::table('categories')->where('id', $existing->id)->update([
                'description' => $bucket['description'],
                'status' => 'published',
                'order' => $bucket['order'],
                'icon' => $bucket['icon'],
                'updated_at' => $now,
            ]);

            $id = (int) $existing->id;
        } else {
            $id = (int) DB::table('categories')->insertGetId([
                'name' => $bucket['name'],
                'parent_id' => 0,
                'description' => $bucket['description'],
                'status' => 'published',
                'author_id' => 1,
                'author_type' => \Botble\ACL\Models\User::class,
                'icon' => $bucket['icon'],
                'order' => $bucket['order'],
                'is_featured' => 0,
                'is_default' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->ensureSlug($id, $bucket['name']);

        return $id;
    }

    private function ensureSlug(int $categoryId, string $name): void
    {
        if (Slug::query()
            ->where('reference_id', $categoryId)
            ->where('reference_type', Category::class)
            ->exists()) {
            return;
        }

        $base = Str::slug($name);
        if ($base === '') {
            return;
        }

        $slug = $base;
        $i = 2;
        while (Slug::query()->where('key', $slug)->where('reference_type', Category::class)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        Slug::query()->create([
            'key' => $slug,
            'reference_id' => $categoryId,
            'reference_type' => Category::class,
            'prefix' => SlugHelper::getPrefix(Category::class) ?? 'blog',
        ]);
    }
}
