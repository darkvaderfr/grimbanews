<?php

namespace App\Console\Commands;

use App\Support\GrimbaDatabaseBackups;
use Illuminate\Console\Command;

/**
 * Wave YYYYYYYYYY (Vader 2026-05-23) — daily SQLite .backup creator.
 *
 * Closes the gap surfaced by the 2026-05-23 DR drill: the verifier
 * was scheduled but no CREATE step existed, leaving database/backups/
 * empty unless an operator created snapshots manually.
 *
 * Pairs with `grimba:verify-backups` which runs 10 minutes later in
 * the scheduler (routes/console.php).
 *
 * Filename convention matches the verifier's glob:
 *   grimbanews.YYYYMMDD-HHMMSS.sqlite
 * (literal period after `grimbanews`).
 *
 * Retention: --keep=14 (default) prunes older artifacts so the
 * backups directory doesn't run away on disk (each backup ~20 MB,
 * 14 = ~280 MB ceiling).
 */
class GrimbaCreateBackup extends Command
{
    protected $signature = 'grimba:create-backup
        {--keep=14 : retain this many newest artifacts, prune older ones}
        {--dry-run : show what would happen without writing}';

    protected $description = 'Create a SQLite .backup snapshot of the live grimbanews DB; prune older artifacts.';

    public function handle(): int
    {
        $dbPath = database_path('grimbanews.sqlite');
        $backupDir = GrimbaDatabaseBackups::defaultDir();
        $keep = max(1, (int) $this->option('keep'));
        $dry = (bool) $this->option('dry-run');

        if (! is_file($dbPath)) {
            $this->error('Live DB file not found: ' . $dbPath);
            return self::FAILURE;
        }

        if (! is_dir($backupDir)) {
            if ($dry) {
                $this->line('Would mkdir -p ' . $backupDir);
            } else {
                if (! @mkdir($backupDir, 0755, true) && ! is_dir($backupDir)) {
                    $this->error('Backup directory could not be created: ' . $backupDir);
                    return self::FAILURE;
                }
            }
        }

        $stamp = date('Ymd-His');
        $artifact = $backupDir . DIRECTORY_SEPARATOR . 'grimbanews.' . $stamp . '.sqlite';

        if ($dry) {
            $this->line('Would create: ' . $artifact);
            $this->line('Would keep newest ' . $keep . ' artifacts, prune older.');
            return self::SUCCESS;
        }

        // Use PHP's PDO + SQLite VACUUM-style backup. Safer than
        // shell-out because no user input touches the shell. The
        // sqlite3 .backup command works the same way under the
        // hood (page-level copy with WAL coordination).
        try {
            $src = new \PDO('sqlite:' . $dbPath);
            $src->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // VACUUM INTO writes a fully-consistent snapshot to the
            // target path. Works regardless of WAL state.
            $stmt = $src->prepare('VACUUM INTO :path');
            $stmt->execute([':path' => $artifact]);
            unset($src, $stmt);
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (! is_file($artifact) || filesize($artifact) < 1024) {
            $this->error('Backup artifact missing or too small: ' . $artifact);
            return self::FAILURE;
        }

        $size = GrimbaDatabaseBackups::formatBytes(filesize($artifact));
        $this->line('Created: ' . basename($artifact) . ' (' . $size . ')');

        // Prune older artifacts (keep newest $keep)
        $files = GrimbaDatabaseBackups::files($backupDir);
        $excess = array_slice($files, $keep);
        $pruned = 0;
        foreach ($excess as $oldFile) {
            if (@unlink($oldFile)) {
                $pruned++;
            }
        }
        if ($pruned > 0) {
            $this->line('Pruned ' . $pruned . ' older backup artifact(s) (keep=' . $keep . ').');
        }

        return self::SUCCESS;
    }
}
