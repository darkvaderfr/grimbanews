<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
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
        $this->assertStringContainsString('grimba:fetch-full-articles --limit=40', $output);
        $this->assertStringContainsString('grimba:nobuai-summaries --limit=40', $output);
        $this->assertStringContainsString('grimba:nobuai-summaries --stale --limit=25', $output);
        $this->assertStringContainsString('grimba:translate-pending --to=fr --limit=50', $output);
        $this->assertStringContainsString('grimba:translate-pending --to=en --limit=50', $output);
    }
}
