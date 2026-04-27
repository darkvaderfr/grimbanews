<?php

namespace Modules\NobuTranslation\Console\Commands;

use Illuminate\Console\Command;
use Modules\NobuTranslation\Support\NobuTranslator;
use Throwable;

class TestTranslationCommand extends Command
{
    protected $signature = 'nobu:translate
                            {text? : Source text to translate}
                            {--to= : Target locale}
                            {--from= : Source locale}
                            {--driver= : Override provider}
                            {--health : Print provider health and exit}';

    protected $description = 'Translate a string via NobuAI Translation.';

    public function handle(NobuTranslator $translator): int
    {
        if ($this->option('health')) {
            $this->info('NobuAI Translation health');
            foreach ($translator->health() as $key => $value) {
                $this->line(sprintf('  %-10s %s', $key, var_export($value, true)));
            }

            return self::SUCCESS;
        }

        $text = trim((string) $this->argument('text'));
        $to = trim((string) $this->option('to'));
        $from = $this->option('from') ? (string) $this->option('from') : null;
        $driver = $this->option('driver') ? (string) $this->option('driver') : null;

        if ($text === '' || $to === '') {
            $this->error('Usage: nobu:translate "<text>" --to=<locale> [--from=<locale>] [--driver=<key>]');

            return self::FAILURE;
        }

        try {
            $translated = $translator->translate($text, $to, $from, $driver);
        } catch (Throwable $e) {
            $this->error('translation failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->line($translated);

        return self::SUCCESS;
    }
}
