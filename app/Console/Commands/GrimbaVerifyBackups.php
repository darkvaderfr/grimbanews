<?php

namespace App\Console\Commands;

use App\Support\GrimbaDatabaseBackups;
use Illuminate\Console\Command;

class GrimbaVerifyBackups extends Command
{
    protected $signature = 'grimba:verify-backups
        {--backup-dir= : database backup directory to inspect; defaults to database/backups}
        {--min=1 : minimum valid backup artifacts required}
        {--all : verify every valid-looking artifact instead of only the newest one}';

    protected $description = 'Verify GrimbaNews SQLite backup artifacts with a restore smoke and PRAGMA quick_check.';

    public function handle(): int
    {
        $backupDir = (string) ($this->option('backup-dir') ?: GrimbaDatabaseBackups::defaultDir());
        $minimum = max(0, (int) $this->option('min'));
        $verifyAll = (bool) $this->option('all');
        $health = GrimbaDatabaseBackups::health($backupDir);

        if (! $health->available) {
            $this->error('Backup directory not found: ' . $health->dir);

            return $minimum > 0 ? self::FAILURE : self::SUCCESS;
        }

        $this->line(sprintf(
            'Backup store: %d valid / %d invalid · %s',
            $health->valid,
            count($health->invalid),
            GrimbaDatabaseBackups::formatBytes($health->size_bytes)
        ));

        if ($health->invalid !== []) {
            foreach ($health->invalid as $invalid) {
                $this->warn('Invalid artifact: ' . $invalid);
            }

            return self::FAILURE;
        }

        if ($health->valid < $minimum) {
            $this->error(sprintf('Backup floor breached: %d/%d valid artifact(s).', $health->valid, $minimum));

            return self::FAILURE;
        }

        $files = array_values(array_filter(
            GrimbaDatabaseBackups::files($health->dir),
            fn (string $file): bool => (int) (@filesize($file) ?: 0) >= GrimbaDatabaseBackups::MIN_BACKUP_BYTES
                && GrimbaDatabaseBackups::looksLikeSqliteBackup($file)
        ));

        if (! $verifyAll) {
            $files = array_slice($files, 0, 1);
        }

        if ($files === []) {
            $this->warn('No backup artifacts selected for restore smoke.');

            return $minimum > 0 ? self::FAILURE : self::SUCCESS;
        }

        $failed = false;
        foreach ($files as $file) {
            $result = GrimbaDatabaseBackups::verify($file);
            $line = basename($file) . ': ' . $result->message;

            if ($result->ok) {
                $this->info('✓ ' . $line);
            } else {
                $this->error('✗ ' . $line);
                $failed = true;
            }
        }

        if ($failed) {
            return self::FAILURE;
        }

        $this->info(sprintf('Verified %d backup artifact(s).', count($files)));

        return self::SUCCESS;
    }
}
