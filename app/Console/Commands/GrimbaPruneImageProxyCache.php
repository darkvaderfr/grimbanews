<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class GrimbaPruneImageProxyCache extends Command
{
    protected $signature = 'grimba:prune-img-proxy-cache
        {--days=60 : delete image proxy cache files older than this many days}
        {--cache-dir= : image proxy cache directory; defaults to storage/app/public/img-proxy}
        {--dry-run : report what would be deleted without deleting files}';

    protected $description = 'Prune old GrimbaNews image proxy cache files and report cache footprint.';

    public function handle(): int
    {
        $cacheDir = (string) ($this->option('cache-dir') ?: storage_path('app/public/img-proxy'));
        $days = max(1, (int) $this->option('days'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days)->getTimestamp();

        if (! is_dir($cacheDir)) {
            $this->line('Image proxy cache directory not found: ' . $cacheDir);

            return self::SUCCESS;
        }

        $files = $this->files($cacheDir);
        $totalBytes = array_sum(array_map(fn (SplFileInfo $file): int => $file->getSize(), $files));
        $expired = array_values(array_filter(
            $files,
            fn (SplFileInfo $file): bool => $file->getMTime() < $cutoff
        ));
        $expiredBytes = array_sum(array_map(fn (SplFileInfo $file): int => $file->getSize(), $expired));
        $deleted = 0;

        if (! $dryRun) {
            foreach ($expired as $file) {
                if (@unlink($file->getPathname())) {
                    $deleted++;
                }
            }
        }

        $this->line(sprintf(
            'Image proxy cache: %d file(s), %s before prune.',
            count($files),
            $this->formatBytes($totalBytes)
        ));
        $this->line(sprintf(
            '%s %d expired file(s), %s reclaimable.',
            $dryRun ? 'Would delete' : 'Deleted',
            $dryRun ? count($expired) : $deleted,
            $this->formatBytes($expiredBytes)
        ));

        if ($dryRun) {
            $this->comment('Dry-run only. Re-run without --dry-run to prune.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<SplFileInfo>
     */
    private function files(string $cacheDir): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $files = [];

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile()) {
                $files[] = $file;
            }
        }

        return $files;
    }

    private function formatBytes(int|float $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 1) . 'GB';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . 'MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . 'KB';
        }

        return (int) $bytes . 'B';
    }
}
