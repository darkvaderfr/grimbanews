<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaticUiTranslationTest extends TestCase
{
    public function test_english_reader_shell_uses_saved_catalog_copy(): void
    {
        $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])
            ->get('/')
            ->assertOk()
            ->assertSee('Today&#039;s briefing', false)
            ->assertSee('Top news stories')
            ->assertSee('Reader')
            ->assertSee('All media outlets')
            ->assertSee('French articles are shown in English when a NobuAI translation is available.');
    }

    public function test_saved_translation_catalogs_are_valid_json(): void
    {
        foreach ([
            lang_path('en.json'),
            lang_path('fr.json'),
            base_path('platform/themes/echo/lang/en.json'),
            base_path('platform/themes/echo/lang/fr.json'),
        ] as $path) {
            $this->assertFileExists($path);
            $this->assertIsArray(json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR));
        }
    }

    public function test_english_story_shell_uses_saved_catalog_copy(): void
    {
        $postId = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('story_cluster_id')
            ->orderBy('id')
            ->value('id');

        $this->assertNotNull($postId, 'Fixture database must contain a clustered published post.');

        $slug = DB::table('slugs')
            ->where('reference_type', 'Botble\\Blog\\Models\\Post')
            ->where('reference_id', $postId)
            ->first(['key', 'prefix']);

        $this->assertNotNull($slug, 'Fixture clustered post must have a slug.');

        $path = '/' . trim(($slug->prefix ? $slug->prefix . '/' : '') . $slug->key, '/');

        $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])
            ->get($path)
            ->assertOk()
            ->assertSee('Story')
            ->assertSee('coverage items')
            ->assertSee('Updated')
            ->assertSee('Filter dossier articles')
            ->assertSee('Coverage details')
            ->assertSee('Read the full article');
    }

    public function test_english_search_and_source_shells_use_saved_catalog_copy(): void
    {
        $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])
            ->get('/search?q=fixture-with-no-results&bias=left')
            ->assertOk()
            ->assertSee('Search')
            ->assertSee('All sources')
            ->assertSee('All bias')
            ->assertSee('Reset filters', false);

        $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])
            ->get('/sources')
            ->assertOk()
            ->assertSee('Ranked sources')
            ->assertSee('All countries')
            ->assertSee('Ownership map');

        $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])
            ->get('/proprietaires')
            ->assertOk()
            ->assertSee('Media ownership')
            ->assertSee('Who owns what');
    }
}
