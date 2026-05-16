<?php

namespace Tests\Feature;

use App\Services\GrimbaLiveNewsFetcher;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class LiveNewsProviderTest extends TestCase
{
    public function test_webz_provider_ingests_articles_and_records_provider_run(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->resetLiveTables();

        $sourceName = 'Webz Fixture ' . Str::random(8);
        $articleUrl = 'https://fixture-webz.example.com/world/story-' . Str::random(10);

        $this->setting('grimba_webz_key', 'test-webz-key-' . Str::random(8));
        $this->setting('grimba_webz_active', '1');
        $this->setting('grimba_webz_queries', 'topic:"financial and economic news"');
        $this->setting('grimba_webz_daily_request_budget', '30');
        $this->setting('grimba_webz_monthly_request_budget', '900');
        $this->setting('grimba_webz_max_calls_per_run', '1');

        Http::fake([
            'api.webz.io/newsApiLite*' => Http::response([
                'posts' => [[
                    'uuid' => 'webz-fixture-1',
                    'url' => $articleUrl,
                    'title' => 'Webz provider fixture headline',
                    'text' => 'This Webz fixture has enough readable article body for GrimbaNews to persist and classify.',
                    'published' => now()->toIso8601String(),
                    'language' => 'english',
                    'thread' => [
                        'site' => 'fixture-webz.example.com',
                        'site_full' => $sourceName,
                        'country' => 'US',
                        'main_image' => 'https://fixture-webz.example.com/image.jpg',
                    ],
                ]],
            ], 200),
        ]);

        $summary = app(GrimbaLiveNewsFetcher::class)->fetchAll(['webz']);

        $this->assertSame('ok', $summary[0]['status']);
        $this->assertSame(1, $summary[0]['returned']);
        $this->assertSame(1, $summary[0]['ingested']);
        $this->assertSame(0, $summary[0]['deduped']);

        $this->assertDatabaseHas('grimba_live_news_provider_runs', [
            'provider' => 'webz',
            'status' => 'ok',
            'returned_articles' => 1,
            'ingested_articles' => 1,
        ]);
        $this->assertDatabaseHas('grimba_live_news_items', [
            'provider' => 'webz',
            'article_url' => $articleUrl,
            'source_name' => $sourceName,
        ]);
        $this->assertDatabaseHas('posts', [
            'name' => 'Webz provider fixture headline',
            'source_name' => $sourceName,
        ]);
    }

    public function test_webz_provider_dedupes_articles_already_seen_by_newsapi(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->resetLiveTables();

        $articleUrl = 'https://fixture-webz.example.com/world/duplicate-' . Str::random(10);
        DB::table('newsapi_items')->insert([
            'source_id' => null,
            'api_source_id' => null,
            'article_url' => $articleUrl,
            'article_url_hash' => sha1($articleUrl),
            'post_id' => null,
            'published_at' => now(),
            'fetched_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->setting('grimba_webz_key', 'test-webz-key-' . Str::random(8));
        $this->setting('grimba_webz_active', '1');
        $this->setting('grimba_webz_queries', 'duplicate fixture');
        $this->setting('grimba_webz_daily_request_budget', '30');
        $this->setting('grimba_webz_monthly_request_budget', '900');
        $this->setting('grimba_webz_max_calls_per_run', '1');

        Http::fake([
            'api.webz.io/newsApiLite*' => Http::response([
                'posts' => [[
                    'uuid' => 'webz-duplicate-1',
                    'url' => $articleUrl,
                    'title' => 'Duplicate provider fixture headline',
                    'text' => 'Duplicate provider fixture text.',
                    'published' => now()->toIso8601String(),
                    'language' => 'english',
                    'thread' => [
                        'site' => 'fixture-webz.example.com',
                        'site_full' => 'Webz Duplicate Fixture',
                        'country' => 'US',
                    ],
                ]],
            ], 200),
        ]);

        $summary = app(GrimbaLiveNewsFetcher::class)->fetchAll(['webz']);

        $this->assertSame('ok', $summary[0]['status']);
        $this->assertSame(1, $summary[0]['returned']);
        $this->assertSame(0, $summary[0]['ingested']);
        $this->assertSame(1, $summary[0]['deduped']);
        $this->assertDatabaseMissing('grimba_live_news_items', [
            'provider' => 'webz',
            'article_url' => $articleUrl,
        ]);
    }

    private function setting(string $key, string $value): void
    {
        $store = app(SettingStore::class);
        $store->set($key, $value);
        $store->save();
    }

    private function resetLiveTables(): void
    {
        DB::table('grimba_live_news_items')->delete();
        DB::table('grimba_live_news_provider_runs')->delete();
    }
}
