<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SearchFacetsTest extends TestCase
{
    public function test_search_filters_by_owner_and_date_range(): void
    {
        $suffix = Str::lower(Str::random(8));
        $author = User::query()->find(1);

        $this->assertNotNull($author, 'Fixture database must contain the system admin user.');

        $matchingSourceId = $this->sourceId('Facet Match Source ' . $suffix, 'Facet Owner Alpha ' . $suffix, 'left');
        $otherOwnerSourceId = $this->sourceId('Facet Other Owner ' . $suffix, 'Facet Owner Beta ' . $suffix, 'left');
        $oldSourceId = $this->sourceId('Facet Old Source ' . $suffix, 'Facet Owner Alpha ' . $suffix, 'left');

        $matchId = $this->postId(
            'facetneedle current owner article ' . $suffix,
            $matchingSourceId,
            $author,
            now()->subDays(2)
        );
        $otherOwnerId = $this->postId(
            'facetneedle wrong owner article ' . $suffix,
            $otherOwnerSourceId,
            $author,
            now()->subDays(2)
        );
        $oldId = $this->postId(
            'facetneedle old owner article ' . $suffix,
            $oldSourceId,
            $author,
            now()->subDays(14)
        );

        $this->withUnencryptedCookies(['grimba_region' => 'europe'])
            ->get('/search?' . http_build_query([
                'q' => 'facetneedle',
                'owner' => 'Facet Owner Alpha ' . $suffix,
                'from_date' => now()->subDays(5)->toDateString(),
                'to_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee((string) DB::table('posts')->where('id', $matchId)->value('name'))
            ->assertDontSee((string) DB::table('posts')->where('id', $otherOwnerId)->value('name'))
            ->assertDontSee((string) DB::table('posts')->where('id', $oldId)->value('name'))
            ->assertSee('name="owner"', false)
            ->assertSee('name="from_date"', false)
            ->assertSee('name="to_date"', false);
    }

    private function sourceId(string $name, string $owner, string $bias): int
    {
        return (int) DB::table('news_sources')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => Str::slug($name) . '.test',
            'bias_rating' => $bias,
            'ownership_type' => 'corporate',
            'owner_name' => $owner,
            'credibility_score' => 80,
            'country' => 'FR',
            'language' => 'fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function postId(string $name, int $sourceId, User $author, mixed $createdAt): int
    {
        $sourceName = DB::table('news_sources')->where('id', $sourceId)->value('name');

        $postId = (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'Search facet fixture for owner and date range.',
            'content' => '<p>Search facet fixture body.</p>',
            'status' => 'published',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'source_id' => $sourceId,
            'source_name' => $sourceName,
            'bias_rating' => 'left',
            'original_language' => 'fr',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        DB::table('slugs')->insert([
            'key' => Str::slug($name),
            'reference_id' => $postId,
            'reference_type' => \Botble\Blog\Models\Post::class,
            'prefix' => 'blog',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $postId;
    }
}
