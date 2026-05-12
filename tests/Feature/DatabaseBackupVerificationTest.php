<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PDO;
use Tests\TestCase;

class DatabaseBackupVerificationTest extends TestCase
{
    public function test_verify_backups_accepts_latest_gzipped_sqlite_backup(): void
    {
        $backupDir = $this->backupDir();
        File::ensureDirectoryExists($backupDir);

        try {
            $this->writeSqliteBackup($backupDir . '/grimbanews.20260512120000.sqlite.gz');

            $this->artisan('grimba:verify-backups', [
                '--backup-dir' => $backupDir,
                '--min' => 1,
            ])
                ->expectsOutputToContain('Backup store: 1 valid / 0 invalid')
                ->expectsOutputToContain('SQLite quick_check ok')
                ->expectsOutputToContain('Verified 1 backup artifact(s).')
                ->assertSuccessful();
        } finally {
            File::deleteDirectory($backupDir);
        }
    }

    public function test_verify_backups_fails_when_restore_smoke_finds_corruption(): void
    {
        $backupDir = $this->backupDir();
        File::ensureDirectoryExists($backupDir);

        try {
            $encoded = gzencode("SQLite format 3\0" . random_bytes(1048576 + 4096), 9);
            $this->assertIsString($encoded);
            file_put_contents($backupDir . '/grimbanews.20260512120000.sqlite.gz', $encoded);

            $this->artisan('grimba:verify-backups', [
                '--backup-dir' => $backupDir,
                '--min' => 1,
            ])
                ->expectsOutputToContain('Backup store: 1 valid / 0 invalid')
                ->expectsOutputToContain('SQLite quick_check failed')
                ->assertFailed();
        } finally {
            File::deleteDirectory($backupDir);
        }
    }

    private function backupDir(): string
    {
        return storage_path('framework/testing/grimba-backups-' . Str::lower(Str::random(8)));
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
}
