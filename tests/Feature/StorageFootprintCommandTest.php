<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class StorageFootprintCommandTest extends TestCase
{
    public function test_storage_footprint_reports_tracked_application_areas(): void
    {
        $baseDir = $this->baseDir();
        File::ensureDirectoryExists($baseDir . '/database/backups');
        File::ensureDirectoryExists($baseDir . '/storage/logs');
        File::ensureDirectoryExists($baseDir . '/storage/app/public/img-proxy');
        File::ensureDirectoryExists($baseDir . '/storage/app/grimba-release-evidence');
        File::ensureDirectoryExists($baseDir . '/storage/framework/cache/data');
        File::ensureDirectoryExists($baseDir . '/storage/framework/views');
        File::ensureDirectoryExists($baseDir . '/public/storage');
        File::ensureDirectoryExists($baseDir . '/vendor/package');

        try {
            file_put_contents($baseDir . '/database/grimbanews.sqlite', str_repeat('d', 1024));
            file_put_contents($baseDir . '/database/backups/grimbanews.20260512120000.sqlite.gz', str_repeat('b', 2048));
            file_put_contents($baseDir . '/storage/logs/laravel.log', str_repeat('l', 512));
            file_put_contents($baseDir . '/storage/app/public/img-proxy/hero.bin', str_repeat('i', 4096));
            file_put_contents($baseDir . '/storage/app/grimba-release-evidence/release.md', str_repeat('r', 128));
            file_put_contents($baseDir . '/vendor/package/autoload.php', '<?php');

            $this->artisan('grimba:storage-footprint', [
                '--base-dir' => $baseDir,
                '--min-free-mb' => 0,
                '--fail-on-risk' => true,
            ])
                ->expectsOutputToContain('GrimbaNews storage footprint')
                ->expectsOutputToContain('database backups')
                ->expectsOutputToContain('image proxy cache')
                ->expectsOutputToContain('release evidence')
                ->expectsOutputToContain('Storage footprint floors are clear')
                ->assertSuccessful();
        } finally {
            File::deleteDirectory($baseDir);
        }
    }

    public function test_storage_footprint_can_fail_on_disk_floor(): void
    {
        $baseDir = $this->baseDir();
        File::ensureDirectoryExists($baseDir);

        try {
            $this->artisan('grimba:storage-footprint', [
                '--base-dir' => $baseDir,
                '--min-free-mb' => 999999999,
                '--fail-on-risk' => true,
            ])
                ->expectsOutputToContain('Disk free space below floor')
                ->assertFailed();
        } finally {
            File::deleteDirectory($baseDir);
        }
    }

    private function baseDir(): string
    {
        return storage_path('framework/testing/storage-footprint-' . Str::lower(Str::random(8)));
    }
}
