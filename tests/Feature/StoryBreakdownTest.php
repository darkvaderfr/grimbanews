<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StoryBreakdownTest extends TestCase
{
    public function test_comparison_page_renders_bias_factuality_and_ownership_breakdown(): void
    {
        $clusterId = 7654321;
        $now = now();

        $sources = collect([
            [
                'name' => 'Breakdown Left',
                'website' => 'https://left.example.com',
                'bias_rating' => 'left',
                'ownership_type' => 'independent',
                'credibility_score' => 91,
                'country' => 'US',
                'language' => 'en',
                'slug' => 'breakdown-left',
                'owner_name' => 'Independent Trust',
            ],
            [
                'name' => 'Breakdown Center',
                'website' => 'https://center.example.com',
                'bias_rating' => 'center',
                'ownership_type' => 'media_conglomerate',
                'credibility_score' => 78,
                'country' => 'US',
                'language' => 'en',
                'slug' => 'breakdown-center',
                'owner_name' => 'Center Group',
            ],
            [
                'name' => 'Breakdown Right',
                'website' => 'https://right.example.com',
                'bias_rating' => 'right',
                'ownership_type' => 'private_equity',
                'credibility_score' => 62,
                'country' => 'US',
                'language' => 'en',
                'slug' => 'breakdown-right',
                'owner_name' => 'Right Capital',
            ],
        ])->map(function (array $source) use ($now) {
            $source['created_at'] = $now;
            $source['updated_at'] = $now;
            $source['id'] = DB::table('news_sources')->insertGetId($source);

            return $source;
        });

        foreach ($sources as $index => $source) {
            DB::table('posts')->insert([
                'name' => 'Breakdown story fixture ' . $source['bias_rating'],
                'description' => 'Comparison breakdown fixture.',
                'content' => '<p>Comparison breakdown fixture.</p>',
                'status' => 'published',
                'author_id' => 1,
                'author_type' => User::class,
                'is_featured' => 0,
                'image' => null,
                'views' => 0,
                'source_id' => $source['id'],
                'source_name' => $source['name'],
                'bias_rating' => $source['bias_rating'],
                'is_blindspot' => 0,
                'credibility_score' => $source['credibility_score'],
                'ownership_type' => $source['ownership_type'],
                'story_cluster_id' => $clusterId,
                'created_at' => $now->copy()->addMinutes($index),
                'updated_at' => $now->copy()->addMinutes($index),
            ]);
        }

        $this->get('/comparatif/' . $clusterId)
            ->assertOk()
            ->assertSee('grimba-breakdown', false)
            ->assertSee('Biais', false)
            ->assertSee('Factualité', false)
            ->assertSee('Propriété', false)
            ->assertSee('grimba-breakdown__owner-grid', false)
            ->assertSee('grimba-breakdown__mini-fill', false)
            ->assertSee('Breakdown Left', false)
            ->assertSee('Private equity', false);
    }

    public function test_command_palette_uses_news_language_urls_not_blog_index_urls(): void
    {
        $now = now();
        $postId = DB::table('posts')->insertGetId([
            'name' => 'Command palette fixture',
            'description' => 'Command palette URL fixture.',
            'content' => '<p>Command palette URL fixture.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'image' => null,
            'views' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'credibility_score' => 80,
            'ownership_type' => 'fixture',
            'story_cluster_id' => null,
            'source_name' => 'Fixture Source',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('slugs')->insert([
            'key' => 'command-palette-fixture',
            'reference_id' => $postId,
            'reference_type' => \Botble\Blog\Models\Post::class,
            'prefix' => 'blog',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->getJson('/command-palette.json')
            ->assertOk()
            ->assertJsonFragment(['url' => url('/article/command-palette-fixture')])
            ->assertDontSee('/blog/command-palette-fixture', false);
    }
}
