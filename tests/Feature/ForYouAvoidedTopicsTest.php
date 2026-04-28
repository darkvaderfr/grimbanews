<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Botble\Blog\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ForYouAvoidedTopicsTest extends TestCase
{
    public function test_for_you_shows_avoided_recent_topics_after_read_history_threshold(): void
    {
        $suffix = Str::lower(Str::random(8));
        $author = User::query()->find(1);

        $this->assertNotNull($author, 'Fixture database must contain the system admin user.');

        $sourceId = $this->sourceId('For You Source ' . $suffix);
        $readCategoryId = $this->categoryId('For You Read Topic ' . $suffix, $author);
        $avoidedCategoryId = $this->categoryId('For You Avoided Topic ' . $suffix, $author);

        $readPostIds = [];
        for ($i = 1; $i <= 11; $i++) {
            $readPostIds[] = $this->postId(
                'for you read history story ' . $i . ' ' . $suffix,
                $sourceId,
                $author,
                $readCategoryId
            );
        }

        $this->postId(
            'for you avoided topic story ' . $suffix,
            $sourceId,
            $author,
            $avoidedCategoryId
        );

        $this->withUnencryptedCookie('grimba_read', implode(',', $readPostIds))
            ->get('/pour-vous')
            ->assertOk()
            ->assertSee('Sujets que vous évitez')
            ->assertSee('For You Avoided Topic ' . $suffix)
            ->assertSee('/blog?categorie=' . $avoidedCategoryId, false);
    }

    private function sourceId(string $name): int
    {
        return (int) DB::table('news_sources')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => Str::slug($name) . '.test',
            'bias_rating' => 'center',
            'ownership_type' => 'independent',
            'owner_name' => 'For You Test Owner',
            'credibility_score' => 82,
            'country' => 'FR',
            'language' => 'fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function categoryId(string $name, User $author): int
    {
        return (int) DB::table('categories')->insertGetId([
            'name' => $name,
            'parent_id' => 0,
            'description' => 'For You topic fixture.',
            'status' => 'published',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'icon' => null,
            'order' => 0,
            'is_featured' => 0,
            'is_default' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function postId(string $name, int $sourceId, User $author, int $categoryId): int
    {
        $now = now()->subDay();
        $sourceName = DB::table('news_sources')->where('id', $sourceId)->value('name');

        $postId = (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'For You personalization fixture.',
            'content' => '<p>For You personalization fixture body.</p>',
            'status' => 'published',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'source_id' => $sourceId,
            'source_name' => $sourceName,
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('post_categories')->insert([
            'post_id' => $postId,
            'category_id' => $categoryId,
        ]);

        DB::table('slugs')->insert([
            'key' => Str::slug($name),
            'reference_id' => $postId,
            'reference_type' => Post::class,
            'prefix' => 'blog',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $postId;
    }
}
