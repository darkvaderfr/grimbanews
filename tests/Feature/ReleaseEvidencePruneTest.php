<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReleaseEvidencePruneTest extends TestCase
{
    public function test_prune_release_evidence_deletes_old_and_excess_reports(): void
    {
        $evidenceDir = $this->evidenceDir();
        File::ensureDirectoryExists($evidenceDir);

        try {
            $old = $evidenceDir . '/grimbanews-release-old.md';
            $newest = $evidenceDir . '/grimbanews-release-newest.md';
            $secondNewest = $evidenceDir . '/grimbanews-release-second.md';
            $excess = $evidenceDir . '/grimbanews-release-excess.md';
            $nonMarkdown = $evidenceDir . '/keep.txt';

            file_put_contents($old, str_repeat('a', 1024));
            file_put_contents($newest, str_repeat('b', 1024));
            file_put_contents($secondNewest, str_repeat('c', 1024));
            file_put_contents($excess, str_repeat('d', 1024));
            file_put_contents($nonMarkdown, 'not evidence');

            touch($old, now()->subDays(40)->getTimestamp());
            touch($newest, now()->subHour()->getTimestamp());
            touch($secondNewest, now()->subHours(2)->getTimestamp());
            touch($excess, now()->subHours(3)->getTimestamp());
            touch($nonMarkdown, now()->subDays(40)->getTimestamp());

            $this->artisan('grimba:prune-release-evidence', [
                '--evidence-dir' => $evidenceDir,
                '--days' => 30,
                '--keep' => 2,
            ])
                ->expectsOutputToContain('Release evidence: 4 file(s)')
                ->expectsOutputToContain('Deleted 2 evidence file(s)')
                ->expectsOutputToContain('Retained 2 file(s)')
                ->assertSuccessful();

            $this->assertFileDoesNotExist($old);
            $this->assertFileDoesNotExist($excess);
            $this->assertFileExists($newest);
            $this->assertFileExists($secondNewest);
            $this->assertFileExists($nonMarkdown);
        } finally {
            File::deleteDirectory($evidenceDir);
        }
    }

    public function test_prune_release_evidence_dry_run_keeps_reports(): void
    {
        $evidenceDir = $this->evidenceDir();
        File::ensureDirectoryExists($evidenceDir);

        try {
            $old = $evidenceDir . '/grimbanews-release-old.md';
            file_put_contents($old, str_repeat('a', 1024));
            touch($old, now()->subDays(40)->getTimestamp());

            $this->artisan('grimba:prune-release-evidence', [
                '--evidence-dir' => $evidenceDir,
                '--days' => 30,
                '--dry-run' => true,
            ])
                ->expectsOutputToContain('Would delete 1 evidence file(s)')
                ->expectsOutputToContain('Dry-run only')
                ->assertSuccessful();

            $this->assertFileExists($old);
        } finally {
            File::deleteDirectory($evidenceDir);
        }
    }

    private function evidenceDir(): string
    {
        return storage_path('framework/testing/release-evidence-' . Str::lower(Str::random(8)));
    }
}
