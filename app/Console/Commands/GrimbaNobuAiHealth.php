<?php

namespace App\Console\Commands;

use App\Services\GrimbaNobuAi;
use App\Services\GrimbaTranslator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GrimbaNobuAiHealth extends Command
{
    protected $signature = 'grimba:nobuai-health
        {--live : Make one short live provider call}
        {--prompt=Return exactly OK. : Prompt to use for --live}';

    protected $description = 'Verify NobuAI wrapper configuration and optionally make one live LLM provider call.';

    public function handle(GrimbaNobuAi $nobuAi, GrimbaTranslator $translator): int
    {
        $llmDrivers = $nobuAi->configuredDrivers();
        $translationDrivers = $translator->configuredDrivers();

        $this->line('NobuAI wrapper');
        $this->line('  LLM providers: ' . ($llmDrivers === [] ? 'none configured' : implode(', ', $llmDrivers)));
        $this->line('  Translation fallback chain: ' . ($translationDrivers === [] ? 'none configured' : implode(', ', $translationDrivers)));
        $this->line('  Story insights: ' . $this->storyInsightSummary());

        if (! $this->option('live')) {
            if ($llmDrivers === []) {
                $this->warn('No LLM provider keys are configured. NobuAI reader branding is present, but live LLM generation is not active locally.');
            }
            $this->comment('Re-run with --live after configuring an LLM key to test a real provider call.');

            return self::SUCCESS;
        }

        if ($llmDrivers === []) {
            $this->error('Cannot run live NobuAI test: no LLM provider keys configured.');
            return self::FAILURE;
        }

        $started = microtime(true);
        $result = $nobuAi->complete((string) $this->option('prompt'));
        $ms = (int) round((microtime(true) - $started) * 1000);

        if ($result === null) {
            $this->error("Live NobuAI call failed across configured providers ({$ms}ms).");
            return self::FAILURE;
        }

        $this->info(sprintf('Live NobuAI call OK via %s (%dms)', $result['driver'], $ms));
        $this->line('  Response: ' . Str::limit(str_replace(["\r", "\n"], ' ', $result['text']), 160));

        return self::SUCCESS;
    }

    private function storyInsightSummary(): string
    {
        if (! Schema::hasColumn('posts', 'summary_nobuai')) {
            return 'summary columns missing';
        }

        $clusters = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('story_cluster_id')
            ->selectRaw("
                story_cluster_id,
                COUNT(*) as post_count,
                MAX(CASE WHEN summary_nobuai IS NOT NULL AND summary_nobuai != '' THEN 1 ELSE 0 END) as has_summary
            ")
            ->groupBy('story_cluster_id')
            ->havingRaw('COUNT(*) >= 2')
            ->get();

        $ready = $clusters->where('has_summary', 1)->count();
        $pending = $clusters->count() - $ready;
        $latest = DB::table('posts')
            ->whereNotNull('summary_generated_at')
            ->max('summary_generated_at');

        return sprintf(
            '%d ready / %d pending%s',
            $ready,
            $pending,
            $latest ? ' · latest ' . $latest : ''
        );
    }
}
