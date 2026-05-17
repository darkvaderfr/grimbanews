<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
            'breaking_live' => [
                'label' => 'Live news providers',
                'command' => 'grimba:fetch-breaking',
                'expected_minutes' => 15,
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
            'freshness_watchdog' => [
                'label' => 'Freshness watchdog',
                'command' => 'grimba:ensure-daily-publish --min=12 --window-hours=24 --per-category-min=3 --category-window-hours=24 --categories=all --max-publish-per-category=5',
                'expected_minutes' => 30,
            ],
            'ops_health' => [
                'label' => 'Ops health guard',
                'command' => 'grimba:health --fail-on-risk --min-full-content-coverage=70 --min-category-published-24h=3 --category-freshness-scope=all',
                'expected_minutes' => 60,
            ],
            'backup_verify' => [
                'label' => 'Backup restore smoke',
                'command' => 'grimba:verify-backups --min=1',
                'expected_minutes' => 1440,
            ],
            'img_proxy_prune' => [
                'label' => 'Image proxy cache prune',
                'command' => 'grimba:prune-img-proxy-cache --days=60',
                'expected_minutes' => 1440,
            ],
            'lang_backfill' => [
                'label' => 'Origin-language backfill (S-LANG-04)',
                'command' => 'grimba:backfill-language',
                'expected_minutes' => 1440,
            ],
            'dossier_lang_recompute' => [
                'label' => 'Dossier primary-language recompute (S-LANG-12)',
                'command' => 'grimba:recompute-dossier-language',
                'expected_minutes' => 1440,
            ],
            'release_evidence_prune' => [
                'label' => 'Release evidence prune',
                'command' => 'grimba:prune-release-evidence --days=30 --keep=30',
                'expected_minutes' => 1440,
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
            'source_classifier' => [
                'label' => 'Source classifier',
                'command' => 'grimba:classify-sources --apply --sync-posts --min-confidence=80',
                'expected_minutes' => 1440,
            ],
            'vault_events_archive' => [
                'label' => 'Vault analytics archive',
                'command' => 'grimba:archive-vault-events',
                'expected_minutes' => 10080,
            ],
            'vault_digest_weekly' => [
                'label' => 'Vault email digest',
                'command' => 'grimba:vault-digests',
                'expected_minutes' => 10080,
            ],
            'saved_search_digest_weekly' => [
                'label' => 'Saved-search alerts',
                'command' => 'grimba:saved-search-digests',
                'expected_minutes' => 10080,
            ],
        ];
    }

    /**
     * Jobs that directly protect the public "recent articles every day" SLA.
     *
     * @return array<int, string>
     */
    public static function freshnessJobKeys(): array
    {
        return [
            'rss_ingest',
            'breaking_live',
            'publish_trusted',
            'publish_guardrails',
            'freshness_watchdog',
            'ops_health',
        ];
    }

    /**
     * Jobs that should make grimba:health fail loudly when they stop.
     *
     * @return array<int, string>
     */
    public static function healthJobKeys(): array
    {
        return [
            ...self::freshnessJobKeys(),
            'full_articles',
        ];
    }

    /**
     * @param array<int, string>|null $jobKeys
     * @return \Illuminate\Support\Collection<int, object>
     */
    public static function status(?array $jobKeys = null): Collection
    {
        if (! self::ready()) {
            return collect();
        }

        $allowed = $jobKeys === null ? null : array_flip($jobKeys);

        return collect(self::jobs())
            ->when($allowed !== null, fn (Collection $jobs) => $jobs->filter(
                fn (array $job, string $key): bool => isset($allowed[$key])
            ))
            ->map(function (array $job, string $key): object {
                $latest = DB::table('grimba_automation_runs')
                    ->where('job_key', $key)
                    ->orderByDesc('id')
                    ->first();

                $success = DB::table('grimba_automation_runs')
                    ->where('job_key', $key)
                    ->where('status', 'success')
                    ->whereNotNull('finished_at')
                    ->orderByDesc('finished_at')
                    ->orderByDesc('id')
                    ->first();

                $startedAt = $latest?->started_at ? Carbon::parse($latest->started_at) : null;
                $lastFinished = $latest?->finished_at ? Carbon::parse($latest->finished_at) : null;
                $lastSuccessAt = $success?->finished_at ? Carbon::parse($success->finished_at) : null;
                $lastObservedAt = $lastSuccessAt ?: ($lastFinished ?: $startedAt);
                $expectedMinutes = (int) $job['expected_minutes'];
                $staleAfterMinutes = max($expectedMinutes * 2, $expectedMinutes + 15);
                $staleCutoff = now()->subMinutes($staleAfterMinutes);
                $isStale = ! $lastObservedAt || $lastObservedAt->lt($staleCutoff);
                $isRunning = $latest?->status === 'running';
                $isStuck = $isRunning && $startedAt && $startedAt->lt($staleCutoff);
                $isFailed = $latest?->status === 'failed';

                return (object) [
                    'key' => $key,
                    'label' => $job['label'],
                    'command' => $job['command'],
                    'expected_minutes' => $expectedMinutes,
                    'stale_after_minutes' => $staleAfterMinutes,
                    'status' => $latest?->status ?: 'never',
                    'exit_code' => $latest?->exit_code,
                    'started_at' => $startedAt,
                    'finished_at' => $lastFinished,
                    'last_success_at' => $lastSuccessAt,
                    'last_observed_at' => $lastObservedAt,
                    'duration_ms' => $latest?->duration_ms,
                    'error_message' => $latest?->error_message,
                    'is_running' => $isRunning,
                    'is_stuck' => $isStuck,
                    'is_stale' => $isStale,
                    'is_failed' => $isFailed || $isStuck,
                ];
            })
            ->values();
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
