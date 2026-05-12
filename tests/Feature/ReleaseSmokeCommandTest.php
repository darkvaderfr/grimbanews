<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReleaseSmokeCommandTest extends TestCase
{
    public function test_release_smoke_checks_public_urls_with_budgets(): void
    {
        $evidencePath = storage_path('framework/testing/release-smoke-pass.md');
        File::delete($evidencePath);

        Http::fake([
            'http://grimbanews.test/' => Http::response('<html>ok</html>', 200),
            'http://grimbanews.test/up' => Http::response('ok', 200),
            'http://grimbanews.test/feed.xml' => Http::response('<rss></rss>', 200),
        ]);

        $this->artisan('grimba:release-smoke', [
            '--base-url' => 'http://grimbanews.test',
            '--host-header' => 'grimbanews.test',
            '--evidence-path' => $evidencePath,
            '--skip-health' => true,
            '--skip-backups' => true,
            '--skip-cache' => true,
        ])
            ->expectsOutputToContain('homepage HTTP 200')
            ->expectsOutputToContain('health endpoint HTTP 200')
            ->expectsOutputToContain('public feed HTTP 200')
            ->expectsOutputToContain('release evidence written')
            ->expectsOutputToContain('Release smoke passed')
            ->assertSuccessful();

        Http::assertSentCount(3);
        $this->assertFileExists($evidencePath);
        $report = (string) File::get($evidencePath);
        $this->assertStringContainsString('# GrimbaNews Release Evidence', $report);
        $this->assertStringContainsString('Result: passed', $report);
        $this->assertStringContainsString('Host header: grimbanews.test', $report);
        $this->assertStringContainsString('| homepage | http | passed | HTTP 200', $report);
    }

    public function test_release_smoke_fails_on_bad_public_status(): void
    {
        $evidencePath = storage_path('framework/testing/release-smoke-fail.md');
        File::delete($evidencePath);

        Http::fake([
            'http://grimbanews.test/' => Http::response('error', 500),
            'http://grimbanews.test/up' => Http::response('ok', 200),
            'http://grimbanews.test/feed.xml' => Http::response('<rss></rss>', 200),
        ]);

        $this->artisan('grimba:release-smoke', [
            '--base-url' => 'http://grimbanews.test',
            '--evidence-path' => $evidencePath,
            '--skip-health' => true,
            '--skip-backups' => true,
            '--skip-cache' => true,
        ])
            ->expectsOutputToContain('homepage returned HTTP 500')
            ->expectsOutputToContain('release evidence written')
            ->expectsOutputToContain('Release smoke failed')
            ->assertFailed();

        $this->assertFileExists($evidencePath);
        $report = (string) File::get($evidencePath);
        $this->assertStringContainsString('Result: failed', $report);
        $this->assertStringContainsString('| homepage | http | failed | HTTP 500', $report);
    }
}
