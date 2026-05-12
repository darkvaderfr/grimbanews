<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

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
        {--skip-health : skip grimba:health}
        {--skip-backups : skip grimba:verify-backups}
        {--skip-cache : skip image proxy cache dry-run}';

    protected $description = 'Run the GrimbaNews post-deploy release smoke: health, backup restore smoke, cache dry-run, and public URL budgets.';

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
            $this->error(sprintf('✗ %s failed: %s', $label, $command));

            return true;
        }

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
            $this->error(sprintf('✗ %s failed: %s', $label, $e->getMessage()));

            return true;
        }

        $elapsedMs = (int) round((microtime(true) - $started) * 1000);
        $status = $response->status();

        if (! $response->successful()) {
            $this->error(sprintf('✗ %s returned HTTP %d in %dms', $label, $status, $elapsedMs));

            return true;
        }

        if ($elapsedMs > $budgetMs) {
            $this->error(sprintf('✗ %s exceeded budget: %dms/%dms', $label, $elapsedMs, $budgetMs));

            return true;
        }

        $this->info(sprintf('✓ %s HTTP %d in %dms (budget %dms)', $label, $status, $elapsedMs, $budgetMs));

        return false;
    }
}
