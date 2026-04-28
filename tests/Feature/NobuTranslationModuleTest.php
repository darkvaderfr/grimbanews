<?php

namespace Tests\Feature;

use App\Services\GrimbaTranslator;
use App\Support\GrimbaTranslationPresenter;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\NobuTranslation\Support\NobuTranslator;
use Tests\TestCase;

class NobuTranslationModuleTest extends TestCase
{
    public function test_nobu_translation_module_powers_grimba_translation(): void
    {
        Cache::flush();

        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_openai_key'],
            ['value' => 'sk-test-openai', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_driver'],
            ['value' => 'openai', 'created_at' => now(), 'updated_at' => now()]
        );

        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => 'Bonjour le monde.',
                    ],
                ]],
            ]),
        ]);

        /** @var NobuTranslator $nobuTranslator */
        $nobuTranslator = app(NobuTranslator::class);
        $this->assertSame('nobuai', $nobuTranslator->health()['driver']);
        $this->assertSame('Bonjour le monde.', $nobuTranslator->translate('Hello world.', 'fr', 'en'));

        /** @var GrimbaTranslator $grimbaTranslator */
        $grimbaTranslator = app(GrimbaTranslator::class);
        $result = $grimbaTranslator->translate('Hello world.', 'en', 'fr');

        $this->assertSame('Bonjour le monde.', $result['text'] ?? null);
        $this->assertSame('nobutranslation:nobuai', $result['driver'] ?? null);
    }

    public function test_grimba_translation_supports_french_to_english(): void
    {
        Cache::flush();

        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_openai_key'],
            ['value' => 'sk-test-openai', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_driver'],
            ['value' => 'openai', 'created_at' => now(), 'updated_at' => now()]
        );

        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => 'The climate agreement was adopted.',
                    ],
                ]],
            ]),
        ]);

        /** @var NobuTranslator $nobuTranslator */
        $nobuTranslator = app(NobuTranslator::class);
        $this->assertSame(
            'The climate agreement was adopted.',
            $nobuTranslator->translate('L’accord climat a été adopté.', 'en', 'fr')
        );

        /** @var GrimbaTranslator $grimbaTranslator */
        $grimbaTranslator = app(GrimbaTranslator::class);
        $result = $grimbaTranslator->translate('L’accord climat a été adopté.', 'fr', 'en');

        $this->assertSame('The climate agreement was adopted.', $result['text'] ?? null);
        $this->assertSame('nobutranslation:nobuai', $result['driver'] ?? null);
    }

    public function test_pending_translation_command_stores_per_locale_english_translation(): void
    {
        Cache::flush();

        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_openai_key'],
            ['value' => 'sk-test-openai', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_driver'],
            ['value' => 'openai', 'created_at' => now(), 'updated_at' => now()]
        );

        $postId = DB::table('posts')->insertGetId([
            'name' => 'Le budget est adopte',
            'description' => 'Le parlement adopte un nouveau budget apres un long debat.',
            'content' => '<p>Le parlement adopte un nouveau budget apres un long debat.</p>',
            'status' => 'published',
            'author_type' => 'Botble\\ACL\\Models\\User',
            'author_id' => 1,
            'is_featured' => 0,
            'views' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Fixture France',
            'original_language' => 'fr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => 'The budget is adopted',
                    ],
                ]],
            ]),
        ]);

        $this->artisan('grimba:translate-pending', [
            '--to' => 'en',
            '--limit' => 1,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('grimba_post_translations', [
            'post_id' => $postId,
            'locale' => 'en',
            'translated_name' => 'The budget is adopted',
        ]);

        $post = DB::table('posts')->where('id', $postId)->first();

        $this->withUnencryptedCookies(['grimba_lang' => 'en']);
        app()->setLocale('en');

        $this->assertTrue(GrimbaTranslationPresenter::isTranslated($post, 'en'));
        $this->assertSame('The budget is adopted', GrimbaTranslationPresenter::title($post));
    }

    public function test_locale_priority_prefers_native_then_translated_then_unknown_then_untranslated(): void
    {
        $now = now();
        $suffix = Str::lower(Str::random(8));

        $nativeId = $this->translationFixturePostId(
            'language priority native english ' . $suffix,
            'en',
            $now->copy()->subHours(4)
        );

        $translatedId = $this->translationFixturePostId(
            'priorite langue francaise traduite ' . $suffix,
            'fr',
            $now->copy()->subHour()
        );

        $unknownId = $this->translationFixturePostId(
            'language priority unknown source ' . $suffix,
            '',
            $now->copy()->subMinutes(10)
        );

        $untranslatedId = $this->translationFixturePostId(
            'priorite langue francaise brute ' . $suffix,
            'fr',
            $now
        );

        DB::table('grimba_post_translations')->insert([
            'post_id' => $translatedId,
            'locale' => 'en',
            'translated_name' => 'translated french story ' . $suffix,
            'translated_description' => 'Translated description.',
            'translated_content' => '<p>Translated content.</p>',
            'translation_driver' => 'nobuai:test',
            'translated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $posts = DB::table('posts')
            ->whereIn('id', [$nativeId, $translatedId, $unknownId, $untranslatedId])
            ->get()
            ->keyBy('id');

        $this->assertSame(0, GrimbaTranslationPresenter::rankForTargetLocale($posts[$nativeId], 'en'));
        $this->assertSame(1, GrimbaTranslationPresenter::rankForTargetLocale($posts[$translatedId], 'en'));
        $this->assertSame(2, GrimbaTranslationPresenter::rankForTargetLocale($posts[$unknownId], 'en'));
        $this->assertSame(3, GrimbaTranslationPresenter::rankForTargetLocale($posts[$untranslatedId], 'en'));

        $orderedIds = DB::table('posts')
            ->whereIn('id', [$nativeId, $translatedId, $unknownId, $untranslatedId])
            ->tap(fn ($query) => GrimbaTranslationPresenter::orderForTargetLocale($query, 'en'))
            ->pluck('id')
            ->all();

        $this->assertSame([$nativeId, $translatedId, $unknownId, $untranslatedId], $orderedIds);
    }

    public function test_failed_pending_translations_are_recorded_and_retryable_in_admin(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        Cache::flush();

        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_openai_key'],
            ['value' => 'sk-test-failure', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_driver'],
            ['value' => 'openai', 'created_at' => now(), 'updated_at' => now()]
        );

        $postId = $this->translationFixturePostId(
            'language failure retry fixture ' . Str::lower(Str::random(8)),
            'en',
            now()
        );

        Http::fake([
            '*' => Http::response(['error' => 'upstream rejected fixture request'], 500),
        ]);

        $this->artisan('grimba:translate-pending', [
            '--to' => 'fr',
            '--limit' => 1,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('grimba_translation_failures', [
            'post_id' => $postId,
            'locale' => 'fr',
            'attempts' => 1,
        ]);

        $this->artisan('grimba:translate-pending', [
            '--to' => 'fr',
            '--limit' => 1,
            '--failed-only' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('grimba_translation_failures', [
            'post_id' => $postId,
            'locale' => 'fr',
            'attempts' => 2,
        ]);

        $failure = DB::table('grimba_translation_failures')
            ->where('post_id', $postId)
            ->where('locale', 'fr')
            ->first();

        $this->assertNotNull($failure);
        $this->assertStringContainsString('openai', (string) $failure->driver_chain);
        $this->assertStringContainsString('googletx', (string) $failure->error_message);
    }

    private function translationFixturePostId(string $name, string $language, mixed $createdAt): int
    {
        return (int) DB::table('posts')->insertGetId([
            'name' => $name,
            'description' => 'Language priority fixture.',
            'content' => '<p>Language priority fixture.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'views' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Language Priority Fixture',
            'original_language' => $language,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
