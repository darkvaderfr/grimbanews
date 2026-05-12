<?php

namespace Tests\Feature;

use App\Services\GrimbaNewsApiFetcher;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NewsApiReadinessCommandTest extends TestCase
{
    public function test_newsapi_readiness_fails_when_key_is_missing(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->setting('grimba_newsapi_key', '');
        $this->setting('grimba_newsapi_active', '0');
        $this->bindMissingKeyFetcher();

        $this->artisan('grimba:newsapi-readiness')
            ->expectsOutputToContain('Key            : missing')
            ->expectsOutputToContain('Risk          : NewsAPI key missing')
            ->expectsOutputToContain('NewsAPI readiness failed')
            ->assertFailed();
    }

    public function test_newsapi_readiness_passes_when_configured_active_and_recently_successful(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('grimba_newsapi_runs')->delete();

        $this->setting('grimba_newsapi_key', 'newsapi-readiness-test-key');
        $this->setting('grimba_newsapi_active', '1');
        $this->setting('grimba_newsapi_countries', 'fr,us');
        $this->setting('grimba_newsapi_categories', 'business,science');
        $this->setting('grimba_newsapi_queries', 'climat');
        $this->setting('grimba_newsapi_daily_request_budget', '900');
        $this->setting('grimba_newsapi_max_calls_per_run', '10');

        DB::table('grimba_newsapi_runs')->insert([
            'endpoint' => 'top-headlines',
            'country' => 'fr',
            'category' => 'business',
            'language' => null,
            'query_label' => 'country=fr category=business',
            'status' => 'ok',
            'total_results' => 8,
            'returned_articles' => 8,
            'ingested_articles' => 3,
            'deduped_articles' => 5,
            'skipped_articles' => 0,
            'duration_ms' => 120,
            'started_at' => now()->subMinutes(20),
            'finished_at' => now()->subMinutes(19),
            'created_at' => now()->subMinutes(20),
            'updated_at' => now()->subMinutes(19),
        ]);

        $this->artisan('grimba:newsapi-readiness', ['--recent-hours' => 24])
            ->expectsOutputToContain('State          : active')
            ->expectsOutputToContain('Key            : configured')
            ->expectsOutputToContain('Planned sweep  : 5 call(s)')
            ->expectsOutputToContain('Latest success :')
            ->expectsOutputToContain('NewsAPI readiness passed')
            ->assertSuccessful();
    }

    public function test_newsapi_readiness_can_report_inactive_state_without_failing(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->setting('grimba_newsapi_key', '');
        $this->setting('grimba_newsapi_active', '0');
        $this->bindMissingKeyFetcher();

        $this->artisan('grimba:newsapi-readiness', ['--allow-inactive' => true])
            ->expectsOutputToContain('Attention     : NewsAPI key missing')
            ->expectsOutputToContain('NewsAPI readiness observed with --allow-inactive')
            ->assertSuccessful();
    }

    private function setting(string $key, string $value): void
    {
        $store = app(SettingStore::class);
        $store->set($key, $value);
        $store->save();
    }

    private function bindMissingKeyFetcher(): void
    {
        $this->app->bind(GrimbaNewsApiFetcher::class, fn () => new class extends GrimbaNewsApiFetcher {
            public function __construct()
            {
            }

            public function isConfigured(): bool
            {
                return false;
            }
        });
    }
}
