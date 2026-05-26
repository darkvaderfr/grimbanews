<?php

namespace App\Console\Commands;

use App\Http\Controllers\GrimbaOgImageController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/*
 * Wave SSSSSSSSSSS (Vader 2026-05-26, Zen YELLOW from loop 11) —
 * OG share-card cache invalidation. The OG controller (home,
 * surface, story, post) writes 1200×630 PNGs to
 * storage/app/public/og/{name}.png and serves them with a 7-day
 * max-age header. When the source copy / accent / layout changes,
 * the cached PNG keeps serving the stale image until either
 * (a) an operator runs `rm storage/app/public/og/...` or (b) the
 * 7-day Cache-Control expires (and social network scrapers ignore
 * that anyway — they hold their own caches).
 *
 * This command is the documented invalidation surface. By default
 * it deletes the static cards (home + local + coffre +
 * juste-milieu) and lets the next request regenerate them. With
 * --include-articles it also wipes per-post + per-story caches,
 * but that's heavier — articles regenerate one-by-one on first
 * social-card request.
 */
class GrimbaRebuildOg extends Command
{
    protected $signature = 'grimba:rebuild-og
        {--include-articles : also delete per-post and per-story cards (heavy; defaults off)}
        {--dry-run : print what would be deleted without removing}';

    protected $description = 'Invalidate cached OG share-card PNGs so the next request regenerates them.';

    public function handle(): int
    {
        $ogDir = storage_path('app/public/og');
        if (! File::isDirectory($ogDir)) {
            $this->warn("OG cache directory does not exist: {$ogDir}");
            return self::SUCCESS;
        }

        $includeArticles = (bool) $this->option('include-articles');
        $dryRun = (bool) $this->option('dry-run');

        $staticCards = ['home.png', 'local.png', 'coffre.png', 'juste-milieu.png'];
        $deleted = [];
        $skipped = [];

        foreach (File::files($ogDir) as $file) {
            $name = $file->getFilename();

            $isStatic = in_array($name, $staticCards, true);
            $isPost = str_starts_with($name, 'post-');
            $isStory = str_starts_with($name, 'story-');

            $shouldDelete = $isStatic || ($includeArticles && ($isPost || $isStory));

            if ($shouldDelete) {
                if ($dryRun) {
                    $this->line("  would delete: {$name}");
                } else {
                    File::delete($file->getPathname());
                    $this->line("  deleted: {$name}");
                }
                $deleted[] = $name;
            } else {
                $skipped[] = $name;
            }
        }

        $verb = $dryRun ? 'would delete' : 'deleted';
        $this->info(sprintf('OG cache rebuild: %s %d file(s); kept %d.', $verb, count($deleted), count($skipped)));

        if (! $includeArticles && $skipped !== []) {
            $this->line('  hint: pass --include-articles to also wipe post-*.png + story-*.png caches.');
        }

        return self::SUCCESS;
    }
}
