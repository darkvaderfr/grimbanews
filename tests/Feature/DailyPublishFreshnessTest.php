<?php

namespace Tests\Feature;

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

        foreach (GrimbaAutomationMonitor::freshnessJobKeys() as $jobKey) {
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
}
