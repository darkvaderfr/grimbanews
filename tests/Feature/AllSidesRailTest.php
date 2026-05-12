<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AllSidesRailTest extends TestCase
{
    public function test_all_sides_cards_link_to_cluster_comparison_not_blog_index(): void
    {
        $clusterId = 998877;
        $now = now();

        foreach (['left', 'center', 'right'] as $index => $bias) {
            DB::table('posts')->insert([
                'name' => 'All sides rail route fixture ' . $bias,
                'description' => 'Route fixture for all sides rail.',
                'content' => '<p>Route fixture.</p>',
                'status' => 'published',
                'author_id' => 1,
                'author_type' => User::class,
                'is_featured' => 0,
                'image' => null,
                'views' => 0,
                'bias_rating' => $bias,
                'is_blindspot' => 0,
                'credibility_score' => 80,
                'ownership_type' => 'fixture',
                'story_cluster_id' => $clusterId,
                'source_name' => 'Fixture Source ' . $bias,
                'created_at' => $now->copy()->addMinutes($index),
                'updated_at' => $now->copy()->addMinutes($index),
            ]);
        }

        $this->get('/')
            ->assertOk()
            ->assertSee('grimba-all-sides__card', false)
            ->assertSee('/comparatif/' . $clusterId, false)
            ->assertSee('-webkit-text-fill-color:var(--gn-ink,#1a1713);', false);

        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-all-sides__card', $css);
        $this->assertStringContainsString('-webkit-text-fill-color: #fffaf0 !important;', $css);
        $this->assertStringContainsString('scroll-snap-type: x proximity;', $css);
        $this->assertStringContainsString('grid-auto-columns: minmax(270px, 84vw) !important;', $css);
    }
}
