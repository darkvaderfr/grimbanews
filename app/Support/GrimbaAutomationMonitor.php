<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GrimbaAutomationMonitor
{
    /**
     * @return array<string, array{label: string, command: string, expected_minutes: int}>
     */
    public static function jobs(): array
    {
        return [
            'rss_ingest' => [
                'label' => 'RSS ingest',
                'command' => 'grimba:poll-feeds',
                'expected_minutes' => 30,
            ],
            'translate_fr' => [
                'label' => 'Translate to FR',
                'command' => 'grimba:translate-pending --to=fr --limit=50',
                'expected_minutes' => 30,
            ],
            'translate_en' => [
                'label' => 'Translate to EN',
                'command' => 'grimba:translate-pending --to=en --limit=50',
                'expected_minutes' => 30,
            ],
            'publish_trusted' => [
                'label' => 'Trusted publish',
                'command' => 'grimba:publish-trusted',
                'expected_minutes' => 30,
            ],
            'publish_guardrails' => [
                'label' => 'Guardrail publish',
                'command' => 'grimba:publish-guardrail-categories',
                'expected_minutes' => 30,
            ],
            'full_articles' => [
                'label' => 'Full article extraction',
                'command' => 'grimba:fetch-full-articles --limit=80',
                'expected_minutes' => 30,
            ],
            'nobuai_summaries' => [
                'label' => 'NobuAI insights',
                'command' => 'grimba:nobuai-summaries --limit=80',
                'expected_minutes' => 30,
            ],
            'nobuai_stale' => [
                'label' => 'NobuAI stale refresh',
                'command' => 'grimba:nobuai-summaries --stale --limit=25',
                'expected_minutes' => 30,
            ],
            'newsapi_fetch' => [
                'label' => 'NewsAPI sweep',
                'command' => 'grimba:fetch-newsapi',
                'expected_minutes' => 288,
            ],
        ];
    }

    public static function start(string $jobKey, string $command): ?int
    {
        if (! self::ready()) {
            return null;
        }

        try {
            return (int) DB::table('grimba_automation_runs')->insertGetId([
                'job_key' => $jobKey,
                'command' => $command,
                'status' => 'running',
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable) {
            return null;
        }
    }

    public static function finish(?int $runId, string $status, ?int $exitCode = null, ?string $error = null): void
    {
        if (! $runId || ! self::ready()) {
            return;
        }

        try {
            $run = DB::table('grimba_automation_runs')->where('id', $runId)->first(['started_at']);
            $startedAt = $run?->started_at ? \Carbon\Carbon::parse($run->started_at) : null;

            DB::table('grimba_automation_runs')
                ->where('id', $runId)
                ->update([
                    'status' => $status,
                    'exit_code' => $exitCode,
                    'finished_at' => now(),
                    'duration_ms' => $startedAt ? max(0, $startedAt->diffInMilliseconds(now())) : null,
                    'error_message' => $error,
                    'updated_at' => now(),
                ]);
        } catch (Throwable) {
            // Monitoring must never break the scheduler.
        }
    }

    public static function ready(): bool
    {
        try {
            return Schema::hasTable('grimba_automation_runs');
        } catch (Throwable) {
            return false;
        }
    }
}
