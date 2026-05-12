<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

class GrimbaStorageFootprint extends Command
{
    protected $signature = 'grimba:storage-footprint
        {--base-dir= : application directory to inspect; defaults to base_path()}
        {--min-free-mb=2048 : minimum free disk floor when --fail-on-risk is used}
        {--fail-on-risk : fail when disk free space is below --min-free-mb}';

    protected $description = 'Report GrimbaNews application storage footprint and disk headroom.';

    public function handle(): int
    {
        $baseDir = rtrim((string) ($this->option('base-dir') ?: base_path()), DIRECTORY_SEPARATOR);
        $minFreeMb = max(0, (int) $this->option('min-free-mb'));
        $failOnRisk = (bool) $this->option('fail-on-risk');

        if (! is_dir($baseDir)) {
            $this->error('Base directory not found: ' . $baseDir);

            return self::FAILURE;
        }

        $freeBytes = @disk_free_space($baseDir);
        $totalBytes = @disk_total_space($baseDir);
        $freeMb = is_int($freeBytes) || is_float($freeBytes) ? (int) floor($freeBytes / 1048576) : null;

        $this->line('GrimbaNews storage footprint');
        $this->line('  Base dir       : ' . $baseDir);
        $this->line(sprintf(
            '  Disk free      : %s%s (floor %dMB)',
            $freeMb === null ? 'unknown' : $freeMb . 'MB',
            is_int($totalBytes) || is_float($totalBytes) ? ' / ' . $this->formatBytes($totalBytes) : '',
            $minFreeMb
        ));

        $rows = [];
        foreach ($this->trackedPaths($baseDir) as $label => $path) {
            $footprint = $this->footprint($path);
            $rows[] = [
                'area' => $label,
                'path' => $footprint['exists'] ? $this->relativePath($baseDir, $path) : '(missing)',
                'files' => $footprint['files'],
                'bytes' => $footprint['bytes'],
                'note' => $footprint['readable'] ? '' : 'unreadable entries skipped',
            ];
        }

        usort($rows, function (array $a, array $b): int {
            return $b['bytes'] <=> $a['bytes'];
        });

        $this->table(['Area', 'Path', 'Files', 'Size', 'Note'], array_map(
            fn (array $row): array => [
                $row['area'],
                $row['path'],
                $row['files'],
                $this->formatBytes($row['bytes']),
                $row['note'],
            ],
            $rows
        ));

        if ($failOnRisk && $freeMb !== null && $freeMb < $minFreeMb) {
            $this->error(sprintf('Disk free space below floor: %dMB/%dMB.', $freeMb, $minFreeMb));

            return self::FAILURE;
        }

        if ($failOnRisk) {
            $this->info('Storage footprint floors are clear.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function trackedPaths(string $baseDir): array
    {
        return [
            'database' => $baseDir . '/database',
            'database backups' => $baseDir . '/database/backups',
            'storage logs' => $baseDir . '/storage/logs',
            'image proxy cache' => $baseDir . '/storage/app/public/img-proxy',
            'release evidence' => $baseDir . '/storage/app/grimba-release-evidence',
            'framework cache' => $baseDir . '/storage/framework/cache',
            'compiled views' => $baseDir . '/storage/framework/views',
            'public storage' => $baseDir . '/public/storage',
            'vendor' => $baseDir . '/vendor',
            'node_modules' => $baseDir . '/node_modules',
        ];
    }

    /**
     * @return array{exists: bool, readable: bool, files: int, bytes: int}
     */
    private function footprint(string $path): array
    {
        if (! file_exists($path)) {
            return ['exists' => false, 'readable' => true, 'files' => 0, 'bytes' => 0];
        }

        if (is_file($path)) {
            return [
                'exists' => true,
                'readable' => true,
                'files' => 1,
                'bytes' => (int) (@filesize($path) ?: 0),
            ];
        }

        $files = 0;
        $bytes = 0;
        $readable = true;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
            );
        } catch (UnexpectedValueException) {
            return ['exists' => true, 'readable' => false, 'files' => 0, 'bytes' => 0];
        }

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || $file->isLink() || ! $file->isFile()) {
                continue;
            }

            if (! $file->isReadable()) {
                $readable = false;
                continue;
            }

            $files++;
            $bytes += (int) $file->getSize();
        }

        return ['exists' => true, 'readable' => $readable, 'files' => $files, 'bytes' => $bytes];
    }

    private function relativePath(string $baseDir, string $path): string
    {
        return str_starts_with($path, $baseDir . DIRECTORY_SEPARATOR)
            ? substr($path, strlen($baseDir) + 1)
            : $path;
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
