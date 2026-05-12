<?php

namespace Tests\Feature;

use App\Services\GrimbaArticleExtractor;
use App\Support\GrimbaAutomationMonitor;
use App\Support\GrimbaIngestGuardrails;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DailyPublishFreshnessTest extends TestCase
{
    public function test_trusted_auto_publish_stamps_publication_time(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $suffix = Str::lower(Str::random(8));
        $author = $this->admin();
        $sourceId = $this->trustedSourceId('Trusted Publish ' . $suffix, 101);
        $createdAt = now()->subHours(2);
        $postId = $this->draftPostId('trusted publish fixture ' . $suffix, $sourceId, $author, $createdAt);
        $before = now()->subSecond();

        $this->artisan('grimba:publish-trusted', [
            '--threshold' => 101,
            '--age-hours' => 1,
            '--limit' => 1,
        ])->assertSuccessful();

        $post = DB::table('posts')->where('id', $postId)->first(['status', 'created_at', 'published_at']);

        $this->assertSame('published', $post->status);
        $this->assertSame($createdAt->toDateTimeString(), (string) $post->created_at);
        $this->assertNotNull($post->published_at);
        $this->assertGreaterThanOrEqual($before->toDateTimeString(), (string) $post->published_at);
    }

    public function test_freshness_watchdog_promotes_recent_trusted_drafts_when_daily_floor_is_empty(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        DB::table('posts')
            ->where('status', 'published')
            ->update(['published_at' => now()->subDays(7)]);

        $suffix = Str::lower(Str::random(8));
        $author = $this->admin();
        $sourceId = $this->trustedSourceId('Freshness Watchdog ' . $suffix, 101);

        $oldestId = $this->draftPostId('freshness oldest fixture ' . $suffix, $sourceId, $author, now()->subHours(5));
        $middleId = $this->draftPostId('freshness middle fixture ' . $suffix, $sourceId, $author, now()->subHours(3));
        $newestId = $this->draftPostId('freshness newest fixture ' . $suffix, $sourceId, $author, now()->subHours(2));

        $this->artisan('grimba:ensure-daily-publish', [
            '--min' => 2,
            '--window-hours' => 24,
            '--lookback-hours' => 24,
            '--threshold' => 101,
            '--min-age-minutes' => 0,
        ])->assertSuccessful();

        $this->assertSame('draft', DB::table('posts')->where('id', $oldestId)->value('status'));
        $this->assertSame('published', DB::table('posts')->where('id', $middleId)->value('status'));
        $this->assertSame('published', DB::table('posts')->where('id', $newestId)->value('status'));
        $this->assertNotNull(DB::table('posts')->where('id', $middleId)->value('published_at'));
        $this->assertNotNull(DB::table('posts')->where('id', $newestId)->value('published_at'));
    }

    public function test_manual_ingest_guardrail_publish_stamps_publication_time(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $suffix = Str::lower(Str::random(8));
        $author = $this->admin();
        $sourceId = $this->trustedSourceId('Manual Guardrail Publish ' . $suffix, 101);
        $postId = $this->draftPostId('manual guardrail publish fixture ' . $suffix, $sourceId, $author, now()->subHours(2));

        DB::table('posts')->where('id', $postId)->update([
            'description' => str_repeat('Ready manual publication fixture. ', 4),
        ]);

        $result = GrimbaIngestGuardrails::publishDrafts([$postId]);

        $this->assertSame(1, $result['published']);
        $this->assertSame('published', DB::table('posts')->where('id', $postId)->value('status'));
        $this->assertNotNull(DB::table('posts')->where('id', $postId)->value('published_at'));
    }

    public function test_ops_health_guard_fails_when_publication_floor_is_breached(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        DB::table('posts')
            ->where('status', 'published')
            ->update(['published_at' => now()->subDays(7)]);

        $this->artisan('grimba:health', [
            '--fail-on-risk' => true,
            '--min-free-mb' => 0,
            '--min-published-24h' => 999999,
        ])
            ->expectsOutputToContain('publication freshness below floor')
            ->assertFailed();
    }

    public function test_ops_health_guard_fails_when_manual_publications_mask_empty_ingest_publication_pipeline(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        DB::table('posts')
            ->where('status', 'published')
            ->update(['published_at' => now()->subDays(7)]);

        $suffix = Str::lower(Str::random(8));
        $author = $this->admin();
        $sourceId = $this->trustedSourceId('Manual Freshness Mask ' . $suffix, 101);

        $firstId = $this->draftPostId('manual freshness mask one ' . $suffix, $sourceId, $author, now()->subHour());
        $secondId = $this->draftPostId('manual freshness mask two ' . $suffix, $sourceId, $author, now()->subHour());

        DB::table('posts')
            ->whereIn('id', [$firstId, $secondId])
            ->update([
                'status' => 'published',
                'published_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('rss_feed_items')->insert([
            'feed_id' => 1,
            'guid' => 'manual-freshness-mask-' . $suffix,
            'link' => 'https://example.test/manual-freshness-mask-' . $suffix,
            'title_snapshot' => 'Manual freshness mask fixture',
            'post_id' => null,
            'seen_at' => now(),
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->markFreshnessAutomationHealthy();

        $this->artisan('grimba:health', [
            '--fail-on-risk' => true,
            '--min-free-mb' => 0,
            '--min-published-24h' => 2,
        ])
            ->expectsOutputToContain('published 24h        : 2 post(s) (floor 2)')
            ->expectsOutputToContain('ingest-to-public freshness below floor: 0/2 RSS/NewsAPI-backed posts in the last 24h')
            ->assertFailed();
    }

    public function test_ops_health_guard_accepts_rss_backed_publications_for_daily_floor(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        DB::table('posts')
            ->where('status', 'published')
            ->update(['published_at' => now()->subDays(7)]);

        $suffix = Str::lower(Str::random(8));
        $author = $this->admin();
        $sourceId = $this->trustedSourceId('RSS Freshness Pipeline ' . $suffix, 101);

        $firstId = $this->draftPostId('rss freshness pipeline one ' . $suffix, $sourceId, $author, now()->subHour());
        $secondId = $this->draftPostId('rss freshness pipeline two ' . $suffix, $sourceId, $author, now()->subHour());

        DB::table('posts')
            ->whereIn('id', [$firstId, $secondId])
            ->update([
                'status' => 'published',
                'published_at' => now(),
                'updated_at' => now(),
            ]);

        foreach ([$firstId, $secondId] as $postId) {
            DB::table('rss_feed_items')->insert([
                'feed_id' => 1,
                'guid' => 'rss-freshness-pipeline-' . $suffix . '-' . $postId,
                'link' => 'https://example.test/rss-freshness-pipeline-' . $suffix . '-' . $postId,
                'title_snapshot' => 'RSS freshness pipeline fixture',
                'post_id' => $postId,
                'seen_at' => now(),
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->markFreshnessAutomationHealthy();

        $this->artisan('grimba:health', [
            '--fail-on-risk' => true,
            '--min-free-mb' => 0,
            '--min-published-24h' => 2,
        ])
            ->expectsOutputToContain('ingest-published 24h : 2 post(s) (RSS 2 / NewsAPI 0 / manual 0, floor 2)')
            ->assertSuccessful();
    }

    public function test_ops_health_guard_fails_when_daily_freshness_scheduler_is_stale(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $suffix = Str::lower(Str::random(8));
        $author = $this->admin();
        $sourceId = $this->trustedSourceId('Freshness Schedule ' . $suffix, 101);
        $postId = $this->draftPostId('freshness schedule fixture ' . $suffix, $sourceId, $author, now()->subHour());

        DB::table('posts')->where('id', $postId)->update([
            'status' => 'published',
            'published_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('rss_feed_items')->insert([
            'feed_id' => 1,
            'guid' => 'freshness-schedule-' . $suffix,
            'link' => 'https://example.test/freshness-schedule-' . $suffix,
            'title_snapshot' => 'Freshness schedule fixture',
            'post_id' => $postId,
            'seen_at' => now(),
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->markFreshnessAutomationHealthy();

        DB::table('grimba_automation_runs')
            ->where('job_key', 'freshness_watchdog')
            ->update([
                'started_at' => now()->subHours(2),
                'finished_at' => now()->subHours(2),
                'updated_at' => now(),
            ]);

        $this->artisan('grimba:health', [
            '--fail-on-risk' => true,
            '--min-free-mb' => 0,
            '--min-published-24h' => 1,
        ])
            ->expectsOutputToContain('automation job unhealthy: Freshness watchdog')
            ->assertFailed();
    }

    public function test_full_article_extractor_prioritizes_never_attempted_posts_over_recent_failures(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        DB::table('posts')->where('status', 'published')->update([
            'full_content' => '<p>' . str_repeat('Already readable seed content. ', 12) . '</p>',
            'full_fetched_at' => now(),
            'full_extract_error' => null,
            'updated_at' => now(),
        ]);

        $suffix = Str::lower(Str::random(8));
        $author = $this->admin();
        $sourceId = $this->trustedSourceId('Full Article Queue ' . $suffix, 101);

        $neverUrl = 'https://example.test/full-article-never-' . $suffix;
        $failedUrl = 'https://example.test/full-article-failed-' . $suffix;
        $neverId = $this->publishedPostId('full article never attempted ' . $suffix, $sourceId, $author, now()->subMinutes(10));
        $failedId = $this->publishedPostId('full article recent failure ' . $suffix, $sourceId, $author, now());

        DB::table('posts')->where('id', $failedId)->update([
            'full_fetched_at' => now()->subHour(),
            'full_extract_error' => 'blocked recently',
            'updated_at' => now(),
        ]);

        $this->rssItem($neverId, $neverUrl, 'full-article-never-' . $suffix);
        $this->rssItem($failedId, $failedUrl, 'full-article-failed-' . $suffix);

        $this->mock(GrimbaArticleExtractor::class, function ($mock) use ($neverUrl): void {
            $mock->shouldReceive('extractFromUrl')
                ->once()
                ->with($neverUrl)
                ->andReturn([
                    'ok' => true,
                    'html' => '<p>' . str_repeat('Fresh extracted body. ', 14) . '</p>',
                    'error' => null,
                ]);
        });

        $this->artisan('grimba:fetch-full-articles', [
            '--limit' => 1,
            '--retry-after-hours' => 24,
        ])->assertSuccessful();

        $this->assertNotNull(DB::table('posts')->where('id', $neverId)->value('full_content'));
        $this->assertNull(DB::table('posts')->where('id', $neverId)->value('full_extract_error'));
        $this->assertNull(DB::table('posts')->where('id', $failedId)->value('full_content'));
        $this->assertSame('blocked recently', DB::table('posts')->where('id', $failedId)->value('full_extract_error'));
    }

    public function test_ops_health_guard_can_fail_when_full_article_coverage_floor_is_breached(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        DB::table('posts')
            ->where('status', 'published')
            ->update(['published_at' => now()->subDays(7)]);

        $suffix = Str::lower(Str::random(8));
        $author = $this->admin();
        $sourceId = $this->trustedSourceId('Full Coverage Health ' . $suffix, 101);

        $readableId = $this->publishedPostId('full coverage readable ' . $suffix, $sourceId, $author, now()->subMinutes(12), [
            'full_content' => '<p>' . str_repeat('Readable in-app article body. ', 12) . '</p>',
            'full_fetched_at' => now()->subMinutes(10),
        ]);
        $missingId = $this->publishedPostId('full coverage missing ' . $suffix, $sourceId, $author, now()->subMinutes(8), [
            'full_content' => null,
            'full_fetched_at' => null,
        ]);

        $this->rssItem($readableId, 'https://example.test/full-coverage-readable-' . $suffix, 'full-coverage-readable-' . $suffix);
        $this->rssItem($missingId, 'https://example.test/full-coverage-missing-' . $suffix, 'full-coverage-missing-' . $suffix);
        $this->markHealthAutomationHealthy();

        $this->artisan('grimba:health', [
            '--fail-on-risk' => true,
            '--min-free-mb' => 0,
            '--min-published-24h' => 2,
            '--min-full-content-coverage' => 75,
        ])
            ->expectsOutputToContain('readable bodies       : 1/2 (50%, floor 75%)')
            ->expectsOutputToContain('full-article coverage below floor: 50%/75%')
            ->assertFailed();
    }

    public function test_ops_health_counts_readable_feed_body_when_extraction_is_blocked(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        DB::table('posts')
            ->where('status', 'published')
            ->update(['published_at' => now()->subDays(7)]);

        $suffix = Str::lower(Str::random(8));
        $author = $this->admin();
        $sourceId = $this->trustedSourceId('Full Coverage Fallback ' . $suffix, 101);

        $fullId = $this->publishedPostId('full coverage extracted ' . $suffix, $sourceId, $author, now()->subMinutes(12), [
            'full_content' => '<p>' . str_repeat('Readable extracted article body. ', 12) . '</p>',
            'full_fetched_at' => now()->subMinutes(10),
        ]);
        $fallbackId = $this->publishedPostId('full coverage feed fallback ' . $suffix, $sourceId, $author, now()->subMinutes(8), [
            'content' => '<p><a href="https://example.test/feed-fallback-' . $suffix . '">Lire l’article original</a></p><p>' . str_repeat('Readable feed body fallback text. ', 12) . '</p>',
            'full_content' => null,
            'full_fetched_at' => now()->subMinutes(5),
            'full_extract_error' => 'http 403',
        ]);

        $this->rssItem($fullId, 'https://example.test/full-coverage-extracted-' . $suffix, 'full-coverage-extracted-' . $suffix);
        $this->rssItem($fallbackId, 'https://example.test/feed-fallback-' . $suffix, 'full-coverage-feed-fallback-' . $suffix);
        $this->markHealthAutomationHealthy();

        $this->artisan('grimba:health', [
            '--fail-on-risk' => true,
            '--min-free-mb' => 0,
            '--min-published-24h' => 2,
            '--min-full-content-coverage' => 100,
        ])
            ->expectsOutputToContain('readable bodies       : 2/2 (100%, floor 100%)')
            ->expectsOutputToContain('feed body fallback    : 1 readable post(s)')
            ->assertSuccessful();
    }

    private function markFreshnessAutomationHealthy(): void
    {
        $this->markHealthAutomationHealthy();
    }

    private function markHealthAutomationHealthy(): void
    {
        DB::table('grimba_automation_runs')->delete();

        foreach (GrimbaAutomationMonitor::healthJobKeys() as $jobKey) {
            $job = GrimbaAutomationMonitor::jobs()[$jobKey];
            DB::table('grimba_automation_runs')->insert([
                'job_key' => $jobKey,
                'command' => $job['command'],
                'status' => 'success',
                'exit_code' => 0,
                'started_at' => now()->subMinutes(5),
                'finished_at' => now()->subMinutes(4),
                'duration_ms' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function admin(): User
    {
        $author = User::query()->find(1);

        $this->assertNotNull($author, 'Fixture database must contain the system admin user.');

        return $author;
    }

    private function trustedSourceId(string $name, int $credibility): int
    {
        return (int) DB::table('news_sources')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => Str::slug($name) . '.test',
            'bias_rating' => 'center',
            'credibility_score' => $credibility,
            'country' => 'FR',
            'language' => 'fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function draftPostId(string $name, int $sourceId, User $author, mixed $createdAt): int
    {
        return (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'Daily publish freshness fixture.',
            'content' => '<p>Daily publish freshness fixture.</p>',
            'status' => 'draft',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'source_id' => $sourceId,
            'source_name' => DB::table('news_sources')->where('id', $sourceId)->value('name'),
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function publishedPostId(string $name, int $sourceId, User $author, mixed $publishedAt, array $overrides = []): int
    {
        return (int) DB::table('posts')->insertGetId(array_merge([
            'name' => $name,
            'description' => 'Published full article coverage fixture.',
            'content' => '<p>Published full article coverage fixture.</p>',
            'full_content' => null,
            'full_fetched_at' => null,
            'full_extract_error' => null,
            'status' => 'published',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'source_id' => $sourceId,
            'source_name' => DB::table('news_sources')->where('id', $sourceId)->value('name'),
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'published_at' => $publishedAt,
            'created_at' => $publishedAt,
            'updated_at' => $publishedAt,
        ], $overrides));
    }

    private function rssItem(int $postId, string $url, string $guid): void
    {
        DB::table('rss_feed_items')->insert([
            'feed_id' => 1,
            'guid' => $guid,
            'link' => $url,
            'title_snapshot' => 'Full article coverage fixture',
            'post_id' => $postId,
            'seen_at' => now(),
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
