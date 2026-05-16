<?php

namespace Tests\Unit;

use App\Support\GrimbaProviderCredits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GrimbaProviderCreditsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        GrimbaProviderCredits::reset('newsdata-io');
    }

    public function test_fresh_install_reports_zero_used(): void
    {
        $this->assertSame(0, GrimbaProviderCredits::used('newsdata-io'));
        $this->assertSame(0, GrimbaProviderCredits::cached('newsdata-io'));
        $this->assertSame(0, GrimbaProviderCredits::fast('newsdata-io'));
    }

    public function test_bump_increments_cached_counter(): void
    {
        GrimbaProviderCredits::bump('newsdata-io');
        GrimbaProviderCredits::bump('newsdata-io');
        GrimbaProviderCredits::bump('newsdata-io');

        $this->assertSame(3, GrimbaProviderCredits::cached('newsdata-io'));
        $this->assertSame(3, GrimbaProviderCredits::fast('newsdata-io'));
    }

    public function test_fast_uses_max_of_db_and_cache(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('grimba_live_news_provider_runs')) {
            $this->markTestSkipped('grimba_live_news_provider_runs table not yet migrated.');
        }

        // Cache lags by 2 — DB still authoritative.
        DB::table('grimba_live_news_provider_runs')->insert([
            'provider'   => 'newsdata-io',
            'status'     => 'ok',
            'started_at' => now()->utc(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('grimba_live_news_provider_runs')->insert([
            'provider'   => 'newsdata-io',
            'status'     => 'ingested',
            'started_at' => now()->utc(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(2, GrimbaProviderCredits::used('newsdata-io'));
        $this->assertSame(2, GrimbaProviderCredits::fast('newsdata-io'));

        GrimbaProviderCredits::bump('newsdata-io');
        // Cache=1, DB=2 → fast = max
        $this->assertSame(2, GrimbaProviderCredits::fast('newsdata-io'));
    }

    public function test_skipped_runs_are_excluded(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('grimba_live_news_provider_runs')) {
            $this->markTestSkipped('grimba_live_news_provider_runs table not yet migrated.');
        }

        DB::table('grimba_live_news_provider_runs')->insert([
            'provider'   => 'newsdata-io',
            'status'     => 'skipped',
            'started_at' => now()->utc(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(0, GrimbaProviderCredits::used('newsdata-io'));
    }

    public function test_reset_wipes_cached_counter(): void
    {
        GrimbaProviderCredits::bump('newsdata-io');
        $this->assertSame(1, GrimbaProviderCredits::cached('newsdata-io'));

        GrimbaProviderCredits::reset('newsdata-io');
        $this->assertSame(0, GrimbaProviderCredits::cached('newsdata-io'));
    }
}
