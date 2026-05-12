<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImageProxyCachePruneTest extends TestCase
{
    public function test_prune_img_proxy_cache_deletes_only_expired_files(): void
    {
        $cacheDir = $this->cacheDir();
        File::ensureDirectoryExists($cacheDir);

        try {
            $oldBin = $cacheDir . '/old.bin';
            $oldMeta = $cacheDir . '/old.bin.type';
            $freshBin = $cacheDir . '/fresh.bin';
            $freshMeta = $cacheDir . '/fresh.bin.type';

            file_put_contents($oldBin, str_repeat('a', 1024));
            file_put_contents($oldMeta, 'image/jpeg');
            file_put_contents($freshBin, str_repeat('b', 2048));
            file_put_contents($freshMeta, 'image/png');

            $oldTimestamp = now()->subDays(61)->getTimestamp();
            touch($oldBin, $oldTimestamp);
            touch($oldMeta, $oldTimestamp);
            touch($freshBin, now()->subDays(2)->getTimestamp());
            touch($freshMeta, now()->subDays(2)->getTimestamp());

            $this->artisan('grimba:prune-img-proxy-cache', [
                '--cache-dir' => $cacheDir,
                '--days' => 60,
            ])
                ->expectsOutputToContain('Image proxy cache: 4 file(s)')
                ->expectsOutputToContain('Deleted 2 expired file(s)')
                ->assertSuccessful();

            $this->assertFileDoesNotExist($oldBin);
            $this->assertFileDoesNotExist($oldMeta);
            $this->assertFileExists($freshBin);
            $this->assertFileExists($freshMeta);
        } finally {
            File::deleteDirectory($cacheDir);
        }
    }

    public function test_prune_img_proxy_cache_dry_run_keeps_expired_files(): void
    {
        $cacheDir = $this->cacheDir();
        File::ensureDirectoryExists($cacheDir);

        try {
            $oldBin = $cacheDir . '/old.bin';
            file_put_contents($oldBin, str_repeat('a', 1024));
            touch($oldBin, now()->subDays(61)->getTimestamp());

            $this->artisan('grimba:prune-img-proxy-cache', [
                '--cache-dir' => $cacheDir,
                '--days' => 60,
                '--dry-run' => true,
            ])
                ->expectsOutputToContain('Would delete 1 expired file(s)')
                ->expectsOutputToContain('Dry-run only')
                ->assertSuccessful();

            $this->assertFileExists($oldBin);
        } finally {
            File::deleteDirectory($cacheDir);
        }
    }

    private function cacheDir(): string
    {
        return storage_path('framework/testing/img-proxy-' . Str::lower(Str::random(8)));
    }
}
