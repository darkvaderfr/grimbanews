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
}
