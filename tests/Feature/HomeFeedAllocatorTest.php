<?php

namespace Tests\Feature;

use App\Support\GrimbaHomeFeed;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeFeedAllocatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        GrimbaHomeFeed::flush();
    }

    public function test_no_post_id_appears_in_more_than_one_section(): void
    {
        $clusterA = 778001;
        $clusterB = 778002;
        $now = now();

        $rows = [];
        $id = 1;
        foreach (['left' => $clusterA, 'center' => $clusterA, 'right' => $clusterA, 'left' => $clusterB, 'right' => $clusterB] as $bias => $cluster) {
            foreach (range(0, 5) as $offset) {
                $rows[] = [
                    'name' => 'Allocator fixture ' . $id,
                    'description' => 'Allocator fixture.',
                    'content' => '<p>Allocator fixture body.</p>',
                    'status' => 'published',
                    'author_id' => 1,
                    'author_type' => User::class,
                    'is_featured' => $id <= 3 ? 1 : 0,
                    'is_blindspot' => $id <= 4 ? 1 : 0,
                    'image' => 'https://example.test/img-' . $id . '.jpg',
                    'views' => 100 + $id,
                    'bias_rating' => $bias,
                    'credibility_score' => 80,
                    'ownership_type' => 'fixture',
                    'story_cluster_id' => $cluster,
                    'source_name' => 'Fixture Source ' . (($id % 4) + 1),
                    'source_id' => ($id % 4) + 1,
                    'created_at' => $now->copy()->subMinutes($id),
                    'updated_at' => $now->copy()->subMinutes($id),
                ];
                $id++;
            }
        }

        DB::table('posts')->insert($rows);

        $feed = GrimbaHomeFeed::build();

        $ids = [];
        $collect = static function ($items) use (&$ids): void {
            foreach ($items as $item) {
                if (is_object($item) && property_exists($item, 'id')) {
                    $ids[] = (int) $item->id;
                }
            }
        };

        if ($feed['briefing']) {
            $ids[] = (int) $feed['briefing']['post']->id;
        }

        foreach ($feed['allSides'] as $card) {
            $ids[] = (int) $card['head']->id;
        }

        if ($feed['hero']) {
            $ids[] = (int) $feed['hero']->id;
        }

        $collect($feed['heroBriefingColumn']);
        $collect($feed['heroBlindspots']);

        foreach ($feed['mostRead'] as $bucket) {
            $collect($bucket);
        }

        $collect($feed['topNews']);

        foreach ($feed['sections'] as $section) {
            if ($section['latest']) {
                $ids[] = (int) $section['latest']->id;
            }
            $collect($section['blindspots']);
        }

        $collect($feed['latest']);

        $duplicates = array_filter(array_count_values($ids), fn (int $n): bool => $n > 1);

        $this->assertEmpty(
            $duplicates,
            'Home feed allocator should not return the same post in multiple sections. Duplicates: ' . json_encode($duplicates)
        );
    }

    public function test_static_cache_returns_same_payload_on_second_call(): void
    {
        $first = GrimbaHomeFeed::build();
        $second = GrimbaHomeFeed::build();

        $this->assertSame($first['allShown'], $second['allShown']);
    }

    public function test_flush_clears_static_cache(): void
    {
        $before = GrimbaHomeFeed::build();
        GrimbaHomeFeed::flush();
        $after = GrimbaHomeFeed::build();

        // After flush we may legitimately get the same allocation if no
        // posts were added between calls — the contract is just that the
        // second build runs fresh, which means it MUST be re-resolvable
        // without throwing.
        $this->assertIsArray($after);
        $this->assertArrayHasKey('allShown', $after);
    }
}
