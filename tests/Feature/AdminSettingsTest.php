<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
            ->assertSee('xAI / Grok')
            ->assertSee('LLM insight providers')
            ->assertSee('Dedicated translation fallbacks')
            ->assertSee('Enregistrer les clés NobuAI');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/news-sources/triage')
            ->assertOk()
            ->assertSee('Classification queue')
            ->assertSee('Sources à classer')
            ->assertSee('Score');

        $sourceName = 'S134 Bias Score Test Source';
        DB::table('news_sources')->where('name', $sourceName)->delete();

        $this->actingAs($this->admin())
            ->get('/admin/grimba/news-sources/create')
            ->assertOk()
            ->assertSee('Score biais');

        $this->actingAs($this->admin())
            ->post('/admin/grimba/news-sources', [
                'name' => $sourceName,
                'website' => 'example.test',
                'bias_rating' => 'left',
                'bias_score' => '-1.7',
                'ownership_type' => 'independent',
                'credibility_score' => '82',
                'country' => 'FR',
                'language' => 'fr',
                'notes' => 'S134 regression fixture',
            ])
            ->assertRedirect('/admin/grimba/news-sources');

        $createdSource = DB::table('news_sources')->where('name', $sourceName)->first();
        $this->assertNotNull($createdSource);
        $this->assertSame('-1.7', number_format((float) $createdSource->bias_score, 1));

        $publicSource = DB::table('news_sources')->whereNotNull('slug')->where('slug', '!=', '')->first();
        $this->assertNotNull($publicSource, 'Fixture database must contain at least one public source slug.');
        DB::table('news_sources')->where('id', $publicSource->id)->update(['bias_score' => -1.6]);

        $this->get('/sources/' . $publicSource->slug)
            ->assertOk()
            ->assertSee('score biais')
            ->assertSee('-1.6');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/coverage-map')
            ->assertOk()
            ->assertSee('Coverage map')
            ->assertSee('Carte de couverture');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/cockpit')
            ->assertOk()
            ->assertSee('Insights dossiers')
            ->assertSee('Dernier insight')
            ->assertSee('Operations board')
            ->assertSee('RSS 24h')
            ->assertSee('NewsAPI 24h')
            ->assertSee('Pending translations')
            ->assertSee('Duplicate groups');

        $clusterId = DB::table('story_clusters')->orderBy('id')->value('id');
        $this->assertNotNull($clusterId, 'Fixture database must contain at least one story cluster.');

        $this->actingAs($this->admin())
            ->get("/admin/grimba/story-clusters/{$clusterId}/edit")
            ->assertOk()
            ->assertSee('NobuAI insights')
            ->assertSee('insight NobuAI');

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

        $batchClusterId = 990014;
        $batchPostIds = DB::table('posts')
            ->where('status', 'published')
            ->orderBy('id')
            ->limit(2)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount(2, $batchPostIds, 'Fixture database must contain at least two published posts.');

        DB::table('story_clusters')->updateOrInsert(
            ['id' => $batchClusterId],
            [
                'topic' => 'Cockpit NobuAI batch test',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ($batchPostIds as $index => $postId) {
            DB::table('posts')->where('id', $postId)->update([
                'story_cluster_id' => $batchClusterId,
                'bias_rating' => $index === 0 ? 'left' : 'right',
                'description' => $index === 0
                    ? 'Une source insiste sur le risque social et le calendrier parlementaire.'
                    : 'Une autre source insiste sur la réponse exécutive et les arbitrages budgétaires.',
                'summary_nobuai' => null,
                'summary_generated_at' => null,
                'summary_driver' => null,
                'updated_at' => now()->addMinute(),
            ]);
        }

        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "Ce qui est confirmé: Les articles décrivent un même dossier depuis deux cadrages.\nAngle mort: Le dossier manque une source classée au centre.\nPourquoi ça compte: NobuAI aide à distinguer consensus et cadrage.",
                    ],
                ]],
            ]),
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/grimba/cockpit/nobuai-summaries', ['limit' => 12])
            ->assertRedirect('/admin/grimba/cockpit')
            ->assertSessionHas('success_msg');

        foreach ($batchPostIds as $postId) {
            $row = DB::table('posts')->where('id', $postId)->first([
                'summary_nobuai',
                'summary_generated_at',
                'summary_driver',
            ]);

            $this->assertStringContainsString('deux cadrages', $row->summary_nobuai);
            $this->assertNotNull($row->summary_generated_at);
            $this->assertSame('openai', $row->summary_driver);
        }

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
