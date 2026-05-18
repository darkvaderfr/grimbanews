<?php

namespace Tests\Feature;

use App\Support\GrimbaTranslationPresenter;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * S-LSAT-02 — pin the contract of `filterForTargetLocale()`.
 *
 * Strict locale filtering MUST:
 *   - keep native-target posts
 *   - keep wrong-locale posts WHEN they carry a target-locale translation
 *     (either in-row `translated_*` or join-table `grimba_post_translations`)
 *   - drop wrong-locale posts that have NO translation
 *   - drop NULL-origin-language posts (unclassified)
 *   - pass the query through unchanged when target is invalid
 *
 * Tests use fixture-only post inserts inside a dedicated ID range
 * (995xxx) so the live corpus is unaffected and no `RefreshDatabase`
 * wipe is required.
 */
class GrimbaFilterForTargetLocaleTest extends TestCase
{
    private const FIXTURE_PREFIX = 'lsat02-fixture-';
    /** @var array<int, int> */
    private array $createdPostIds = [];
    /** @var array<int, int> */
    private array $createdTranslationIds = [];
    private string $suffix;

    protected function setUp(): void
    {
        parent::setUp();
        $this->suffix = Str::lower(Str::random(8));
    }

    protected function tearDown(): void
    {
        if ($this->createdTranslationIds && DB::getSchemaBuilder()->hasTable('grimba_post_translations')) {
            DB::table('grimba_post_translations')->whereIn('id', $this->createdTranslationIds)->delete();
        }
        if ($this->createdPostIds) {
            DB::table('posts')->whereIn('id', $this->createdPostIds)->delete();
        }
        parent::tearDown();
    }

    public function test_strict_fr_keeps_fr_originals_and_translated_en_originals(): void
    {
        $frNative = $this->insertPost('fr', null);
        $enWithFrTranslation = $this->insertPost('en', null);
        $this->insertInRowTranslation($enWithFrTranslation, 'fr');
        $enNoTranslation = $this->insertPost('en', null);
        $unclassified = $this->insertPost('', null);

        $ids = $this->runFilter('fr');

        $this->assertContains($frNative, $ids, 'FR-native must survive');
        $this->assertContains($enWithFrTranslation, $ids, 'EN-origin with FR in-row translation must survive');
        $this->assertNotContains($enNoTranslation, $ids, 'EN-origin without translation must be dropped');
        $this->assertNotContains($unclassified, $ids, 'NULL-language must be dropped');
    }

    public function test_strict_en_keeps_en_originals_and_translated_fr_originals(): void
    {
        $enNative = $this->insertPost('en', null);
        $frWithEnTranslation = $this->insertPost('fr', null);
        $this->insertInRowTranslation($frWithEnTranslation, 'en');
        $frNoTranslation = $this->insertPost('fr', null);

        $ids = $this->runFilter('en');

        $this->assertContains($enNative, $ids);
        $this->assertContains($frWithEnTranslation, $ids);
        $this->assertNotContains($frNoTranslation, $ids);
    }

    public function test_strict_picks_up_join_table_translations(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('grimba_post_translations')) {
            $this->markTestSkipped('grimba_post_translations table not migrated.');
        }

        $enWithFrJoinRow = $this->insertPost('en', null);
        $this->insertJoinTranslation($enWithFrJoinRow, 'fr');

        $ids = $this->runFilter('fr');
        $this->assertContains($enWithFrJoinRow, $ids, 'Join-table-only translation must satisfy strict filter.');
    }

    /**
     * Zen audit add-on (2026-05-18): explicitly assert the join-table
     * branch satisfies the filter EVEN WHEN the in-row `translated_to`
     * column is NULL. Catches a future regression where someone
     * mistakenly ANDs the branches together.
     */
    public function test_join_table_only_satisfies_filter_when_in_row_markers_are_null(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('grimba_post_translations')) {
            $this->markTestSkipped('grimba_post_translations table not migrated.');
        }

        $post = $this->insertPost('en', null);
        // Explicitly NULL in-row markers so only the join-table row
        // can satisfy the filter.
        DB::table('posts')->where('id', $post)->update([
            'translated_to' => null,
            'translated_name' => null,
        ]);
        $this->insertJoinTranslation($post, 'fr');

        $ids = $this->runFilter('fr');
        $this->assertContains(
            $post,
            $ids,
            'Filter must accept post when ONLY the join-table row carries the translation.'
        );
    }

    public function test_empty_translated_name_does_not_satisfy_filter(): void
    {
        // Half-rolled-back state: in-row marker says `translated_to=fr`
        // but `translated_name` is empty. Strict filter must NOT credit.
        $halfRolled = $this->insertPost('en', null);
        DB::table('posts')->where('id', $halfRolled)->update([
            'translated_to' => 'fr',
            'translated_name' => '',
        ]);

        $ids = $this->runFilter('fr');
        $this->assertNotContains($halfRolled, $ids);
    }

    public function test_invalid_target_passes_query_through_unchanged(): void
    {
        $any = $this->insertPost('en', null);

        $query = DB::table('posts')
            ->where('name', 'LIKE', self::FIXTURE_PREFIX . '%')
            ->whereIn('id', $this->createdPostIds);

        // Invalid target → query is returned unchanged.
        GrimbaTranslationPresenter::filterForTargetLocale($query, 'klingon', applyOrder: false);

        $ids = $query->pluck('id')->all();
        $this->assertContains($any, $ids, 'Invalid target must NOT silently filter out content.');
    }

    /**
     * Run the filter against a query scoped to this test's fixtures
     * and return the surviving post ids.
     *
     * @return array<int, int>
     */
    private function runFilter(string $target): array
    {
        $query = DB::table('posts')
            ->whereIn('id', $this->createdPostIds);

        GrimbaTranslationPresenter::filterForTargetLocale($query, $target, applyOrder: false);

        return array_map('intval', $query->pluck('id')->all());
    }

    private function insertPost(string $originalLanguage, ?string $editorialRegion): int
    {
        $id = (int) DB::table('posts')->insertGetId([
            'name' => self::FIXTURE_PREFIX . $this->suffix . '-' . Str::lower(Str::random(4)),
            'description' => 'S-LSAT-02 filter fixture',
            'content' => '<p>fixture</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'views' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'fixture',
            'original_language' => $originalLanguage,
            'editorial_region' => $editorialRegion,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdPostIds[] = $id;
        return $id;
    }

    private function insertInRowTranslation(int $postId, string $locale): void
    {
        DB::table('posts')->where('id', $postId)->update([
            'translated_to' => $locale,
            'translated_name' => 'translated-' . $locale . '-' . $this->suffix,
            'translated_description' => 'desc',
            'translated_content' => '<p>body</p>',
            'translated_at' => now(),
        ]);
    }

    private function insertJoinTranslation(int $postId, string $locale): void
    {
        $id = (int) DB::table('grimba_post_translations')->insertGetId([
            'post_id' => $postId,
            'locale' => $locale,
            'translated_name' => 'join-' . $locale . '-' . $this->suffix,
            'translated_description' => 'desc',
            'translated_content' => '<p>body</p>',
            'translation_driver' => 'fixture',
            'translated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdTranslationIds[] = $id;
    }
}
