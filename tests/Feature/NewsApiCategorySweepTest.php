<?php

namespace Tests\Feature;

use App\Services\GrimbaNewsApiFetcher;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class NewsApiCategorySweepTest extends TestCase
{
    public function test_newsapi_fetcher_sweeps_configured_categories_per_country(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('grimba_newsapi_runs')->delete();

        $this->setting('grimba_newsapi_key', 'test-newsapi-key-' . Str::random(8));
        $this->setting('grimba_newsapi_countries', 'fr');
        $this->setting('grimba_newsapi_categories', 'business,science');
        $this->setting('grimba_newsapi_queries', '');
        $this->setting('grimba_newsapi_daily_request_budget', '900');
        $this->setting('grimba_newsapi_max_calls_per_run', '40');

        Http::fake([
            'newsapi.org/v2/top-headlines*' => Http::response([
                'status' => 'ok',
                'totalResults' => 0,
                'articles' => [],
            ], 200),
        ]);

        $summary = app(GrimbaNewsApiFetcher::class)->fetchAll();

        $this->assertCount(2, $summary);
        $this->assertSame('country=fr category=business', $summary[0]['query']);
        $this->assertSame('country=fr category=science', $summary[1]['query']);

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'category=business'));
        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'category=science'));

        $this->assertDatabaseHas('grimba_newsapi_runs', [
            'endpoint' => 'top-headlines',
            'country' => 'fr',
            'category' => 'business',
            'status' => 'ok',
            'returned_articles' => 0,
        ]);
        $this->assertDatabaseHas('grimba_newsapi_runs', [
            'endpoint' => 'top-headlines',
            'country' => 'fr',
            'category' => 'science',
            'status' => 'ok',
            'returned_articles' => 0,
        ]);
        $this->assertSame(2, DB::table('grimba_newsapi_runs')->count());
    }

    public function test_newsapi_fetcher_honors_per_run_call_guardrail(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('grimba_newsapi_runs')->delete();

        $this->setting('grimba_newsapi_key', 'test-newsapi-key-' . Str::random(8));
        $this->setting('grimba_newsapi_countries', 'fr,us');
        $this->setting('grimba_newsapi_categories', 'business,science');
        $this->setting('grimba_newsapi_queries', '');
        $this->setting('grimba_newsapi_daily_request_budget', '900');
        $this->setting('grimba_newsapi_max_calls_per_run', '1');

        Http::fake([
            'newsapi.org/v2/top-headlines*' => Http::response([
                'status' => 'ok',
                'totalResults' => 0,
                'articles' => [],
            ], 200),
        ]);

        $summary = app(GrimbaNewsApiFetcher::class)->fetchAll();

        $this->assertCount(2, $summary);
        $this->assertSame('ok', $summary[0]['status']);
        $this->assertSame('skipped', $summary[1]['status']);
        $this->assertSame('NewsAPI request guardrail reached.', $summary[1]['error']);
        Http::assertSentCount(1);
    }

    private function setting(string $key, string $value): void
    {
        $store = app(SettingStore::class);
        $store->set($key, $value);
        $store->save();
    }
}
