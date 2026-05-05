<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SourceCountryBackfillCommandTest extends TestCase
{
    public function test_source_country_backfill_defaults_to_dry_run(): void
    {
        $sourceId = $this->source('K8 Dry Run Source', 'k8-dry-run.fr');
        $this->activeFeed($sourceId);

        $this->artisan('grimba:backfill-source-countries')
            ->expectsOutputToContain('Dry-run found')
            ->expectsOutputToContain('Dry-run only')
            ->assertExitCode(0);

        $this->assertNull(DB::table('news_sources')->where('id', $sourceId)->value('country'));
    }

    public function test_source_country_backfill_applies_high_confidence_missing_countries(): void
    {
        $frSource = $this->source('K8 France Source', 'k8-france.fr');
        $unknownSource = $this->source('K8 Unknown Source', 'example.com');

        $this->activeFeed($frSource);
        $this->activeFeed($unknownSource);

        $this->artisan('grimba:backfill-source-countries', ['--apply' => true])
            ->expectsOutputToContain('Applied')
            ->assertExitCode(0);

        $this->assertSame('FR', DB::table('news_sources')->where('id', $frSource)->value('country'));
        $this->assertNull(DB::table('news_sources')->where('id', $unknownSource)->value('country'));
    }

    public function test_source_country_backfill_uses_feed_url_when_website_is_missing(): void
    {
        $sourceId = $this->source('K8 Feed Evidence Source', null);
        $this->activeFeed($sourceId, 'https://www.telerama.fr/rss.xml');

        $this->artisan('grimba:backfill-source-countries', ['--apply' => true])
            ->expectsOutputToContain('Applied')
            ->assertExitCode(0);

        $this->assertSame('FR', DB::table('news_sources')->where('id', $sourceId)->value('country'));
    }

    private function source(string $name, ?string $website): int
    {
        return (int) DB::table('news_sources')->insertGetId([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'website' => $website,
            'bias_rating' => 'center',
            'ownership_type' => 'corporate',
            'credibility_score' => 70,
            'country' => null,
            'language' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function activeFeed(int $sourceId, ?string $url = null): void
    {
        DB::table('rss_feeds')->insert([
            'source_id' => $sourceId,
            'url' => $url ?: "https://example.test/k8-{$sourceId}.xml",
            'feed_format' => 'rss',
            'is_active' => true,
            'consecutive_failures' => 0,
            'items_ingested' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
