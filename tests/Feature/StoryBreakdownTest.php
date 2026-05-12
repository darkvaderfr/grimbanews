<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Botble\Blog\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            ->assertSee('position: relative;', false)
            ->assertSee('[data-bs-theme="dark"]', false)
            ->assertSee('--gbd-card:', false)
            ->assertSee('Breakdown Left', false)
            ->assertSee('Private equity', false);
    }

    public function test_comparison_page_sanitizes_newsapi_truncation_markers_in_snippets(): void
    {
        $clusterId = 7654322;
        $now = now();

        foreach (['left', 'center'] as $index => $bias) {
            DB::table('posts')->insert([
                'name' => 'Sanitized comparison fixture ' . $bias,
                'description' => 'Prominent Jewish American leader and Israel defender Abraham Abe Foxman has died at age 86. [+4285 chars]',
                'content' => '<p>Prominent Jewish American leader and Israel defender Abraham Abe Foxman has died at age 86. The Anti-Defamation League confirmed his death on Sunday, calling… [+4285 chars]</p>',
                'status' => 'published',
                'author_id' => 1,
                'author_type' => User::class,
                'is_featured' => 0,
                'image' => null,
                'views' => 0,
                'source_name' => 'Sanitized Fixture Source ' . $bias,
                'bias_rating' => $bias,
                'is_blindspot' => 0,
                'credibility_score' => 80,
                'ownership_type' => 'fixture',
                'story_cluster_id' => $clusterId,
                'created_at' => $now->copy()->addMinutes($index),
                'updated_at' => $now->copy()->addMinutes($index),
            ]);
        }

        $this->get('/comparatif/' . $clusterId)
            ->assertOk()
            ->assertSee('Prominent Jewish American leader and Israel defender')
            ->assertDontSee('[+4285 chars]')
            ->assertDontSee('4285 chars');
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

    public function test_article_urls_are_canonicalized_away_from_blog_prefix(): void
    {
        $now = now();
        $postId = DB::table('posts')->insertGetId([
            'name' => 'Canonical article fixture',
            'description' => 'Canonical article URL fixture.',
            'content' => '<p>Canonical article URL fixture.</p>',
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
            'key' => 'canonical-article-fixture',
            'reference_id' => $postId,
            'reference_type' => Post::class,
            'prefix' => 'blog',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $post = Post::query()->with('slugable')->findOrFail($postId);

        $this->assertSame('/article/canonical-article-fixture', parse_url($post->url, PHP_URL_PATH));

        $this->get('/blog/canonical-article-fixture?utm=legacy')
            ->assertRedirect('/article/canonical-article-fixture?utm=legacy');

        $this->get('/article/canonical-article-fixture')
            ->assertOk()
            ->assertSee('grimba-article-shell', false)
            ->assertSee('Canonical article fixture');
    }

    public function test_orphan_article_page_renders_readable_feed_fallback_in_reader_block(): void
    {
        $now = now();
        $slug = 'orphan-feed-fallback-' . Str::lower(Str::random(8));
        $fallback = 'Readable orphan fallback body starts here with enough context for the reader. '
            . str_repeat('This fallback sentence keeps useful article context inside GrimbaNews. ', 5);

        $postId = DB::table('posts')->insertGetId([
            'name' => 'Orphan feed fallback fixture',
            'description' => 'Orphan feed fallback description.',
            'content' => '<p><a href="https://example.test/orphan-fallback" target="_blank" rel="noopener">Lire l’article original</a></p><p>' . $fallback . '</p>',
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
            'full_content' => null,
            'full_fetched_at' => $now,
            'full_extract_error' => 'http 403',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('slugs')->insert([
            'key' => $slug,
            'reference_id' => $postId,
            'reference_type' => Post::class,
            'prefix' => 'blog',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $html = $this->get('/article/' . $slug)
            ->assertOk()
            ->assertSee('grimba-full-article--reader', false)
            ->assertSee('Extrait disponible')
            ->assertSee("Lire l'extrait disponible")
            ->assertSee('Readable orphan fallback body starts here')
            ->getContent();

        $this->assertSame(1, substr_count($html, 'Readable orphan fallback body starts here'));
        $this->assertStringNotContainsString('Lire l’article original</a></p><p>Readable orphan fallback body starts here', $html);
    }

    public function test_orphan_article_reader_prefers_extracted_body_over_short_translated_ingest_body(): void
    {
        $now = now();
        $slug = 'orphan-full-content-' . Str::lower(Str::random(8));
        $full = 'Extracted original article body remains visible inside the reader. '
            . str_repeat('This extracted paragraph is the durable in-app article body for readers. ', 5);

        $postId = DB::table('posts')->insertGetId([
            'name' => 'Original English title',
            'description' => 'Original short description.',
            'content' => '<p>Original short ingest body.</p>',
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
            'original_language' => 'en',
            'translated_to' => 'fr',
            'translated_name' => 'Titre français',
            'translated_description' => 'Description française.',
            'translated_content' => '<p>Résumé traduit trop court.</p>',
            'full_content' => '<p>' . $full . '</p>',
            'full_fetched_at' => $now,
            'full_extract_error' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('slugs')->insert([
            'key' => $slug,
            'reference_id' => $postId,
            'reference_type' => Post::class,
            'prefix' => 'blog',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withUnencryptedCookies([
            'grimba_lang' => 'fr',
            'grimba_onboarded' => '1',
        ])
            ->get('/article/' . $slug)
            ->assertOk()
            ->assertSee('Texte intégral')
            ->assertSee("Lire l'article complet")
            ->assertSee('Extracted original article body remains visible')
            ->assertDontSee('Résumé traduit trop court.');
    }
}
