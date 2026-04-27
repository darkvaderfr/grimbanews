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
        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_openai_key'],
            ['value' => 'sk-test-admin-diagnostics', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_nobuai_failure_openai'],
            [
                'value' => json_encode([
                    'driver' => 'openai',
                    'message' => 'quota test failure',
                    'at' => now()->subMinute()->toDateTimeString(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->actingAs($this->admin())
            ->get('/admin/grimba/translation')
            ->assertOk()
            ->assertSee('NobuAI Provider Vault')
            ->assertSee('Provider diagnostics')
            ->assertSee('Dernier échec')
            ->assertSee('quota test failure')
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

        $rssSource = DB::table('news_sources')->whereNotNull('name')->orderBy('id')->first(['id', 'name']);
        $this->assertNotNull($rssSource, 'Fixture database must contain at least one news source.');

        $rssDraftIds = DB::table('posts')
            ->where('status', 'published')
            ->orderByDesc('id')
            ->limit(2)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount(2, $rssDraftIds, 'Fixture database must contain at least two posts for RSS draft guardrails.');

        DB::table('rss_feed_items')
            ->whereIn('guid', ['s231-blocked-draft', 's231-ready-draft'])
            ->delete();

        DB::table('posts')->where('id', $rssDraftIds[0])->update([
            'status' => 'draft',
            'source_id' => null,
            'source_name' => null,
            'bias_rating' => 'unknown',
            'original_language' => 'en',
            'translated_name' => null,
            'description' => 'Short.',
            'updated_at' => now(),
        ]);

        DB::table('posts')->where('id', $rssDraftIds[1])->update([
            'status' => 'draft',
            'source_id' => $rssSource->id,
            'source_name' => $rssSource->name,
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'translated_name' => null,
            'description' => 'Ce brouillon contient un extrait suffisamment long pour valider les garde-fous éditoriaux avant publication RSS.',
            'updated_at' => now(),
        ]);

        DB::table('rss_feed_items')->insert([
            [
                'feed_id' => 990001,
                'guid' => 's231-blocked-draft',
                'link' => 'https://example.test/s231-blocked',
                'title_snapshot' => 'S231 blocked draft',
                'post_id' => $rssDraftIds[0],
                'seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'feed_id' => 990001,
                'guid' => 's231-ready-draft',
                'link' => 'https://example.test/s231-ready',
                'title_snapshot' => 'S231 ready draft',
                'post_id' => $rssDraftIds[1],
                'seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/grimba/rss-drafts?bias=unknown')
            ->assertOk()
            ->assertSee('grimba-admin-actions', false)
            ->assertSee('Blockers RSS')
            ->assertSee('source manquante')
            ->assertSee('biais inconnu')
            ->assertSee('traduction manquante')
            ->assertSee('extrait trop court')
            ->assertSee('garde-fous')
            ->assertSee(route('grimba.news-sources.triage'), false)
            ->assertSee(route('grimba.translation.index'), false)
            ->assertSee(route('posts.edit', $rssDraftIds[0]), false);

        $this->actingAs($this->admin())
            ->get('/admin/grimba/rss-drafts?source=' . $rssSource->id . '&bias=center')
            ->assertOk()
            ->assertSee('Prêt à publier')
            ->assertSee('garde-fous');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/rss-feeds')
            ->assertOk()
            ->assertSee('grimba-admin-actions', false)
            ->assertSee('RSS control tower')
            ->assertSee('Flux RSS');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/subscribers')
            ->assertOk()
            ->assertSee('grimba-admin-actions', false)
            ->assertSee('Audience command')
            ->assertSee('Abonnés infolettre');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/subscribers?q=s238-empty-state-fixture')
            ->assertOk()
            ->assertSee('grimba-admin-empty', false)
            ->assertSee('Aucun abonné pour ces filtres');

        $this->actingAs($this->admin())
            ->post('/admin/grimba/rss-drafts/publish', ['ids' => $rssDraftIds])
            ->assertRedirect()
            ->assertSessionHas('success_msg');

        $this->assertSame('draft', DB::table('posts')->where('id', $rssDraftIds[0])->value('status'));
        $this->assertSame('published', DB::table('posts')->where('id', $rssDraftIds[1])->value('status'));

        $sourceName = 'S134 Bias Score Test Source';
        DB::table('news_sources')
            ->where('name', $sourceName)
            ->orWhere('slug', 's134-bias-score-test-source')
            ->delete();

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
            ->assertSee('grimba-admin-actions', false)
            ->assertSee('Insights dossiers')
            ->assertSee('Dernier insight')
            ->assertSee('Operations board')
            ->assertSee('RSS 24h')
            ->assertSee('NewsAPI 24h')
            ->assertSee('Pending translations')
            ->assertSee('Duplicate groups')
            ->assertSee('stale')
            ->assertSee('NobuAI health')
            ->assertSee('Dernières erreurs NobuAI')
            ->assertSee('quota test failure')
            ->assertSee('Draft blockers')
            ->assertSee(route('grimba.news-sources.triage'), false)
            ->assertSee(route('grimba.translation.index'), false)
            ->assertSee('Poll 1 RSS')
            ->assertSee('Fetch NewsAPI')
            ->assertSee('Translate 3 FR');

        $this->actingAs($this->admin())
            ->post('/admin/grimba/cockpit/runbook', [
                'action' => 'health',
                'limit' => 99,
            ])
            ->assertRedirect('/admin/grimba/cockpit')
            ->assertSessionHas('success_msg');

        $diagnosticClusterId = 990016;
        $diagnosticSourceName = 'S228 Low Cred Test Source';
        $diagnosticSourceSlug = 's228-low-cred-test-source';

        $staleDiagnosticSourceIds = DB::table('news_sources')
            ->where('name', $diagnosticSourceName)
            ->orWhere('slug', $diagnosticSourceSlug)
            ->pluck('id')
            ->all();

        if ($staleDiagnosticSourceIds !== []) {
            DB::table('posts')
                ->whereIn('source_id', $staleDiagnosticSourceIds)
                ->update(['source_id' => null, 'source_name' => null]);
        }

        DB::table('news_sources')
            ->where('name', $diagnosticSourceName)
            ->orWhere('slug', $diagnosticSourceSlug)
            ->delete();

        $diagnosticSourceId = DB::table('news_sources')->insertGetId([
            'name' => $diagnosticSourceName,
            'website' => 'https://lowcred.example',
            'bias_rating' => 'right',
            'ownership_type' => 'corporate',
            'credibility_score' => 42,
            'country' => 'US',
            'language' => 'en',
            'notes' => 'S228 admin drilldown fixture',
            'slug' => $diagnosticSourceSlug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('story_clusters')->updateOrInsert(
            ['id' => $diagnosticClusterId],
            [
                'topic' => 'Admin source drilldown diagnostics',
                'description' => 'S228 regression fixture',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $diagnosticPostIds = DB::table('posts')
            ->where('status', 'published')
            ->orderBy('id')
            ->limit(2)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount(2, $diagnosticPostIds, 'Fixture database must contain at least two published posts.');

        DB::table('posts')->where('id', $diagnosticPostIds[0])->update([
            'story_cluster_id' => $diagnosticClusterId,
            'source_id' => null,
            'source_name' => null,
            'bias_rating' => 'unknown',
            'description' => 'Article sans métadonnées source pour vérifier le diagnostic admin.',
            'summary_nobuai' => "Ce qui est confirmé: Ancien insight à rafraîchir.",
            'summary_generated_at' => now()->subDay(),
            'summary_driver' => 'openai',
            'updated_at' => now(),
        ]);

        DB::table('posts')->where('id', $diagnosticPostIds[1])->update([
            'story_cluster_id' => $diagnosticClusterId,
            'source_id' => $diagnosticSourceId,
            'source_name' => $diagnosticSourceName,
            'bias_rating' => 'right',
            'description' => 'Article avec crédibilité basse pour vérifier le signal éditorial.',
            'summary_nobuai' => "Ce qui est confirmé: Ancien insight à rafraîchir.",
            'summary_generated_at' => now()->subDay(),
            'summary_driver' => 'openai',
            'updated_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->get("/admin/grimba/story-clusters/{$diagnosticClusterId}/edit")
            ->assertOk()
            ->assertSee('NobuAI insights')
            ->assertSee('insight NobuAI')
            ->assertSee('Diagnostic sources')
            ->assertSee('Métadonnées source manquantes')
            ->assertSee('Biais inconnu')
            ->assertSee('Crédibilité basse')
            ->assertSee($diagnosticSourceName)
            ->assertSee('Modifier l')
            ->assertSee('Insight stale');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/cockpit')
            ->assertOk()
            ->assertSee('Rafraîchir 3 stale');

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

        $newsApiDraftIds = DB::table('posts')
            ->where('status', 'published')
            ->orderByDesc('id')
            ->limit(2)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount(2, $newsApiDraftIds, 'Fixture database must contain at least two posts for NewsAPI draft guardrails.');

        DB::table('newsapi_items')
            ->whereIn('article_url_hash', ['s232-blocked-hash', 's232-ready-hash'])
            ->delete();

        DB::table('posts')->where('id', $newsApiDraftIds[0])->update([
            'status' => 'draft',
            'source_id' => null,
            'source_name' => null,
            'bias_rating' => 'unknown',
            'original_language' => 'en',
            'translated_name' => null,
            'description' => 'Tiny.',
            'updated_at' => now(),
        ]);

        DB::table('posts')->where('id', $newsApiDraftIds[1])->update([
            'status' => 'draft',
            'source_id' => $rssSource->id,
            'source_name' => $rssSource->name,
            'bias_rating' => 'center',
            'original_language' => 'fr',
            'translated_name' => null,
            'description' => 'Ce brouillon NewsAPI contient un extrait suffisamment long pour valider les garde-fous avant publication.',
            'updated_at' => now(),
        ]);

        DB::table('newsapi_items')->insert([
            [
                'source_id' => null,
                'api_source_id' => null,
                'article_url' => 'https://example.test/s232-blocked',
                'article_url_hash' => 's232-blocked-hash',
                'post_id' => $newsApiDraftIds[0],
                'published_at' => now(),
                'fetched_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'source_id' => $rssSource->id,
                'api_source_id' => 's232-ready',
                'article_url' => 'https://example.test/s232-ready',
                'article_url_hash' => 's232-ready-hash',
                'post_id' => $newsApiDraftIds[1],
                'published_at' => now(),
                'fetched_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/grimba/newsapi')
            ->assertOk()
            ->assertSee('grimba-admin-actions', false)
            ->assertSee('NewsAPI draft readiness')
            ->assertSee('Blockers NewsAPI')
            ->assertSee('source manquante')
            ->assertSee('biais inconnu')
            ->assertSee('traduction manquante')
            ->assertSee('extrait trop court')
            ->assertSee('Prêt à publier')
            ->assertSee(route('grimba.news-sources.triage'), false)
            ->assertSee(route('grimba.translation.index'), false)
            ->assertSee(route('posts.edit', $newsApiDraftIds[0]), false);

        $this->actingAs($this->admin())
            ->post('/admin/grimba/newsapi/publish-drafts', ['ids' => $newsApiDraftIds])
            ->assertRedirect()
            ->assertSessionHas('success_msg');

        $this->assertSame('draft', DB::table('posts')->where('id', $newsApiDraftIds[0])->value('status'));
        $this->assertSame('published', DB::table('posts')->where('id', $newsApiDraftIds[1])->value('status'));

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
