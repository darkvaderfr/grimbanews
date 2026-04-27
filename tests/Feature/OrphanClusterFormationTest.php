<?php

namespace Tests\Feature;

use App\Services\GrimbaRssPoller;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrphanClusterFormationTest extends TestCase
{
    private function insertOrphan(string $title, int $minutesAgo = 0): int
    {
        return (int) DB::table('posts')->insertGetId([
            'name' => $title,
            'description' => 'S132 regression fixture',
            'content' => '<p>S132 regression fixture</p>',
            'status' => 'draft',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'views' => 0,
            'bias_rating' => 'center',
            'story_cluster_id' => null,
            'source_name' => 'Fixture',
            'created_at' => now()->subMinutes($minutesAgo),
            'updated_at' => now()->subMinutes($minutesAgo),
        ]);
    }

    public function test_saved_orphan_does_not_form_cluster_with_itself(): void
    {
        $postId = $this->insertOrphan('S132 famine corridor opens after Sudan ceasefire talks');
        $before = DB::table('story_clusters')->count();

        $clusterId = GrimbaRssPoller::findOrFormCluster(
            'S132 famine corridor opens after Sudan ceasefire talks',
            30,
            0.30,
            false,
            null,
            $postId,
        );

        $this->assertNull($clusterId);
        $this->assertSame($before, DB::table('story_clusters')->count());
        $this->assertNull(DB::table('posts')->where('id', $postId)->value('story_cluster_id'));
    }

    public function test_matching_orphans_form_new_cluster_without_mutating_caller(): void
    {
        $firstId = $this->insertOrphan('S132 Paris climate pact approved after industry exemptions', 20);
        $secondId = $this->insertOrphan('S132 industry exemptions shape Paris climate pact approval', 5);

        $clusterId = GrimbaRssPoller::findOrFormCluster(
            'S132 industry exemptions shape Paris climate pact approval',
            30,
            0.30,
            false,
            null,
            $secondId,
        );

        $this->assertIsInt($clusterId);
        $this->assertTrue(DB::table('story_clusters')->where('id', $clusterId)->exists());
        $this->assertSame($clusterId, (int) DB::table('posts')->where('id', $firstId)->value('story_cluster_id'));
        $this->assertNull(DB::table('posts')->where('id', $secondId)->value('story_cluster_id'));

        DB::table('posts')->where('id', $secondId)->update(['story_cluster_id' => $clusterId]);

        $this->assertSame(2, DB::table('posts')->where('story_cluster_id', $clusterId)->count());
    }
}
