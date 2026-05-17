<?php

namespace Tests\Feature;

use App\Support\GrimbaTranslationPresenter;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * S-LANG-15 (Vader 2026-05-17) — translation atomicity test.
 *
 * The presenter trusts two redundant write-paths: the per-post in-row
 * cache (`posts.translated_*`) and the per-locale join table
 * (`grimba_post_translations`). When both exist, they should agree.
 * When one is stale, the presenter must still pick the source-of-truth.
 *
 * This test pins the invariants so a future drift (e.g. a half-rolled-
 * back transaction in `GrimbaTranslatePending`) gets caught before
 * production sees inconsistent reader-facing translations.
 */
class TranslationAtomicityTest extends TestCase
{
    public function test_in_row_and_join_table_agree_for_same_locale(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('grimba_post_translations')) {
            $this->markTestSkipped('grimba_post_translations table not yet migrated.');
        }

        $suffix = Str::lower(Str::random(8));
        $now = now();
        $postId = $this->insertFixturePost('atomicity native ' . $suffix, 'fr', $now);

        // Write through the canonical pair: in-row cache + join row.
        DB::table('posts')->where('id', $postId)->update([
            'translated_name' => 'atomicity translated ' . $suffix,
            'translated_description' => 'desc',
            'translated_content' => '<p>body</p>',
            'translated_to' => 'en',
            'translated_at' => $now,
        ]);
        DB::table('grimba_post_translations')->insert([
            'post_id' => $postId,
            'locale' => 'en',
            'translated_name' => 'atomicity translated ' . $suffix,
            'translated_description' => 'desc',
            'translated_content' => '<p>body</p>',
            'translation_driver' => 'fixture',
            'translated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $post = DB::table('posts')->where('id', $postId)->first();

        // Presenter must see rank 1 (translated) for EN target.
        $this->assertSame(1, GrimbaTranslationPresenter::rankForTargetLocale($post, 'en'));

        // The in-row and join rows must hold the same translated_name.
        $joinName = DB::table('grimba_post_translations')
            ->where('post_id', $postId)->where('locale', 'en')
            ->value('translated_name');
        $this->assertSame($post->translated_name, $joinName);
    }

    public function test_join_only_translation_still_satisfies_presenter(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('grimba_post_translations')) {
            $this->markTestSkipped('grimba_post_translations table not yet migrated.');
        }

        $suffix = Str::lower(Str::random(8));
        $now = now();
        $postId = $this->insertFixturePost('join only ' . $suffix, 'fr', $now);

        // Drop only the join row — in-row stays NULL. Presenter must
        // still treat the post as translated to EN.
        DB::table('grimba_post_translations')->insert([
            'post_id' => $postId,
            'locale' => 'en',
            'translated_name' => 'join only ' . $suffix,
            'translated_description' => 'desc',
            'translated_content' => '<p>body</p>',
            'translation_driver' => 'fixture',
            'translated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $post = DB::table('posts')->where('id', $postId)->first();
        $this->assertSame(1, GrimbaTranslationPresenter::rankForTargetLocale($post, 'en'));
    }

    public function test_empty_translated_name_does_not_count_as_translated(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('grimba_post_translations')) {
            $this->markTestSkipped('grimba_post_translations table not yet migrated.');
        }

        $suffix = Str::lower(Str::random(8));
        $now = now();
        $postId = $this->insertFixturePost('empty translated ' . $suffix, 'fr', $now);

        // Half-rolled-back state: in-row has translated_to='en' but
        // translated_name is empty. Presenter must NOT credit this as
        // a real translation.
        DB::table('posts')->where('id', $postId)->update([
            'translated_name' => '',
            'translated_to' => 'en',
            'translated_at' => $now,
        ]);

        $post = DB::table('posts')->where('id', $postId)->first();
        // Origin=fr, target=en, no translation → rank 2 (labeled
        // wrong-locale, untranslated). Must NOT be rank 1.
        $this->assertSame(2, GrimbaTranslationPresenter::rankForTargetLocale($post, 'en'));
    }

    public function test_unique_locale_index_prevents_duplicate_translations(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('grimba_post_translations')) {
            $this->markTestSkipped('grimba_post_translations table not yet migrated.');
        }

        $suffix = Str::lower(Str::random(8));
        $now = now();
        $postId = $this->insertFixturePost('dup test ' . $suffix, 'fr', $now);

        DB::table('grimba_post_translations')->insert([
            'post_id' => $postId,
            'locale' => 'en',
            'translated_name' => 'first ' . $suffix,
            'translation_driver' => 'fixture',
            'translated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $exception = null;
        try {
            DB::table('grimba_post_translations')->insert([
                'post_id' => $postId,
                'locale' => 'en', // same locale as above
                'translated_name' => 'second ' . $suffix,
                'translation_driver' => 'fixture',
                'translated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (\Throwable $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'Expected the unique (post_id, locale) index to reject the second insert.');

        $count = DB::table('grimba_post_translations')
            ->where('post_id', $postId)->where('locale', 'en')
            ->count();
        $this->assertSame(1, $count, 'Only one translation row should exist per (post_id, locale).');
    }

    private function insertFixturePost(string $name, string $language, mixed $createdAt): int
    {
        return (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'Atomicity fixture.',
            'content' => '<p>Atomicity fixture.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'views' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Atomicity Fixture',
            'original_language' => $language,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
