<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReleaseSmokeCommandTest extends TestCase
{
    public function test_release_smoke_checks_public_urls_with_budgets(): void
    {
        Http::fake([
            'http://grimbanews.test/' => Http::response('<html>ok</html>', 200),
            'http://grimbanews.test/up' => Http::response('ok', 200),
            'http://grimbanews.test/feed.xml' => Http::response('<rss></rss>', 200),
        ]);

        $this->artisan('grimba:release-smoke', [
            '--base-url' => 'http://grimbanews.test',
            '--host-header' => 'grimbanews.test',
            '--skip-health' => true,
            '--skip-backups' => true,
            '--skip-cache' => true,
        ])
            ->expectsOutputToContain('homepage HTTP 200')
            ->expectsOutputToContain('health endpoint HTTP 200')
            ->expectsOutputToContain('public feed HTTP 200')
            ->expectsOutputToContain('Release smoke passed')
            ->assertSuccessful();

        Http::assertSentCount(3);
    }

    public function test_release_smoke_fails_on_bad_public_status(): void
    {
        Http::fake([
            'http://grimbanews.test/' => Http::response('error', 500),
            'http://grimbanews.test/up' => Http::response('ok', 200),
            'http://grimbanews.test/feed.xml' => Http::response('<rss></rss>', 200),
        ]);

        $this->artisan('grimba:release-smoke', [
            '--base-url' => 'http://grimbanews.test',
            '--skip-health' => true,
            '--skip-backups' => true,
            '--skip-cache' => true,
        ])
            ->expectsOutputToContain('homepage returned HTTP 500')
            ->expectsOutputToContain('Release smoke failed')
            ->assertFailed();
    }
}
