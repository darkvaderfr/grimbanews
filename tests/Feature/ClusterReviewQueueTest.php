<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ClusterReviewQueueTest extends TestCase
{
    public function test_cluster_review_queue_renders_conflict_and_persists_action(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->cleanupFixtures();

        $clusterId = $this->cluster('J1 Cluster Review Dense One Sided');
        for ($i = 1; $i <= 6; $i++) {
            $this->article('J1 dense one sided article ' . $i, $clusterId, 'left');
        }

        $this->actingAs($this->admin())
            ->get('/admin/grimba/cluster-review')
            ->assertOk()
            ->assertSee('Revue dossiers')
            ->assertSee('J1 Cluster Review Dense One Sided')
            ->assertSee('Unilatéral dense')
            ->assertSee('Fusionner')
            ->assertSee('Scinder')
            ->assertSee('Approuver');

        $this->actingAs($this->admin())
            ->post('/admin/grimba/cluster-review/' . $clusterId . '/action', [
                'action' => 'approve',
            ])
            ->assertRedirect('/admin/grimba/cluster-review')
            ->assertSessionHas('success_msg');

        $cluster = DB::table('story_clusters')->where('id', $clusterId)->first();

        $this->assertSame('approve', $cluster->review_action);
        $this->assertNotNull($cluster->reviewed_at);
    }

    private function admin(): User
    {
        $user = User::query()->find(1);

        $this->assertNotNull($user, 'Fixture database must contain the system admin user.');

        return $user;
    }

    private function cluster(string $topic): int
    {
        return (int) DB::table('story_clusters')->insertGetId([
            'topic' => $topic,
            'description' => 'J1 cluster review fixture',
            'review_action' => null,
            'reviewed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function article(string $name, int $clusterId, string $bias): int
    {
        return (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'J1 cluster review fixture article.',
            'content' => '<p>J1 cluster review fixture article.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'views' => 0,
            'bias_rating' => $bias,
            'is_blindspot' => 0,
            'story_cluster_id' => $clusterId,
            'source_name' => 'J1 Cluster Review Fixture',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function cleanupFixtures(): void
    {
        $clusterIds = DB::table('story_clusters')
            ->where('topic', 'J1 Cluster Review Dense One Sided')
            ->pluck('id')
            ->all();

        if ($clusterIds !== []) {
            DB::table('posts')->whereIn('story_cluster_id', $clusterIds)->delete();
            DB::table('story_clusters')->whereIn('id', $clusterIds)->delete();
        }

        DB::table('posts')
            ->where('source_name', 'J1 Cluster Review Fixture')
            ->delete();
    }
}
