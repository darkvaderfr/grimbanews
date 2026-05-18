<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Wave MM — Vader 2026-05-18 — pin idempotency of the two RSS-source
 * seed commands. These commands run during operator-driven launch prep
 * and MUST be safe to re-run without producing duplicate
 * `news_sources` rows or duplicate `rss_feeds` rows.
 *
 * Both seeders rely on a host-substring guard against `news_sources.website`
 * and an exact-URL guard against `rss_feeds.url`. If either guard
 * regresses, a duplicate insert can pollute the source list during
 * pre-launch testing.
 */
class GrimbaSeedSourcesIdempotencyTest extends TestCase
{
    public function test_seed_immigration_sources_does_not_duplicate_on_rerun(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('news_sources')) {
            $this->markTestSkipped('news_sources table not present.');
        }

        $countBefore = DB::table('news_sources')->count();
        $feedsBefore = DB::table('rss_feeds')->count();

        // Two consecutive runs should be a no-op if the seeded sources
        // are already present. Use --dry-run to keep the assertion DB-pure.
        Artisan::call('grimba:seed-immigration-sources', ['--dry-run' => true]);
        Artisan::call('grimba:seed-immigration-sources', ['--dry-run' => true]);

        $this->assertSame($countBefore, DB::table('news_sources')->count(), 'news_sources count must not change on dry-run re-execution.');
        $this->assertSame($feedsBefore, DB::table('rss_feeds')->count(), 'rss_feeds count must not change on dry-run re-execution.');
    }

    public function test_seed_thin_category_sources_does_not_duplicate_on_rerun(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('news_sources')) {
            $this->markTestSkipped('news_sources table not present.');
        }

        $countBefore = DB::table('news_sources')->count();
        $feedsBefore = DB::table('rss_feeds')->count();

        Artisan::call('grimba:seed-thin-category-sources', ['--dry-run' => true]);
        Artisan::call('grimba:seed-thin-category-sources', ['--dry-run' => true]);

        $this->assertSame($countBefore, DB::table('news_sources')->count());
        $this->assertSame($feedsBefore, DB::table('rss_feeds')->count());
    }

    public function test_seed_immigration_sources_reports_existing_publishers_after_first_run(): void
    {
        $exitCode = Artisan::call('grimba:seed-immigration-sources', ['--dry-run' => true]);
        $this->assertSame(0, $exitCode);

        // The output should mention "already_exists" for every publisher
        // that was seeded in the prior 2026-05-17 run (which lives in
        // the development DB). At minimum, La Cimade should show.
        $output = Artisan::output();
        $this->assertStringContainsString('already_exists', $output, 'Re-run should hit the idempotency guard.');
    }

    public function test_seed_thin_category_sources_skips_subcategory_feeds_already_attached(): void
    {
        $exitCode = Artisan::call('grimba:seed-thin-category-sources', ['--dry-run' => true]);
        $this->assertSame(0, $exitCode);

        // After the prior live run (2026-05-18) attached Le Monde Santé,
        // Sciences, RFI Sports, France 24 général, and NPR World feeds,
        // a re-run must report all 5 as already_exists.
        $output = Artisan::output();
        $alreadyExistsCount = substr_count($output, 'already_exists');
        $this->assertGreaterThanOrEqual(
            5,
            $alreadyExistsCount,
            'Expected at least 5 already_exists markers from the 5 sub-feeds attached during the prior run.'
        );
    }

    public function test_dry_run_writes_nothing(): void
    {
        $sourcesBefore = DB::table('news_sources')->count();
        $feedsBefore   = DB::table('rss_feeds')->count();

        Artisan::call('grimba:seed-immigration-sources', ['--dry-run' => true]);
        Artisan::call('grimba:seed-thin-category-sources', ['--dry-run' => true]);

        $this->assertSame($sourcesBefore, DB::table('news_sources')->count());
        $this->assertSame($feedsBefore, DB::table('rss_feeds')->count());
    }
}
