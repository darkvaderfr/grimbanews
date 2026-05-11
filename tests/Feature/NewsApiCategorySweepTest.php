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
    public function test_newsapi_command_fails_when_key_is_missing(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $this->app->bind(GrimbaNewsApiFetcher::class, fn () => new class extends GrimbaNewsApiFetcher {
            public function __construct()
            {
            }

            public function isConfigured(): bool
            {
                return false;
            }
        });

        $this->artisan('grimba:fetch-newsapi')
            ->expectsOutputToContain('NewsAPI key not set')
            ->assertFailed();
    }

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

    public function test_newsapi_fetcher_infers_country_for_auto_created_sources(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('grimba_newsapi_runs')->delete();

        $sourceName = 'K8 Auto Country ' . Str::random(8);
        $articleUrl = 'https://www.telerama.fr/k8-' . Str::random(10);

        $this->setting('grimba_newsapi_key', 'test-newsapi-key-' . Str::random(8));
        $this->setting('grimba_newsapi_countries', 'fr');
        $this->setting('grimba_newsapi_categories', 'general');
        $this->setting('grimba_newsapi_queries', '');
        $this->setting('grimba_newsapi_daily_request_budget', '900');
        $this->setting('grimba_newsapi_max_calls_per_run', '40');

        Http::fake([
            'newsapi.org/v2/top-headlines*' => Http::response([
                'status' => 'ok',
                'totalResults' => 1,
                'articles' => [[
                    'source' => ['id' => null, 'name' => $sourceName],
                    'author' => null,
                    'title' => 'K8 country inference headline',
                    'description' => 'A test article for source country inference.',
                    'url' => $articleUrl,
                    'urlToImage' => 'https://www.telerama.fr/image.jpg',
                    'publishedAt' => now()->toIso8601String(),
                    'content' => 'Country inference fixture content.',
                ]],
            ], 200),
        ]);

        $summary = app(GrimbaNewsApiFetcher::class)->fetchAll();

        $this->assertSame('ok', $summary[0]['status']);
        $this->assertSame(1, $summary[0]['ingested']);
        $this->assertSame('FR', DB::table('news_sources')->where('name', $sourceName)->value('country'));
    }

    public function test_newsapi_ingest_scrubs_truncation_marker_from_persisted_article_text(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::table('grimba_newsapi_runs')->delete();

        $sourceName = 'Truncation Fixture ' . Str::random(8);
        $articleUrl = 'https://www.reuters.com/world/truncation-fixture-' . Str::random(10);

        $this->setting('grimba_newsapi_key', 'test-newsapi-key-' . Str::random(8));
        $this->setting('grimba_newsapi_countries', 'us');
        $this->setting('grimba_newsapi_categories', 'general');
        $this->setting('grimba_newsapi_queries', '');
        $this->setting('grimba_newsapi_daily_request_budget', '900');
        $this->setting('grimba_newsapi_max_calls_per_run', '40');

        Http::fake([
            'newsapi.org/v2/top-headlines*' => Http::response([
                'status' => 'ok',
                'totalResults' => 1,
                'articles' => [[
                    'source' => ['id' => null, 'name' => $sourceName],
                    'author' => null,
                    'title' => 'Truncation fixture headline',
                    'description' => 'Leader profile summary from NewsAPI. [+4285 chars]',
                    'url' => $articleUrl,
                    'urlToImage' => 'https://www.reuters.com/image.jpg',
                    'publishedAt' => now()->toIso8601String(),
                    'content' => 'Prominent Jewish American leader and Israel defender Abraham Abe Foxman has died at age 86. The Anti-Defamation League confirmed his death on Sunday, calling… [+4285 chars]',
                ]],
            ], 200),
        ]);

        $summary = app(GrimbaNewsApiFetcher::class)->fetchAll();

        $this->assertSame('ok', $summary[0]['status']);
        $this->assertSame(1, $summary[0]['ingested']);

        $postId = (int) DB::table('newsapi_items')->where('article_url', $articleUrl)->value('post_id');
        $post = DB::table('posts')->where('id', $postId)->first(['description', 'content']);

        $this->assertNotNull($post);
        $this->assertStringContainsString('Leader profile summary from NewsAPI.', $post->description);
        $this->assertStringContainsString('Prominent Jewish American leader and Israel defender', $post->content);
        $this->assertStringNotContainsString('[+4285 chars]', $post->description);
        $this->assertStringNotContainsString('[+4285 chars]', $post->content);
    }

    private function setting(string $key, string $value): void
    {
        $store = app(SettingStore::class);
        $store->set($key, $value);
        $store->save();
    }
}
