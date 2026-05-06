<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class CoverageMapAdminTest extends TestCase
{
    public function test_coverage_map_renders_missing_side_visualization(): void
    {
        $this->cleanupFixtures();

        $gapClusterId = $this->cluster('J3 Coverage Gap Fixture');
        $balancedClusterId = $this->cluster('J3 Coverage Balanced Fixture');

        $this->article('J3 left coverage one', $gapClusterId, 'left');
        $this->article('J3 left coverage two', $gapClusterId, 'left');
        $this->article('J3 balanced left', $balancedClusterId, 'left');
        $this->article('J3 balanced center', $balancedClusterId, 'center');
        $this->article('J3 balanced right', $balancedClusterId, 'right');

        $response = $this->actingAs($this->admin())
            ->get('/admin/grimba/coverage-map?filter=missing-right');

        $response
            ->assertOk()
            ->assertSee('Carte de couverture')
            ->assertSee('J3 Coverage Gap Fixture')
            ->assertSee('Unilatérale')
            ->assertSee('Droite')
            ->assertSee('33%')
            ->assertSee('grimba-coverage-bar', false)
            ->assertSee('grimba-coverage-score', false)
            ->assertDontSee('J3 Coverage Balanced Fixture');
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
            'description' => 'J3 coverage map fixture',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function article(string $name, int $clusterId, string $bias): int
    {
        return (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'J3 coverage map fixture article.',
            'content' => '<p>J3 coverage map fixture article.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'views' => 0,
            'bias_rating' => $bias,
            'is_blindspot' => 0,
            'story_cluster_id' => $clusterId,
            'source_name' => 'J3 Coverage Fixture Source',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function cleanupFixtures(): void
    {
        $clusterIds = DB::table('story_clusters')
            ->whereIn('topic', [
                'J3 Coverage Gap Fixture',
                'J3 Coverage Balanced Fixture',
            ])
            ->pluck('id')
            ->all();

        if ($clusterIds !== []) {
            DB::table('posts')->whereIn('story_cluster_id', $clusterIds)->delete();
            DB::table('story_clusters')->whereIn('id', $clusterIds)->delete();
        }

        DB::table('posts')
            ->whereIn('name', [
                'J3 left coverage one',
                'J3 left coverage two',
                'J3 balanced left',
                'J3 balanced center',
                'J3 balanced right',
            ])
            ->delete();
    }
}
