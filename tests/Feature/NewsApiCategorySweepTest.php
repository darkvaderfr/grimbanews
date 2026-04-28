<?php

namespace Tests\Feature;

use App\Services\GrimbaNewsApiFetcher;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class NewsApiCategorySweepTest extends TestCase
{
    public function test_newsapi_fetcher_sweeps_configured_categories_per_country(): void
    {
        $this->setting('grimba_newsapi_key', 'test-newsapi-key-' . Str::random(8));
        $this->setting('grimba_newsapi_countries', 'fr');
        $this->setting('grimba_newsapi_categories', 'business,science');
        $this->setting('grimba_newsapi_queries', '');

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
    }

    private function setting(string $key, string $value): void
    {
        $store = app(SettingStore::class);
        $store->set($key, $value);
        $store->save();
    }
}
