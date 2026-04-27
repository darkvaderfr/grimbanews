<?php

namespace Tests\Feature;

use Botble\Blog\Models\Post;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MostReadByBiasTest extends TestCase
{
    public function test_homepage_renders_most_read_by_bias_rail(): void
    {
        foreach (['left', 'center', 'right'] as $index => $bias) {
            Post::query()
                ->where('status', 'published')
                ->where('bias_rating', $bias)
                ->limit(1)
                ->update(['views' => 900 - $index]);
        }

        $this->get('/')
            ->assertOk()
            ->assertSee('Les plus lus par tendance')
            ->assertSee('Lecture publique')
            ->assertSee('Gauche')
            ->assertSee('Centre')
            ->assertSee('Droite');
    }

    public function test_article_view_increments_once_per_session_window(): void
    {
        $post = Post::query()
            ->where('status', 'published')
            ->whereNotNull('name')
            ->firstOrFail();

        DB::table('posts')->where('id', $post->id)->update(['views' => 10]);

        $path = parse_url($post->url, PHP_URL_PATH) ?: '/';

        $this->withCookie('grimba_read', '')
            ->get($path)
            ->assertOk();

        $this->assertSame(11, (int) DB::table('posts')->where('id', $post->id)->value('views'));

        $this
            ->get($path)
            ->assertOk();

        $this->assertSame(11, (int) DB::table('posts')->where('id', $post->id)->value('views'));
    }
}
