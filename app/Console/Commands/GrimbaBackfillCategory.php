<?php

namespace App\Console\Commands;

use App\Services\GrimbaLiveNewsFetcher;
use App\Services\GrimbaNewsApiFetcher;
use App\Services\GrimbaRssPoller;
use App\Support\GrimbaEditorialCategories;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Vader directive 2026-05-16 — every editorial category needs at
 * least 500+ articles populated in its trailing-90-day window before
 * launch so each rail can be assessed and tested end-to-end.
 *
 * Strategy:
 *   1. For every canonical editorial topic category, count its posts
 *      published in the trailing 90 days.
 *   2. If short of `--target`, kick the ingest pipeline using
 *      category-scoped queries against NewsAPI + RSS + breaking-news
 *      providers until the threshold is met OR the per-category
 *      attempt budget is exhausted.
 *   3. Report a table of {category, before, after, gained, status}.
 *
 * Safe defaults:
 *   --target=500 (Vader's threshold)
 *   --window=90 days
 *   --max-runs-per-category=8 (avoids burning provider quotas in one tick)
 *   --dry-run for sizing without firing API calls
 */
class GrimbaBackfillCategory extends Command
{
    protected $signature = 'grimba:backfill-category
        {--target=500 : minimum article count per category}
        {--window=90 : trailing-day window in which to count articles}
        {--max-runs-per-category=8 : per-category fetch attempts before giving up}
        {--category= : only process this category name (skip the rest)}
        {--dry-run : count + report only, never call upstream providers}';

    protected $description = 'Backfill 500+ articles per editorial category for pre-launch testing.';

    public function handle(
        GrimbaNewsApiFetcher $newsapi,
        GrimbaLiveNewsFetcher $live,
        GrimbaRssPoller $rss
    ): int {
        $target  = max(1, (int) $this->option('target'));
        $window  = max(1, (int) $this->option('window'));
        $maxRuns = max(1, (int) $this->option('max-runs-per-category'));
        $only    = $this->option('category');
        $dry     = (bool) $this->option('dry-run');

        $since = Carbon::now()->subDays($window);

        $rows = collect(GrimbaEditorialCategories::topicRows())
            ->pluck('name')
            ->when($only, fn ($names) => $names->filter(fn ($n) => mb_strtolower($n) === mb_strtolower((string) $only)))
            ->values()
            ->all();

        if (! $rows) {
            $this->error('No editorial categories matched. Did you typo --category?');
            return self::FAILURE;
        }

        $this->info("Backfilling {$target}+ articles/category in trailing {$window}d window. " . ($dry ? '(DRY RUN)' : ''));
        $this->newLine();

        $report = [];
        foreach ($rows as $categoryName) {
            $category = Category::query()->where('name', $categoryName)->first();
            if (! $category) {
                $report[] = [$categoryName, '—', '—', '—', 'no_category_row'];
                continue;
            }

            $before = $this->countInWindow($category->id, $since);
            if ($before >= $target) {
                $report[] = [$categoryName, $before, $before, 0, 'ok'];
                continue;
            }

            if ($dry) {
                $deficit = $target - $before;
                $report[] = [$categoryName, $before, $before, 0, "needs_{$deficit}"];
                continue;
            }

            // Run the live + RSS pipeline up to maxRuns times, polling
            // the count each iteration. Stop early when target met.
            $runs = 0;
            $after = $before;
            $queriesForThisCategory = $this->queriesFor($categoryName);

            while ($after < $target && $runs < $maxRuns && $queriesForThisCategory) {
                $query = $queriesForThisCategory[$runs % count($queriesForThisCategory)];
                $this->line("  · [{$categoryName}] run " . ($runs + 1) . "/{$maxRuns} → \"{$query}\"");

                try {
                    // NewsAPI /everything per-query (free quota-aware). The
                    // command keeps it tight by running one focused query at a time
                    // rather than the configured everythingQueries() sweep.
                    if ($newsapi->isConfigured()) {
                        $newsapi->fetchEverythingPublic($query);
                    }
                    // Breaking-news lane (google-news) — opportunistic, no key needed.
                    $live->fetchAll(['google-news']);
                    // RSS poll runs on a schedule; skipping here to avoid
                    // re-polling 600+ feeds per category × per run.
                } catch (\Throwable $e) {
                    $this->warn("    fetch failed: {$e->getMessage()}");
                }

                $after = $this->countInWindow($category->id, $since);
                $runs++;
            }

            $status = $after >= $target ? 'reached' : 'short';
            $report[] = [$categoryName, $before, $after, $after - $before, $status];
        }

        $this->newLine();
        $this->table(
            ['Category', 'Before', 'After', 'Gained', 'Status'],
            $report
        );

        $shortCount = collect($report)->filter(fn ($r) => $r[4] === 'short' || str_starts_with((string) $r[4], 'needs_'))->count();
        return $shortCount === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function countInWindow(int $categoryId, Carbon $since): int
    {
        return (int) DB::table('post_categories')
            ->join('posts', 'posts.id', '=', 'post_categories.post_id')
            ->where('post_categories.category_id', $categoryId)
            ->where('posts.status', 'published')
            ->where('posts.created_at', '>=', $since)
            ->count();
    }

    /**
     * Topic-scoped seed queries per category. Each list is hand-tuned
     * against the editorial taxonomy in GrimbaEditorialCategories. Used
     * by NewsAPI's /everything endpoint primarily.
     *
     * @return array<int, string>
     */
    private function queriesFor(string $categoryName): array
    {
        $map = [
            'À la une'              => ['breaking news', 'top stories', 'urgent', "dernière minute"],
            'Politique'             => ['election', 'gouvernement', 'parlement', 'prime minister', 'congress'],
            'Économie'              => ['economy', 'inflation', 'GDP', 'jobs report', 'stock market'],
            'Monde'                 => ['United Nations', 'foreign policy', 'diplomacy', 'sanctions'],
            'Géopolitique'          => ['NATO', 'sanctions', 'border conflict', 'military', 'arms control'],
            'Société'               => ['protest', 'social movement', 'education', 'inequality'],
            'Immigration'           => ['immigration', 'asylum', 'border policy', 'migrant', 'refugee'],
            'Justice'               => ['court ruling', 'verdict', 'trial', 'investigation', 'prosecution'],
            'Tech & Numérique'      => ['artificial intelligence', 'AI regulation', 'data breach', 'cybersecurity', 'big tech'],
            'Climat & Environnement' => ['climate change', 'emissions', 'wildfire', 'flood', 'biodiversity', 'COP30'],
            'Santé'                 => ['public health', 'WHO', 'pandemic', 'vaccine', 'hospital'],
            'Sciences'              => ['research study', 'NASA', 'space mission', 'discovery', 'physics breakthrough'],
            'Sports'                => ['Premier League', 'NBA', 'World Cup', 'Olympics', 'transfer window'],
            'Culture'               => ['film festival', 'museum', 'literature prize', 'music release', 'box office'],
        ];

        return $map[$categoryName] ?? [];
    }
}
