<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class GrimbaPruneReleaseEvidence extends Command
{
    protected $signature = 'grimba:prune-release-evidence
        {--days=30 : delete release evidence files older than this many days}
        {--keep=30 : always keep this many newest release evidence files unless they exceed the age limit}
        {--evidence-dir= : release evidence directory; defaults to storage/app/grimba-release-evidence}
        {--dry-run : report what would be deleted without deleting files}';

    protected $description = 'Prune old GrimbaNews release smoke evidence files and report footprint.';

    public function handle(): int
    {
        $evidenceDir = (string) ($this->option('evidence-dir') ?: storage_path('app/grimba-release-evidence'));
        $days = max(1, (int) $this->option('days'));
        $keep = max(1, (int) $this->option('keep'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days)->getTimestamp();

        if (! is_dir($evidenceDir)) {
            $this->line('Release evidence directory not found: ' . $evidenceDir);

            return self::SUCCESS;
        }

        $files = $this->files($evidenceDir);
        usort($files, fn (SplFileInfo $a, SplFileInfo $b): int => $b->getMTime() <=> $a->getMTime());

        $totalBytes = array_sum(array_map(fn (SplFileInfo $file): int => $file->getSize(), $files));
        $expiredPaths = [];

        foreach ($files as $index => $file) {
            if ($file->getMTime() < $cutoff || $index >= $keep) {
                $expiredPaths[$file->getPathname()] = $file;
            }
        }

        $expired = array_values($expiredPaths);
        $expiredBytes = array_sum(array_map(fn (SplFileInfo $file): int => $file->getSize(), $expired));
        $deleted = 0;

        if (! $dryRun) {
            foreach ($expired as $file) {
                if (@unlink($file->getPathname())) {
                    $deleted++;
                }
            }
        }

        $deletedOrExpired = $dryRun ? count($expired) : $deleted;
        $retained = max(0, count($files) - $deletedOrExpired);

        $this->line(sprintf(
            'Release evidence: %d file(s), %s before prune.',
            count($files),
            $this->formatBytes($totalBytes)
        ));
        $this->line(sprintf(
            '%s %d evidence file(s), %s reclaimable.',
            $dryRun ? 'Would delete' : 'Deleted',
            $deletedOrExpired,
            $this->formatBytes($expiredBytes)
        ));
        $this->line(sprintf(
            'Retained %d file(s) (keep latest %d, max age %d day(s)).',
            $retained,
            $keep,
            $days
        ));

        if ($dryRun) {
            $this->comment('Dry-run only. Re-run without --dry-run to prune.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<SplFileInfo>
     */
    private function files(string $evidenceDir): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($evidenceDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $files = [];

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'md') {
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
