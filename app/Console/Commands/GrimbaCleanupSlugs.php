<?php

namespace App\Console\Commands;

use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Blog\Models\Tag;
use Botble\Page\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GrimbaCleanupSlugs extends Command
{
    protected $signature = 'grimba:cleanup-slugs {--dry-run : Show what would be deleted without executing}';

    protected $description = 'Delete slug rows whose reference_id no longer exists in the target model (prevents 404s after re-seeds).';

    public function handle(): int
    {
        $models = [
            Post::class     => 'posts',
            Category::class => 'blog categories',
            Tag::class      => 'blog tags',
            Page::class     => 'pages',
        ];

        $totalDeleted = 0;

        foreach ($models as $modelClass => $label) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $ids = $modelClass::query()->pluck('id');
            $orphansQuery = DB::table('slugs')
                ->where('reference_type', $modelClass)
                ->whereNotIn('reference_id', $ids);

            $count = $orphansQuery->count();

            if ($count === 0) {
                $this->info("• {$label}: no orphan slugs.");
                continue;
            }

            if ($this->option('dry-run')) {
                $this->warn("• {$label}: {$count} orphan slugs would be deleted (dry-run).");
            } else {
                $orphansQuery->delete();
                $this->warn("• {$label}: {$count} orphan slugs deleted.");
                $totalDeleted += $count;
            }
        }

        $this->newLine();
        if ($this->option('dry-run')) {
            $this->comment('Dry-run only. Re-run without --dry-run to apply.');
        } else {
            $this->info("Done. {$totalDeleted} rows removed.");
        }

        return self::SUCCESS;
    }
}
