<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GrimbaReleaseSmoke extends Command
{
    protected $signature = 'grimba:release-smoke
        {--base-url= : base URL to smoke; defaults to app.url}
        {--host-header= : optional Host header for IP-based production smoke}
        {--max-home-ms=3000 : maximum acceptable homepage response time}
        {--max-up-ms=1500 : maximum acceptable /up response time}
        {--max-feed-ms=3000 : maximum acceptable feed response time}
        {--min-free-mb=2048 : minimum free disk floor passed to grimba:health}
        {--min-full-content-coverage=70 : minimum full article coverage passed to grimba:health}
        {--evidence : write a markdown release evidence report under storage/app/grimba-release-evidence}
        {--evidence-path= : explicit markdown release evidence output path}
        {--skip-security-headers : skip homepage security header assertions}
        {--skip-health : skip grimba:health}
        {--skip-backups : skip grimba:verify-backups}
        {--skip-cache : skip image proxy cache dry-run}';

    protected $description = 'Run the GrimbaNews post-deploy release smoke: health, backup restore smoke, cache dry-run, and public URL budgets.';

    /**
     * @var array<int, array{label: string, type: string, result: string, detail: string}>
     */
    private array $checks = [];

    public function handle(): int
    {
        $failed = false;

        if (! (bool) $this->option('skip-health')) {
            $failed = $this->runArtisanCheck('health guard', 'grimba:health', [
                '--fail-on-risk' => true,
                '--min-full-content-coverage' => (int) $this->option('min-full-content-coverage'),
                '--min-free-mb' => (int) $this->option('min-free-mb'),
            ]) || $failed;
        }

        if (! (bool) $this->option('skip-backups')) {
            $failed = $this->runArtisanCheck('backup restore smoke', 'grimba:verify-backups', [
                '--min' => 1,
            ]) || $failed;
        }

        if (! (bool) $this->option('skip-cache')) {
            $failed = $this->runArtisanCheck('image proxy cache dry-run', 'grimba:prune-img-proxy-cache', [
                '--days' => 60,
                '--dry-run' => true,
            ]) || $failed;
        }

        $baseUrl = rtrim((string) ($this->option('base-url') ?: config('app.url')), '/');
        $headers = [];
        $hostHeader = trim((string) $this->option('host-header'));
        if ($hostHeader !== '') {
            $headers['Host'] = $hostHeader;
        }

        foreach ([
            ['label' => 'homepage', 'path' => '/', 'budget' => (int) $this->option('max-home-ms')],
            ['label' => 'health endpoint', 'path' => '/up', 'budget' => (int) $this->option('max-up-ms')],
            ['label' => 'public feed', 'path' => '/feed.xml', 'budget' => (int) $this->option('max-feed-ms')],
        ] as $check) {
            $failed = $this->runHttpCheck($baseUrl, $headers, $check['label'], $check['path'], $check['budget']) || $failed;
        }

        if ($this->shouldWriteEvidence()) {
            $failed = $this->writeEvidenceReport($failed, $baseUrl, $hostHeader) || $failed;
        }

        if ($failed) {
            $this->error('Release smoke failed.');

            return self::FAILURE;
        }

        $this->info('Release smoke passed.');

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function runArtisanCheck(string $label, string $command, array $parameters): bool
    {
        $exitCode = Artisan::call($command, $parameters);

        if ($exitCode !== self::SUCCESS) {
            $this->recordCheck($label, 'artisan', 'failed', sprintf('%s exited %d', $command, $exitCode));
            $this->error(sprintf('✗ %s failed: %s', $label, $command));

            return true;
        }

        $this->recordCheck($label, 'artisan', 'passed', sprintf('%s exited 0', $command));
        $this->info(sprintf('✓ %s passed', $label));

        return false;
    }

    /**
     * @param array<string, string> $headers
     */
    private function runHttpCheck(string $baseUrl, array $headers, string $label, string $path, int $budgetMs): bool
    {
        $url = $baseUrl . '/' . ltrim($path, '/');
        $started = microtime(true);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(max(1, (int) ceil(max($budgetMs, 1000) / 1000) + 2))
                ->get($url);
        } catch (\Throwable $e) {
            $this->recordCheck($label, 'http', 'failed', $e->getMessage());
            $this->error(sprintf('✗ %s failed: %s', $label, $e->getMessage()));

            return true;
        }

        $elapsedMs = (int) round((microtime(true) - $started) * 1000);
        $status = $response->status();

        if (! $response->successful()) {
            $this->recordCheck($label, 'http', 'failed', sprintf('HTTP %d in %dms', $status, $elapsedMs));
            $this->error(sprintf('✗ %s returned HTTP %d in %dms', $label, $status, $elapsedMs));

            return true;
        }

        if ($elapsedMs > $budgetMs) {
            $this->recordCheck($label, 'http', 'failed', sprintf('HTTP %d in %dms, budget %dms', $status, $elapsedMs, $budgetMs));
            $this->error(sprintf('✗ %s exceeded budget: %dms/%dms', $label, $elapsedMs, $budgetMs));

            return true;
        }

        $this->recordCheck($label, 'http', 'passed', sprintf('HTTP %d in %dms, budget %dms', $status, $elapsedMs, $budgetMs));
        $this->info(sprintf('✓ %s HTTP %d in %dms (budget %dms)', $label, $status, $elapsedMs, $budgetMs));

        if ($label === 'homepage' && ! (bool) $this->option('skip-security-headers')) {
            return $this->runSecurityHeaderCheck($response);
        }

        return false;
    }

    private function runSecurityHeaderCheck(\Illuminate\Http\Client\Response $response): bool
    {
        $requirements = [
            ['Content-Security-Policy', "default-src 'self'"],
            ['Content-Security-Policy', "frame-ancestors 'self'"],
            ['X-Content-Type-Options', 'nosniff'],
            ['X-Frame-Options', 'SAMEORIGIN'],
            ['Referrer-Policy', 'strict-origin-when-cross-origin'],
        ];

        $failures = [];
        foreach ($requirements as [$header, $expected]) {
            $value = (string) $response->header($header, '');
            if ($value === '' || ! Str::contains($value, $expected)) {
                $failures[] = $header . ' missing ' . $expected;
            }
        }

        if ((string) $response->header('Content-Security-Policy-Report-Only', '') !== '') {
            $failures[] = 'Content-Security-Policy-Report-Only must not be present';
        }

        if ($failures !== []) {
            $detail = implode('; ', $failures);
            $this->recordCheck('homepage security headers', 'headers', 'failed', $detail);
            $this->error('✗ homepage security headers failed: ' . $detail);

            return true;
        }

        $this->recordCheck('homepage security headers', 'headers', 'passed', 'CSP enforced with frame/content/referrer protections');
        $this->info('✓ homepage security headers passed');

        return false;
    }

    private function shouldWriteEvidence(): bool
    {
        return (bool) $this->option('evidence') || trim((string) $this->option('evidence-path')) !== '';
    }

    private function writeEvidenceReport(bool $failed, string $baseUrl, string $hostHeader): bool
    {
        $path = trim((string) $this->option('evidence-path'));
        if ($path === '') {
            $path = $this->defaultEvidencePath($failed);
        }

        try {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $this->renderEvidenceReport($failed, $baseUrl, $hostHeader));
        } catch (\Throwable $e) {
            $this->error('✗ release evidence write failed: ' . $e->getMessage());

            return true;
        }

        $this->info('✓ release evidence written: ' . $path);

        return false;
    }

    private function defaultEvidencePath(bool $failed): string
    {
        $revision = Str::slug($this->currentRevision());
        $status = $failed ? 'failed' : 'passed';

        return storage_path(sprintf(
            'app/grimba-release-evidence/grimbanews-release-%s-%s-%s.md',
            now()->format('Ymd-His'),
            $revision === '' ? 'unknown' : $revision,
            $status
        ));
    }

    private function renderEvidenceReport(bool $failed, string $baseUrl, string $hostHeader): string
    {
        $lines = [
            '# GrimbaNews Release Evidence',
            '',
            'Generated: ' . now()->toIso8601String(),
            'Environment: ' . app()->environment(),
            'Commit: ' . $this->currentRevision(),
            'Result: ' . ($failed ? 'failed' : 'passed'),
            'Base URL: ' . $baseUrl,
            'Host header: ' . ($hostHeader !== '' ? $hostHeader : 'none'),
            'Disk floor: ' . (int) $this->option('min-free-mb') . 'MB',
            'Full-content coverage floor: ' . (int) $this->option('min-full-content-coverage') . '%',
            '',
            '## Checks',
            '',
            '| Check | Type | Result | Detail |',
            '|---|---|---|---|',
        ];

        foreach ($this->checks as $check) {
            $lines[] = sprintf(
                '| %s | %s | %s | %s |',
                $this->markdownCell($check['label']),
                $this->markdownCell($check['type']),
                $this->markdownCell($check['result']),
                $this->markdownCell($check['detail'])
            );
        }

        $lines[] = '';

        return implode("\n", $lines);
    }

    private function currentRevision(): string
    {
        foreach (['SOURCE_VERSION', 'GIT_COMMIT', 'HEROKU_SLUG_COMMIT'] as $key) {
            $value = trim((string) env($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        $revisionFile = base_path('REVISION');
        if (is_file($revisionFile)) {
            $value = trim((string) @file_get_contents($revisionFile));
            if ($value !== '') {
                return $value;
            }
        }

        if (function_exists('exec')) {
            $output = [];
            $exitCode = 1;
            @exec('git -C ' . escapeshellarg(base_path()) . ' rev-parse --short HEAD 2>/dev/null', $output, $exitCode);
            if ($exitCode === 0 && trim((string) ($output[0] ?? '')) !== '') {
                return trim((string) $output[0]);
            }
        }

        return 'unknown';
    }

    private function recordCheck(string $label, string $type, string $result, string $detail): void
    {
        $this->checks[] = compact('label', 'type', 'result', 'detail');
    }

    private function markdownCell(string $value): string
    {
        return str_replace(["\r", "\n", '|'], [' ', ' ', '\\|'], $value);
    }
}
