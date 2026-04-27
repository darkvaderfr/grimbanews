<?php

namespace Tests\Feature;

use App\Services\GrimbaTranslator;
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
}
