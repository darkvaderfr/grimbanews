<?php

namespace App\Console\Commands;

use App\Services\GrimbaNobuAi;
use App\Services\GrimbaTranslator;
use Illuminate\Console\Command;
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
}
