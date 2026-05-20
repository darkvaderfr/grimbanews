<?php

namespace Tests\Feature;

use App\Services\GrimbaNewsApiFetcher;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PDO;
use Tests\TestCase;

class ReleaseSmokeCommandTest extends TestCase
{
    public function test_release_smoke_checks_public_urls_with_budgets(): void
    {
        $evidencePath = storage_path('framework/testing/release-smoke-pass.md');
        File::delete($evidencePath);

        Http::fake([
            'http://grimbanews.test/' => Http::response('<html>ok</html>', 200, $this->securityHeaders()),
            'http://grimbanews.test/up' => Http::response('ok', 200),
            'http://grimbanews.test/health' => Http::response($this->healthyPayload(), 200),
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
            ->expectsOutputToContain('homepage security headers passed')
            ->expectsOutputToContain('platform liveness endpoint HTTP 200')
            ->expectsOutputToContain('product health endpoint HTTP 200')
            ->expectsOutputToContain('product health payload passed')
            ->expectsOutputToContain('public feed HTTP 200')
            ->expectsOutputToContain('release evidence written')
            ->expectsOutputToContain('Release smoke passed')
            ->assertSuccessful();

        Http::assertSentCount(4);
        $this->assertFileExists($evidencePath);
        $report = (string) File::get($evidencePath);
        $this->assertStringContainsString('# GrimbaNews Release Evidence', $report);
        $this->assertStringContainsString('Result: passed', $report);
        $this->assertStringContainsString('Host header: grimbanews.test', $report);
        $this->assertStringContainsString('| homepage | http | passed | HTTP 200', $report);
        $this->assertStringContainsString('| product health endpoint | http | passed | HTTP 200', $report);
        $this->assertStringContainsString('| product health payload | json | passed | status ok, db up, service grimbanews |', $report);
        $this->assertStringContainsString('| homepage security headers | headers | passed | CSP enforced', $report);
    }

    public function test_release_smoke_fails_on_bad_public_status(): void
    {
        $evidencePath = storage_path('framework/testing/release-smoke-fail.md');
        File::delete($evidencePath);

        Http::fake([
            'http://grimbanews.test/' => Http::response('error', 500),
            'http://grimbanews.test/up' => Http::response('ok', 200),
            'http://grimbanews.test/health' => Http::response($this->healthyPayload(), 200),
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

    public function test_release_smoke_fails_when_homepage_security_headers_are_missing(): void
    {
        Http::fake([
            'http://grimbanews.test/' => Http::response('<html>ok</html>', 200),
            'http://grimbanews.test/up' => Http::response('ok', 200),
            'http://grimbanews.test/health' => Http::response($this->healthyPayload(), 200),
            'http://grimbanews.test/feed.xml' => Http::response('<rss></rss>', 200),
        ]);

        $this->artisan('grimba:release-smoke', [
            '--base-url' => 'http://grimbanews.test',
            '--skip-health' => true,
            '--skip-backups' => true,
            '--skip-cache' => true,
        ])
            ->expectsOutputToContain('homepage security headers failed: Content-Security-Policy')
            ->expectsOutputToContain('Release smoke failed')
            ->assertFailed();
    }

    public function test_release_smoke_fails_when_product_health_payload_is_degraded(): void
    {
        Http::fake([
            'http://grimbanews.test/' => Http::response('<html>ok</html>', 200, $this->securityHeaders()),
            'http://grimbanews.test/up' => Http::response('ok', 200),
            'http://grimbanews.test/health' => Http::response([
                'service' => 'grimbanews',
                'status' => 'degraded',
                'db' => 'down',
                'last_post_at' => null,
            ], 200),
            'http://grimbanews.test/feed.xml' => Http::response('<rss></rss>', 200),
        ]);

        $this->artisan('grimba:release-smoke', [
            '--base-url' => 'http://grimbanews.test',
            '--skip-health' => true,
            '--skip-backups' => true,
            '--skip-cache' => true,
        ])
            ->expectsOutputToContain('product health payload failed: status must be ok; db must be up')
            ->expectsOutputToContain('Release smoke failed')
            ->assertFailed();
    }

    public function test_release_smoke_accepts_backup_dir_for_restore_smoke(): void
    {
        $backupDir = storage_path('framework/testing/release-smoke-backups-' . Str::lower(Str::random(8)));
        File::ensureDirectoryExists($backupDir);
        $evidencePath = storage_path('framework/testing/release-smoke-backup-dir.md');
        File::delete($evidencePath);

        try {
            $this->writeSqliteBackup($backupDir . '/grimbanews.20260520120000.sqlite.gz');

            Http::fake([
                'http://grimbanews.test/' => Http::response('<html>ok</html>', 200, $this->securityHeaders()),
                'http://grimbanews.test/up' => Http::response('ok', 200),
                'http://grimbanews.test/health' => Http::response($this->healthyPayload(), 200),
                'http://grimbanews.test/feed.xml' => Http::response('<rss></rss>', 200),
            ]);

            $this->artisan('grimba:release-smoke', [
                '--base-url' => 'http://grimbanews.test',
                '--backup-dir' => $backupDir,
                '--evidence-path' => $evidencePath,
                '--skip-health' => true,
                '--skip-cache' => true,
            ])
                ->expectsOutputToContain('backup restore smoke passed')
                ->expectsOutputToContain('Release smoke passed')
                ->assertSuccessful();

            $report = (string) File::get($evidencePath);
            $this->assertStringContainsString('Backup dir: ' . $backupDir, $report);
            $this->assertStringContainsString('| backup restore smoke | artisan | passed | grimba:verify-backups exited 0 |', $report);
        } finally {
            File::deleteDirectory($backupDir);
            File::delete($evidencePath);
        }
    }

    public function test_release_smoke_can_require_newsapi_readiness(): void
    {
        $this->app->bind(GrimbaNewsApiFetcher::class, fn () => new class extends GrimbaNewsApiFetcher {
            public function __construct()
            {
            }

            public function isConfigured(): bool
            {
                return false;
            }
        });

        Http::fake([
            'http://grimbanews.test/' => Http::response('<html>ok</html>', 200, $this->securityHeaders()),
            'http://grimbanews.test/up' => Http::response('ok', 200),
            'http://grimbanews.test/health' => Http::response($this->healthyPayload(), 200),
            'http://grimbanews.test/feed.xml' => Http::response('<rss></rss>', 200),
        ]);

        $this->artisan('grimba:release-smoke', [
            '--base-url' => 'http://grimbanews.test',
            '--require-newsapi' => true,
            '--newsapi-recent-hours' => 24,
            '--skip-health' => true,
            '--skip-backups' => true,
            '--skip-cache' => true,
        ])
            ->expectsOutputToContain('NewsAPI readiness failed: grimba:newsapi-readiness')
            ->expectsOutputToContain('Release smoke failed')
            ->assertFailed();
    }

    /**
     * @return array<string, string|null>
     */
    private function healthyPayload(): array
    {
        return [
            'status' => 'ok',
            'service' => 'grimbanews',
            'time' => '2026-05-20T00:00:00+00:00',
            'db' => 'up',
            'last_post_at' => '2026-05-20 00:00:00',
        ];
    }

    private function writeSqliteBackup(string $gzPath): void
    {
        $sqlitePath = $gzPath . '.tmp';
        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->exec('CREATE TABLE backup_smoke (id INTEGER PRIMARY KEY, payload BLOB)');
        $pdo->exec('INSERT INTO backup_smoke (payload) VALUES (randomblob(1200000))');
        $pdo = null;

        $raw = file_get_contents($sqlitePath);
        $this->assertIsString($raw);

        $encoded = gzencode($raw, 9);
        $this->assertIsString($encoded);
        file_put_contents($gzPath, $encoded);
        @unlink($sqlitePath);
    }

    /**
     * @return array<string, string>
     */
    private function securityHeaders(): array
    {
        return [
            'Content-Security-Policy' => "default-src 'self'; frame-ancestors 'self'; object-src 'none'",
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ];
    }
}
