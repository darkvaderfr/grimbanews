<?php

namespace Tests\Feature;

use App\Support\GrimbaAutomationMonitor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AutomationScheduleTest extends TestCase
{
    public function test_grimba_daily_automation_pipeline_is_scheduled(): void
    {
        Artisan::call('schedule:list');

        $output = Artisan::output();

        $this->assertStringContainsString('grimba:poll-feeds', $output);
        $this->assertStringContainsString('grimba:publish-trusted', $output);
        $this->assertStringContainsString('grimba:publish-guardrail-categories', $output);
        $this->assertStringContainsString('grimba:ensure-daily-publish --min=12 --window-hours=24', $output);
        $this->assertStringContainsString('grimba:health --fail-on-risk', $output);
        $this->assertStringContainsString('grimba:verify-backups --min=1', $output);
        $this->assertStringContainsString('grimba:prune-img-proxy-cache --days=60', $output);
        $this->assertStringContainsString('grimba:prune-release-evidence --days=30 --keep=30', $output);
        $this->assertStringContainsString('grimba:fetch-full-articles --limit=80', $output);
        $this->assertStringContainsString('grimba:nobuai-summaries --limit=80', $output);
        $this->assertStringContainsString('grimba:nobuai-summaries --stale --limit=25', $output);
        $this->assertStringContainsString('grimba:translate-pending --to=fr --limit=50', $output);
        $this->assertStringContainsString('grimba:translate-pending --to=en --limit=50', $output);
    }

    public function test_monitor_registry_covers_all_tracked_scheduled_jobs(): void
    {
        $console = file_get_contents(base_path('routes/console.php'));
        $this->assertIsString($console);

        preg_match_all("/grimba_schedule_command\\('([^']+)',\\s*'([^']+)'\\)/", $console, $matches, PREG_SET_ORDER);
        $this->assertNotEmpty($matches);

        $jobs = GrimbaAutomationMonitor::jobs();

        foreach ($matches as $match) {
            $jobKey = $match[1];
            $command = $match[2];

            $this->assertArrayHasKey($jobKey, $jobs, "Missing monitor registry entry for {$jobKey}.");
            $this->assertSame($command, $jobs[$jobKey]['command'], "Monitor command drifted for {$jobKey}.");
            $this->assertGreaterThan(0, $jobs[$jobKey]['expected_minutes'], "Monitor interval must be positive for {$jobKey}.");
        }
    }

    public function test_automation_monitor_records_success_and_failure_runs(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('grimba_automation_runs')->delete();

        $successId = GrimbaAutomationMonitor::start('rss_ingest', 'grimba:poll-feeds');
        GrimbaAutomationMonitor::finish($successId, 'success', 0);

        $failureId = GrimbaAutomationMonitor::start('nobuai_summaries', 'grimba:nobuai-summaries --limit=80');
        GrimbaAutomationMonitor::finish($failureId, 'failed', 1, 'provider failed');

        $this->assertDatabaseHas('grimba_automation_runs', [
            'job_key' => 'rss_ingest',
            'command' => 'grimba:poll-feeds',
            'status' => 'success',
            'exit_code' => 0,
        ]);

        $this->assertDatabaseHas('grimba_automation_runs', [
            'job_key' => 'nobuai_summaries',
            'status' => 'failed',
            'exit_code' => 1,
            'error_message' => 'provider failed',
        ]);

        $this->assertSame(2, DB::table('grimba_automation_runs')->count());
    }

    public function test_monitor_treats_recent_background_start_as_observed_not_missed(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('grimba_automation_runs')->delete();

        DB::table('grimba_automation_runs')->insert([
            'job_key' => 'rss_ingest',
            'command' => 'grimba:poll-feeds',
            'status' => 'running',
            'started_at' => now()->subMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $status = GrimbaAutomationMonitor::status(['rss_ingest'])->first();

        $this->assertSame('running', $status->status);
        $this->assertNull($status->last_success_at);
        $this->assertNotNull($status->last_observed_at);
        $this->assertFalse($status->is_stale);
        $this->assertFalse($status->is_failed);
    }
}
