<?php

namespace Tests\Feature;

use App\Services\GrimbaTranslator;
use App\Support\GrimbaTranslationPresenter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
}
