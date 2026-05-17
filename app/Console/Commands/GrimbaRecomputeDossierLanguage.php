<?php

namespace App\Console\Commands;

use App\Support\GrimbaDossierLanguage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * S-LANG-12 — daily dossier-language recompute. Sweeps every cluster
 * whose `language_recomputed_at` is older than 24h (or never set) and
 * re-derives its modal language. Pure CPU; safe to run as a cron tick.
 */
class GrimbaRecomputeDossierLanguage extends Command
{
    protected $signature = 'grimba:recompute-dossier-language
        {--all : recompute every cluster, not just stale ones}
        {--since= : ISO datetime — only recompute clusters last recomputed before this point}';

    protected $description = 'Recompute story_clusters.primary_language from each cluster posts.';

    public function handle(): int
    {
        if (! Schema::hasColumn('story_clusters', 'primary_language')) {
            $this->error('story_clusters.primary_language column not present. Run migration 2026_05_16_180000_add_primary_language_to_story_clusters_table.php first.');
            return self::FAILURE;
        }

        $sinceOption = $this->option('since');
        $since = null;
        if ($this->option('all')) {
            $since = null;
        } elseif ($sinceOption) {
            $since = Carbon::parse($sinceOption);
        } else {
            $since = Carbon::now()->subDay();
        }

        $this->info('Sweeping clusters' . ($since ? " not recomputed since {$since->toIso8601String()}" : ' — full pass'));

        $counts = GrimbaDossierLanguage::recomputeStale($since);

        $this->table(
            ['Bucket', 'Count'],
            [
                ['fr',         $counts['fr']],
                ['en',         $counts['en']],
                ['unknown',    $counts['unknown']],
                ['Processed',  $counts['processed']],
            ]
        );

        return self::SUCCESS;
    }
}
