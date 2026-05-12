<?php

namespace App\Support;

use PDO;
use Throwable;

class GrimbaDatabaseBackups
{
    public const MIN_BACKUP_BYTES = 1048576;

    public static function defaultDir(): string
    {
        return base_path('database/backups');
    }

    /**
     * @return object{
     *     available: bool,
     *     dir: string,
     *     valid: int,
     *     invalid: list<string>,
     *     size_bytes: int,
     *     latest_at: int|null
     * }
     */
    public static function health(?string $backupDir = null): object
    {
        $backupDir = rtrim($backupDir ?: self::defaultDir(), DIRECTORY_SEPARATOR);

        if (! is_dir($backupDir)) {
            return (object) [
                'available' => false,
                'dir' => $backupDir,
                'valid' => 0,
                'invalid' => [],
                'size_bytes' => 0,
                'latest_at' => null,
            ];
        }

        $valid = 0;
        $invalid = [];
        $sizeBytes = 0;
        $latestAt = null;

        foreach (self::files($backupDir) as $file) {
            $size = (int) (@filesize($file) ?: 0);
            $mtime = @filemtime($file) ?: null;
            $sizeBytes += $size;
            $latestAt = $mtime !== null ? max($latestAt ?? $mtime, $mtime) : $latestAt;

            if ($size < self::MIN_BACKUP_BYTES) {
                $invalid[] = basename($file) . ' (' . self::formatBytes($size) . ')';
                continue;
            }

            if (! self::looksLikeSqliteBackup($file)) {
                $invalid[] = basename($file) . ' (not readable SQLite)';
                continue;
            }

            $valid++;
        }

        return (object) [
            'available' => true,
            'dir' => $backupDir,
            'valid' => $valid,
            'invalid' => $invalid,
            'size_bytes' => $sizeBytes,
            'latest_at' => $latestAt,
        ];
    }

    /**
     * @return list<string>
     */
    public static function files(string $backupDir): array
    {
        $backupDir = rtrim($backupDir, DIRECTORY_SEPARATOR);

        $files = array_values(array_filter(array_merge(
            glob($backupDir . DIRECTORY_SEPARATOR . 'grimbanews.*.sqlite') ?: [],
            glob($backupDir . DIRECTORY_SEPARATOR . 'grimbanews.*.sqlite.gz') ?: []
        ), 'is_file'));

        usort($files, fn (string $a, string $b): int => ((int) (@filemtime($b) ?: 0)) <=> ((int) (@filemtime($a) ?: 0)));

        return $files;
    }

    public static function looksLikeSqliteBackup(string $file): bool
    {
        if (str_ends_with($file, '.gz')) {
            if (! function_exists('gzopen')) {
                return false;
            }

            $handle = @gzopen($file, 'rb');
            if (! $handle) {
                return false;
            }

            $header = @gzread($handle, 16);
            @gzclose($handle);

            return is_string($header) && str_starts_with($header, 'SQLite format 3');
        }

        $header = @file_get_contents($file, false, null, 0, 16);

        return is_string($header) && str_starts_with($header, 'SQLite format 3');
    }

    /**
     * @return object{ok: bool, file: string, message: string}
     */
    public static function verify(string $file, ?string $workDir = null): object
    {
        if (! is_file($file)) {
            return self::result($file, false, 'backup artifact not found');
        }

        $size = (int) (@filesize($file) ?: 0);
        if ($size < self::MIN_BACKUP_BYTES) {
            return self::result($file, false, 'backup artifact is too small: ' . self::formatBytes($size));
        }

        if (! self::looksLikeSqliteBackup($file)) {
            return self::result($file, false, 'backup artifact does not start with a SQLite header');
        }

        $temporaryPath = null;
        $sqlitePath = $file;

        if (str_ends_with($file, '.gz')) {
            $inflated = self::inflateGzip($file, $workDir);
            if (! $inflated->ok) {
                return self::result($file, false, $inflated->message);
            }

            $temporaryPath = $inflated->path;
            $sqlitePath = $inflated->path;
        }

        try {
            $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $result = $pdo->query('PRAGMA quick_check')?->fetchColumn();

            if ($result !== 'ok') {
                return self::result($file, false, 'SQLite quick_check failed: ' . (string) $result);
            }

            return self::result($file, true, 'SQLite quick_check ok');
        } catch (Throwable $e) {
            return self::result($file, false, 'SQLite quick_check failed: ' . $e->getMessage());
        } finally {
            if ($temporaryPath !== null) {
                @unlink($temporaryPath);
            }
        }
    }

    public static function formatBytes(int $bytes): string
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

        return $bytes . 'B';
    }

    /**
     * @return object{ok: bool, path: string|null, message: string}
     */
    private static function inflateGzip(string $file, ?string $workDir): object
    {
        if (! function_exists('gzopen')) {
            return (object) ['ok' => false, 'path' => null, 'message' => 'PHP zlib is unavailable'];
        }

        $workDir = $workDir ?: storage_path('framework/cache/grimba-backup-verify');
        if (! is_dir($workDir) && ! @mkdir($workDir, 0775, true) && ! is_dir($workDir)) {
            return (object) ['ok' => false, 'path' => null, 'message' => 'could not create backup verification work directory'];
        }

        $temporaryPath = @tempnam($workDir, 'grimba-backup-');
        if (! is_string($temporaryPath)) {
            return (object) ['ok' => false, 'path' => null, 'message' => 'could not allocate temporary restore file'];
        }

        $input = @gzopen($file, 'rb');
        $output = @fopen($temporaryPath, 'wb');
        if (! $input || ! $output) {
            if ($input) {
                @gzclose($input);
            }
            if ($output) {
                @fclose($output);
            }
            @unlink($temporaryPath);

            return (object) ['ok' => false, 'path' => null, 'message' => 'could not open backup artifact for restore smoke'];
        }

        while (! @gzeof($input)) {
            $chunk = @gzread($input, 1048576);
            if (! is_string($chunk)) {
                @gzclose($input);
                @fclose($output);
                @unlink($temporaryPath);

                return (object) ['ok' => false, 'path' => null, 'message' => 'could not read compressed backup artifact'];
            }

            if (@fwrite($output, $chunk) === false) {
                @gzclose($input);
                @fclose($output);
                @unlink($temporaryPath);

                return (object) ['ok' => false, 'path' => null, 'message' => 'could not write temporary restore file'];
            }
        }

        @gzclose($input);
        @fclose($output);

        return (object) ['ok' => true, 'path' => $temporaryPath, 'message' => 'ok'];
    }

    private static function result(string $file, bool $ok, string $message): object
    {
        return (object) [
            'ok' => $ok,
            'file' => $file,
            'message' => $message,
        ];
    }
}
