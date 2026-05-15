<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SourceClassifierCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->cleanClassifierFixtures();
    }

    public function test_source_classifier_defaults_to_dry_run(): void
    {
        $sourceId = $this->source('Classifier Dry Fox ' . Str::lower(Str::random(8)), 'https://www.foxnews.com');
        $this->activeFeed($sourceId);

        $this->artisan('grimba:classify-sources')
            ->expectsOutputToContain('Dry-run found')
            ->expectsOutputToContain('Dry-run only')
            ->assertExitCode(0);

        $row = DB::table('news_sources')->where('id', $sourceId)->first([
            'bias_rating',
            'classification_confidence',
        ]);

        $this->assertSame('unknown', $row->bias_rating);
        $this->assertNull($row->classification_confidence);
    }

    public function test_source_classifier_applies_known_source_profile(): void
    {
        $sourceId = $this->source('Classifier Reuters ' . Str::lower(Str::random(8)), 'https://www.reuters.com');
        $this->activeFeed($sourceId);

        $this->artisan('grimba:classify-sources', ['--apply' => true])
            ->expectsOutputToContain('Applied')
            ->assertExitCode(0);

        $row = DB::table('news_sources')->where('id', $sourceId)->first([
            'bias_rating',
            'ownership_type',
            'owner_name',
            'credibility_score',
            'country',
            'language',
            'bias_score',
            'classification_confidence',
            'classification_method',
            'classified_at',
        ]);

        $this->assertSame('center', $row->bias_rating);
        $this->assertSame('corporation', $row->ownership_type);
        $this->assertSame('Thomson Reuters', $row->owner_name);
        $this->assertSame(92, (int) $row->credibility_score);
        $this->assertSame('GB', $row->country);
        $this->assertSame('en', $row->language);
        $this->assertSame(0.0, (float) $row->bias_score);
        $this->assertSame(92, (int) $row->classification_confidence);
        $this->assertSame('source-map-v1', $row->classification_method);
        $this->assertNotNull($row->classified_at);
    }

    public function test_source_classifier_respects_manual_lock_notes(): void
    {
        $sourceId = $this->source(
            'Classifier Locked CNN ' . Str::lower(Str::random(8)),
            'https://www.cnn.com',
            ['notes' => 'source-classifier:manual-lock editor override']
        );
        $this->activeFeed($sourceId);

        $this->artisan('grimba:classify-sources', ['--apply' => true])
            ->expectsOutputToContain('Applied')
            ->assertExitCode(0);

        $row = DB::table('news_sources')->where('id', $sourceId)->first([
            'bias_rating',
            'owner_name',
            'classification_confidence',
        ]);

        $this->assertSame('unknown', $row->bias_rating);
        $this->assertNull($row->owner_name);
        $this->assertNull($row->classification_confidence);
    }

    public function test_source_classifier_syncs_missing_post_metadata(): void
    {
        $sourceId = $this->source('Classifier Sync Reuters ' . Str::lower(Str::random(8)), 'https://www.reuters.com');
        $this->activeFeed($sourceId);
        $postId = $this->postFixture($sourceId);

        $this->artisan('grimba:classify-sources', [
            '--apply' => true,
            '--sync-posts' => true,
        ])
            ->expectsOutputToContain('Synced')
            ->assertExitCode(0);

        $row = DB::table('posts')->where('id', $postId)->first([
            'bias_rating',
            'credibility_score',
            'ownership_type',
        ]);

        $this->assertSame('center', $row->bias_rating);
        $this->assertSame(92, (int) $row->credibility_score);
        $this->assertSame('corporation', $row->ownership_type);
    }

    public function test_country_only_classification_without_language_is_idempotent(): void
    {
        $sourceId = $this->source('000 Classifier Country Only ' . Str::lower(Str::random(8)), 'https://al.com');
        $this->activeFeed($sourceId);

        $this->artisan('grimba:classify-sources', [
            '--apply' => true,
        ])->assertExitCode(0);

        $this->assertSame('US', DB::table('news_sources')->where('id', $sourceId)->value('country'));
        $this->assertNull(DB::table('news_sources')->where('id', $sourceId)->value('language'));

        $fixedUpdatedAt = now()->subDay()->toDateTimeString();
        DB::table('news_sources')->where('id', $sourceId)->update(['updated_at' => $fixedUpdatedAt]);

        $this->artisan('grimba:classify-sources', [
            '--apply' => true,
        ])->assertExitCode(0);

        $this->assertSame($fixedUpdatedAt, (string) DB::table('news_sources')->where('id', $sourceId)->value('updated_at'));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function source(string $name, string $website, array $overrides = []): int
    {
        return (int) DB::table('news_sources')->insertGetId(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => $website,
            'bias_rating' => 'unknown',
            'ownership_type' => 'unknown',
            'owner_name' => null,
            'credibility_score' => null,
            'country' => null,
            'language' => null,
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function cleanClassifierFixtures(): void
    {
        $sourceIds = DB::table('news_sources')
            ->where('name', 'like', '%Classifier%')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if ($sourceIds === []) {
            return;
        }

        DB::table('rss_feeds')->whereIn('source_id', $sourceIds)->delete();
        DB::table('posts')->whereIn('source_id', $sourceIds)->delete();
        DB::table('news_sources')->whereIn('id', $sourceIds)->delete();
    }

    private function activeFeed(int $sourceId): void
    {
        DB::table('rss_feeds')->insert([
            'source_id' => $sourceId,
            'url' => "https://example.test/classifier-{$sourceId}.xml",
            'feed_format' => 'rss',
            'is_active' => true,
            'consecutive_failures' => 0,
            'items_ingested' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function postFixture(int $sourceId): int
    {
        return (int) DB::table('posts')->insertGetId([
            'name' => 'Classifier sync post ' . Str::lower(Str::random(8)),
            'description' => 'Classifier sync fixture.',
            'content' => '<p>Classifier sync fixture.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'source_id' => $sourceId,
            'source_name' => DB::table('news_sources')->where('id', $sourceId)->value('name'),
            'bias_rating' => 'unknown',
            'credibility_score' => null,
            'ownership_type' => 'unknown',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
