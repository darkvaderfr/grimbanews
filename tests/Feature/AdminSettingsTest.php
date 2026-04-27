<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    private function admin(): User
    {
        $user = User::query()->find(1);

        $this->assertNotNull($user, 'Fixture database must contain the system admin user.');

        return $user;
    }

    private function settingValue(string $key): ?string
    {
        return DB::table('settings')->where('key', $key)->value('value');
    }

    public function test_grimba_admin_settings_pages_render_and_save_through_setting_store(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/grimba/translation')
            ->assertOk()
            ->assertSee('NobuAI Provider Vault')
            ->assertSee('OpenAI')
            ->assertSee('OpenRouter')
            ->assertSee('Anthropic')
            ->assertSee('xAI / Grok');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/news-sources/triage')
            ->assertOk()
            ->assertSee('Classification queue')
            ->assertSee('Sources à classer');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/coverage-map')
            ->assertOk()
            ->assertSee('Coverage map')
            ->assertSee('Carte de couverture');

        $this->actingAs($this->admin())
            ->post('/admin/grimba/translation', [
                'driver' => 'openai',
                'openai_key' => 'sk-test-grimba-openai',
                'openai_model' => 'gpt-test',
                'ingest_auto_publish' => '1',
            ])
            ->assertRedirect('/admin/grimba/translation');

        $this->assertSame('sk-test-grimba-openai', $this->settingValue('grimba_translator_openai_key'));
        $this->assertSame('openai', $this->settingValue('grimba_translator_driver'));
        $this->assertSame('gpt-test', $this->settingValue('grimba_translator_openai_model'));
        $this->assertSame('1', $this->settingValue('grimba_ingest_auto_publish'));

        $this->actingAs($this->admin())
            ->post('/admin/grimba/newsapi', [
                'key' => 'newsapi-test-key',
                'queries' => "france\nclimat",
                'language' => 'fr',
                'countries' => 'fr,us',
                'active' => '1',
                'window' => '72',
            ])
            ->assertRedirect('/admin/grimba/newsapi');

        $this->assertSame('newsapi-test-key', $this->settingValue('grimba_newsapi_key'));
        $this->assertSame("france\nclimat", $this->settingValue('grimba_newsapi_queries'));
        $this->assertSame('fr', $this->settingValue('grimba_newsapi_language'));
        $this->assertSame('fr,us', $this->settingValue('grimba_newsapi_countries'));
        $this->assertSame('1', $this->settingValue('grimba_newsapi_active'));
        $this->assertSame('72', $this->settingValue('grimba_newsapi_everything_window_hours'));

        $this->actingAs($this->admin())
            ->post('/admin/grimba/cookies', [
                'active' => '1',
                'title' => 'Cookie test',
                'body' => 'Consent body',
                'accept_label' => 'Accept',
                'reject_label' => 'Reject',
                'more_label' => 'Details',
                'more_url' => '/privacy-test',
            ])
            ->assertRedirect('/admin/grimba/cookies');

        $this->assertSame('1', $this->settingValue('grimba_cookie_active'));
        $this->assertSame('Cookie test', $this->settingValue('grimba_cookie_title'));
        $this->assertSame('Consent body', $this->settingValue('grimba_cookie_body'));
        $this->assertSame('Accept', $this->settingValue('grimba_cookie_accept_label'));
        $this->assertSame('Reject', $this->settingValue('grimba_cookie_reject_label'));
        $this->assertSame('Details', $this->settingValue('grimba_cookie_more_label'));
        $this->assertSame('/privacy-test', $this->settingValue('grimba_cookie_more_url'));
    }
}
