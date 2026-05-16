<?php

namespace Tests\Feature;

use App\Services\GrimbaCategoryClassifier;
use Botble\ACL\Models\User;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class EditorialCategoriesTest extends TestCase
{
    public function test_classifier_assigns_edition_and_topical_news_category(): void
    {
        $europeId = $this->category('Europe', 2);
        $politicsId = $this->category('Politique', 11);
        $sourceName = 'Editorial Categories Europe ' . Str::lower(Str::random(8));
        $this->source($sourceName, 'FR');

        $ids = app(GrimbaCategoryClassifier::class)->classify(
            'Election campaign puts the French government under pressure',
            'Parliament and ministers face a vote after a tense campaign.',
            $sourceName
        );

        $this->assertContains($europeId, $ids);
        $this->assertContains($politicsId, $ids);
    }

    public function test_homepage_chips_show_topics_without_repeating_editorial_locations(): void
    {
        $africaId = $this->category('Afrique', 1);
        $europeId = $this->category('Europe', 2);
        $americasId = $this->category('Amériques', 3);
        $internationalId = $this->category('International', 4);
        $politicsId = $this->category('Politique', 11);
        $sourceId = $this->source('Editorial Categories Homepage ' . Str::lower(Str::random(8)), 'FR');
        $postId = $this->postId('Editorial categories election fixture ' . Str::lower(Str::random(8)), $sourceId);
        $africaPostId = $this->postId('Editorial categories Africa fixture ' . Str::lower(Str::random(8)), $this->source('Editorial Categories Africa ' . Str::lower(Str::random(8)), 'ZA'));
        $americasPostId = $this->postId('Editorial categories Americas fixture ' . Str::lower(Str::random(8)), $this->source('Editorial Categories Americas ' . Str::lower(Str::random(8)), 'US'));
        $internationalPostId = $this->postId('Editorial categories International fixture ' . Str::lower(Str::random(8)), $this->source('Editorial Categories International ' . Str::lower(Str::random(8)), 'QA'));

        DB::table('post_categories')->insertOrIgnore([
            ['post_id' => $postId, 'category_id' => $europeId],
            ['post_id' => $postId, 'category_id' => $politicsId],
            ['post_id' => $africaPostId, 'category_id' => $africaId],
            ['post_id' => $americasPostId, 'category_id' => $americasId],
            ['post_id' => $internationalPostId, 'category_id' => $internationalId],
        ]);

        $this->withUnencryptedCookies(['grimba_region' => 'europe'])
            ->get('/')
            ->assertOk()
            ->assertSee('data-grimba-edition="europe"', false)
            ->assertDontSee('data-category-id="' . $africaId . '"', false)
            ->assertDontSee('data-category-id="' . $europeId . '"', false)
            ->assertDontSee('data-category-id="' . $americasId . '"', false)
            ->assertDontSee('data-category-id="' . $internationalId . '"', false)
            ->assertSee('data-category-id="' . $politicsId . '"', false)
            ->assertSee('Politique');
    }

    public function test_article_cards_show_topic_and_editorial_location_badges(): void
    {
        $this->markTestIncomplete('Legacy markup pre-dossier-reinvention; see docs/GRIMBANEWS_TEST_DEBT_DOSSIER_REINVENTION.md');
        $europeId = $this->category('Europe', 2);
        $politicsId = $this->category('Politique', 11);
        $sourceId = $this->source('Editorial Categories Cards ' . Str::lower(Str::random(8)), 'FR');
        $title = 'Editorial category card badges ' . Str::lower(Str::random(8));
        $postId = $this->postId($title, $sourceId);

        DB::table('post_categories')->insertOrIgnore([
            ['post_id' => $postId, 'category_id' => $europeId],
            ['post_id' => $postId, 'category_id' => $politicsId],
        ]);

        $this->withUnencryptedCookies(['grimba_region' => 'europe'])
            ->get('/blog/politique?style=grid')
            ->assertOk()
            ->assertSee($title)
            ->assertSee('data-grimba-category-role="topic"', false)
            ->assertSee('data-grimba-category-role="edition"', false)
            ->assertSee('Politique')
            ->assertSee('Europe');
    }

    public function test_category_top_sources_respect_selected_editorial_location(): void
    {
        $this->markTestIncomplete('Legacy markup pre-dossier-reinvention; see docs/GRIMBANEWS_TEST_DEBT_DOSSIER_REINVENTION.md');
        $africaId = $this->category('Afrique', 1);
        $europeId = $this->category('Europe', 2);
        $politicsId = $this->category('Politique', 11);
        $suffix = Str::lower(Str::random(8));
        $europeSource = 'Editorial Top Sources Europe ' . $suffix;
        $africaSource = 'Editorial Top Sources Africa ' . $suffix;

        $europePostId = $this->postId('Editorial top source Europe politics ' . $suffix, $this->source($europeSource, 'FR'));
        $africaPostId = $this->postId('Editorial top source Africa politics ' . $suffix, $this->source($africaSource, 'ZA'));

        DB::table('post_categories')->insertOrIgnore([
            ['post_id' => $europePostId, 'category_id' => $europeId],
            ['post_id' => $europePostId, 'category_id' => $politicsId],
            ['post_id' => $africaPostId, 'category_id' => $africaId],
            ['post_id' => $africaPostId, 'category_id' => $politicsId],
        ]);

        $this->withUnencryptedCookies(['grimba_region' => 'europe'])
            ->get('/blog/politique')
            ->assertOk()
            ->assertSee($europeSource)
            ->assertDontSee($africaSource);
    }

    public function test_article_page_lists_full_category_set_not_just_primary_pair(): void
    {
        $this->markTestIncomplete('Legacy markup pre-dossier-reinvention; see docs/GRIMBANEWS_TEST_DEBT_DOSSIER_REINVENTION.md');
        $europeId = $this->category('Europe', 2);
        $politicsId = $this->category('Politique', 11);
        $economyId = $this->category('Économie', 12);
        $worldId = $this->category('Monde', 13);
        $internalGuardrailId = $this->category('Trusted Source Credibility', 16);
        $sourceId = $this->source('Editorial Categories Article ' . Str::lower(Str::random(8)), 'FR');
        $title = 'Editorial category article badges ' . Str::lower(Str::random(8));
        $postId = $this->postId($title, $sourceId);

        DB::table('post_categories')->insertOrIgnore([
            ['post_id' => $postId, 'category_id' => $europeId],
            ['post_id' => $postId, 'category_id' => $politicsId],
            ['post_id' => $postId, 'category_id' => $economyId],
            ['post_id' => $postId, 'category_id' => $worldId],
            ['post_id' => $postId, 'category_id' => $internalGuardrailId],
        ]);

        $path = parse_url(Post::query()->findOrFail($postId)->url, PHP_URL_PATH);
        $this->assertSame('/article/' . Str::slug($title), $path);

        $this->get($path)
            ->assertOk()
            ->assertSee($title)
            ->assertSee('data-grimba-category-role="topic"', false)
            ->assertSee('data-grimba-category-role="edition"', false)
            ->assertSee('Politique')
            ->assertSee('Économie')
            ->assertSee('Monde')
            ->assertSee('Europe')
            ->assertDontSee('Trusted Source Credibility');
    }

    private function category(string $name, int $order): int
    {
        $author = User::query()->find(1);
        $this->assertNotNull($author, 'Fixture database must contain the system admin user.');

        $existing = DB::table('categories')->where('name', $name)->first();
        if ($existing) {
            DB::table('categories')->where('id', $existing->id)->update([
                'status' => 'published',
                'order' => $order,
                'updated_at' => now(),
            ]);
            $id = (int) $existing->id;
        } else {
            $id = (int) DB::table('categories')->insertGetId([
                'name' => $name,
                'parent_id' => 0,
                'description' => 'Editorial category fixture.',
                'status' => 'published',
                'author_id' => $author->getKey(),
                'author_type' => User::class,
                'icon' => null,
                'order' => $order,
                'is_featured' => 0,
                'is_default' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->slug($id, Category::class, Str::slug($name), 'blog');

        return $id;
    }

    private function source(string $name, string $country): int
    {
        return (int) DB::table('news_sources')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name),
            'website' => Str::slug($name) . '.test',
            'bias_rating' => 'center',
            'credibility_score' => 91,
            'country' => $country,
            'language' => 'fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function postId(string $name, int $sourceId): int
    {
        $author = User::query()->find(1);
        $this->assertNotNull($author, 'Fixture database must contain the system admin user.');

        $id = (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'Election campaign and government vote fixture.',
            'content' => '<p>Election campaign and government vote fixture.</p>',
            'status' => 'published',
            'author_id' => $author->getKey(),
            'author_type' => User::class,
            'source_id' => $sourceId,
            'source_name' => DB::table('news_sources')->where('id', $sourceId)->value('name'),
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->slug($id, Post::class, Str::slug($name), 'blog');

        return $id;
    }

    private function slug(int $referenceId, string $referenceType, string $key, string $prefix): void
    {
        if ($key === '') {
            $key = 'editorial-category-fixture-' . $referenceId;
        }

        $slug = $key;
        $i = 2;
        while (DB::table('slugs')
            ->where('key', $slug)
            ->where('reference_type', $referenceType)
            ->where('reference_id', '!=', $referenceId)
            ->exists()) {
            $slug = $key . '-' . $i;
            $i++;
        }

        DB::table('slugs')->updateOrInsert(
            ['reference_id' => $referenceId, 'reference_type' => $referenceType],
            ['key' => $slug, 'prefix' => $prefix, 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
