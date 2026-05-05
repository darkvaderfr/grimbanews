<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class SourceLogoProxyTest extends TestCase
{
    public function test_img_proxy_records_source_logo_success_and_miss(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $host = 'logo-' . Str::random(10) . '.test';
        $sourceId = DB::table('news_sources')->insertGetId([
            'name' => 'Logo Proxy Fixture ' . Str::random(8),
            'slug' => 'logo-proxy-fixture-' . Str::random(8),
            'website' => $host,
            'bias_rating' => 'center',
            'logo_status' => 'unknown',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $remote = "https://logo.clearbit.com/{$host}?size=34";
        Http::fake([
            'logo.clearbit.com/*' => Http::response('fake-png', 200, ['Content-Type' => 'image/png']),
        ]);

        $this->get('/img-proxy?' . http_build_query([
            'u' => $remote,
            'sid' => $sourceId,
            'provider' => 'clearbit',
        ]))->assertOk();

        $this->assertDatabaseHas('news_sources', [
            'id' => $sourceId,
            'logo_status' => 'clearbit',
            'logo_url' => $remote,
        ]);

        $missingSourceId = DB::table('news_sources')->insertGetId([
            'name' => 'Logo Missing Fixture ' . Str::random(8),
            'slug' => 'logo-missing-fixture-' . Str::random(8),
            'website' => 'missing-' . Str::random(10) . '.test',
            'bias_rating' => 'center',
            'logo_status' => 'unknown',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'www.google.com/*' => Http::response('', 404),
        ]);

        $this->get('/img-proxy?' . http_build_query([
            'u' => 'https://www.google.com/s2/favicons?domain=missing-logo.test&sz=64',
            'sid' => $missingSourceId,
            'provider' => 'favicon',
        ]))->assertNotFound();

        $this->assertDatabaseHas('news_sources', [
            'id' => $missingSourceId,
            'logo_status' => 'missing',
        ]);
    }

    public function test_img_proxy_caches_allowed_article_hero_images(): void
    {
        $remote = 'https://i.f1g.fr/media/cms/' . Str::uuid() . '.jpg';
        Http::fake([
            'i.f1g.fr/*' => Http::response('fake-hero-jpeg', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $query = http_build_query([
            'u' => $remote,
            'provider' => 'article-hero',
            'pid' => 123,
        ]);

        $this->get('/img-proxy?' . $query)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('Cache-Control', 'max-age=2592000, public, s-maxage=2592000')
            ->assertContent('fake-hero-jpeg');

        $this->get('/img-proxy?' . $query)
            ->assertOk()
            ->assertContent('fake-hero-jpeg');

        Http::assertSentCount(1);
    }

    public function test_img_proxy_caches_article_hero_placeholder_when_publisher_fails(): void
    {
        $remote = 'https://www.lexpress.fr/assets/' . Str::uuid() . '.jpg';
        Http::fake([
            'www.lexpress.fr/*' => Http::response('', 403),
        ]);

        $query = http_build_query([
            'u' => $remote,
            'provider' => 'article-hero',
            'pid' => 0,
        ]);

        $this->get('/img-proxy?' . $query)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml; charset=UTF-8')
            ->assertSee('<svg', false);

        $this->get('/img-proxy?' . $query)
            ->assertOk()
            ->assertSee('<svg', false);

        Http::assertSentCount(1);
    }

    public function test_img_proxy_rejects_unallowlisted_article_hero_hosts(): void
    {
        Http::fake();

        $this->get('/img-proxy?' . http_build_query([
            'u' => 'https://example.com/not-allowed.jpg',
            'provider' => 'article-hero',
            'pid' => 123,
        ]))->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_post_hero_partial_rewrites_external_images_through_proxy(): void
    {
        $post = (object) [
            'id' => 456,
            'name' => 'External publisher image',
            'image' => 'https://i.f1g.fr/media/cms/example.jpg',
            'original_language' => 'fr',
        ];

        $html = html_entity_decode(view()->file(
            base_path('platform/themes/echo/partials/post-hero-img.blade.php'),
            ['post' => $post, 'size' => 'large']
        )->render());

        $this->assertStringContainsString('/img-proxy?', $html);
        $this->assertStringContainsString('provider=article-hero', $html);
        $this->assertStringContainsString('pid=456', $html);
        $this->assertStringContainsString('u=https%3A%2F%2Fi.f1g.fr%2Fmedia%2Fcms%2Fexample.jpg', $html);
        $this->assertStringNotContainsString('src="https://i.f1g.fr/media/cms/example.jpg"', $html);
    }
}
