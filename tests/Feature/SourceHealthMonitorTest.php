<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SourceHealthMonitorTest extends TestCase
{
    public function test_rss_source_health_monitor_prioritizes_and_marks_stale_feeds(): void
    {
        $this->cleanupFixtures();

        $brokenSourceId = $this->source('J6 Health Broken Source');
        $healthySourceId = $this->source('J6 Health Fresh Source');

        DB::table('rss_feeds')->insert([
            [
                'source_id' => $healthySourceId,
                'url' => 'https://example.test/j6-health-fresh.xml',
                'feed_format' => 'rss',
                'is_active' => true,
                'last_polled_at' => now()->subMinutes(12),
                'last_success_at' => now()->subMinutes(12),
                'last_error' => null,
                'consecutive_failures' => 0,
                'items_ingested' => 11,
                'notes' => 'J6 fresh fixture',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'source_id' => $brokenSourceId,
                'url' => 'https://example.test/j6-health-broken.xml',
                'feed_format' => 'rss',
                'is_active' => true,
                'last_polled_at' => now()->subHour(),
                'last_success_at' => now()->subDays(2),
                'last_error' => 'J6 upstream timeout',
                'consecutive_failures' => 3,
                'items_ingested' => 2,
                'notes' => 'J6 stale fixture',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($this->admin())
            ->get('/admin/grimba/rss-feeds?q=J6%20Health');

        $response
            ->assertOk()
            ->assertSee('Moniteur santé sources')
            ->assertSee('Dernier fetch')
            ->assertSee('J6 upstream timeout')
            ->assertSee('dernier succes')
            ->assertSee('grimba-rss-feed-row--stale', false)
            ->assertSee('data-health-row="stale"', false);

        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, 'J6 Health Fresh Source'),
            strpos($content, 'J6 Health Broken Source'),
            'Stale feeds should appear before healthy feeds in the monitor.'
        );
    }

    private function admin(): User
    {
        $user = User::query()->find(1);

        $this->assertNotNull($user, 'Fixture database must contain the system admin user.');

        return $user;
    }

    private function source(string $name): int
    {
        $slug = Str::slug($name);

        return (int) DB::table('news_sources')->insertGetId([
            'name' => $name,
            'website' => 'https://' . $slug . '.example',
            'bias_rating' => 'center',
            'ownership_type' => 'independent',
            'credibility_score' => 80,
            'country' => 'FR',
            'language' => 'fr',
            'notes' => 'J6 health monitor fixture',
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function cleanupFixtures(): void
    {
        DB::table('rss_feeds')
            ->whereIn('url', [
                'https://example.test/j6-health-fresh.xml',
                'https://example.test/j6-health-broken.xml',
            ])
            ->delete();

        DB::table('news_sources')
            ->whereIn('name', [
                'J6 Health Fresh Source',
                'J6 Health Broken Source',
            ])
            ->delete();
    }
}
