<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class NobuAiSummaryCommandTest extends TestCase
{
    public function test_nobuai_summary_command_persists_provider_output_to_cluster_posts(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $clusterId = 990010;
        $postIds = DB::table('posts')
            ->where('status', 'published')
            ->orderBy('id')
            ->limit(2)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount(2, $postIds, 'Fixture database must contain at least two published posts.');

        DB::table('story_clusters')->updateOrInsert(
            ['id' => $clusterId],
            [
                'topic' => 'NobuAI summary test',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ($postIds as $index => $postId) {
            DB::table('posts')->where('id', $postId)->update([
                'story_cluster_id' => $clusterId,
                'bias_rating' => $index === 0 ? 'left' : 'right',
                'description' => $index === 0
                    ? 'Le premier article insiste sur les risques budgétaires et cite les syndicats.'
                    : 'Le second article met en avant la réponse gouvernementale et les contraintes économiques.',
                'summary_nobuai' => null,
                'summary_generated_at' => null,
                'summary_driver' => null,
            ]);
        }

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
                        'content' => "Ce qui est confirmé: Les sources décrivent deux lectures concurrentes du dossier.\nAngle mort: La couverture manque encore une voix centriste indépendante.\nPourquoi ça compte: Le lecteur peut distinguer le fait commun des cadrages politiques.",
                    ],
                ]],
            ]),
        ]);

        $this->artisan('grimba:nobuai-summaries', [
            '--cluster' => $clusterId,
            '--limit' => 1,
        ])->assertExitCode(0);

        foreach ($postIds as $postId) {
            $row = DB::table('posts')->where('id', $postId)->first([
                'summary_nobuai',
                'summary_generated_at',
                'summary_driver',
            ]);

            $this->assertStringContainsString('lectures concurrentes', $row->summary_nobuai);
            $this->assertStringContainsString('Angle mort:', $row->summary_nobuai);
            $this->assertStringContainsString('voix centriste', $row->summary_nobuai);
            $this->assertNotNull($row->summary_generated_at);
            $this->assertSame('openai', $row->summary_driver);
        }
    }

    public function test_nobuai_summary_command_can_refresh_only_stale_insights(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $postIds = DB::table('posts')
            ->where('status', 'published')
            ->orderBy('id')
            ->limit(6)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount(6, $postIds, 'Fixture database must contain at least six published posts.');

        $clusters = [
            'stale' => ['id' => 990018, 'posts' => array_slice($postIds, 0, 2)],
            'fresh' => ['id' => 990019, 'posts' => array_slice($postIds, 2, 2)],
            'pending' => ['id' => 990020, 'posts' => array_slice($postIds, 4, 2)],
        ];

        foreach ($clusters as $name => $cluster) {
            DB::table('story_clusters')->updateOrInsert(
                ['id' => $cluster['id']],
                [
                    'topic' => 'NobuAI stale refresh ' . $name,
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach ($clusters['stale']['posts'] as $postId) {
            DB::table('posts')->where('id', $postId)->update([
                'story_cluster_id' => $clusters['stale']['id'],
                'bias_rating' => 'left',
                'description' => 'Article actualisé après un ancien insight.',
                'summary_nobuai' => 'Old stale summary',
                'summary_generated_at' => now()->subDay(),
                'summary_driver' => 'openai',
                'updated_at' => now(),
            ]);
        }

        foreach ($clusters['fresh']['posts'] as $postId) {
            DB::table('posts')->where('id', $postId)->update([
                'story_cluster_id' => $clusters['fresh']['id'],
                'bias_rating' => 'center',
                'description' => 'Article déjà couvert par un insight récent.',
                'summary_nobuai' => 'Fresh summary should remain',
                'summary_generated_at' => now()->addHour(),
                'summary_driver' => 'openai',
                'updated_at' => now(),
            ]);
        }

        foreach ($clusters['pending']['posts'] as $postId) {
            DB::table('posts')->where('id', $postId)->update([
                'story_cluster_id' => $clusters['pending']['id'],
                'bias_rating' => 'right',
                'description' => 'Article sans insight initial.',
                'summary_nobuai' => null,
                'summary_generated_at' => null,
                'summary_driver' => null,
                'updated_at' => now(),
            ]);
        }

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
                        'content' => "Ce qui est confirmé: Stale insight refreshed only.\nPourquoi ça compte: Le cockpit peut cibler les dossiers obsolètes.",
                    ],
                ]],
            ]),
        ]);

        $this->artisan('grimba:nobuai-summaries', [
            '--stale' => true,
            '--limit' => 10,
        ])->assertExitCode(0);

        $staleSummary = DB::table('posts')->where('id', $clusters['stale']['posts'][0])->value('summary_nobuai');
        $freshSummary = DB::table('posts')->where('id', $clusters['fresh']['posts'][0])->value('summary_nobuai');
        $pendingSummary = DB::table('posts')->where('id', $clusters['pending']['posts'][0])->value('summary_nobuai');

        $this->assertStringContainsString('Stale insight refreshed only', $staleSummary);
        $this->assertSame('Fresh summary should remain', $freshSummary);
        $this->assertNull($pendingSummary);
    }

    public function test_nobuai_health_reports_story_insight_readiness(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);

        $clusterId = 990012;
        $postIds = DB::table('posts')
            ->where('status', 'published')
            ->orderByDesc('id')
            ->limit(2)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount(2, $postIds, 'Fixture database must contain at least two published posts.');

        DB::table('story_clusters')->updateOrInsert(
            ['id' => $clusterId],
            [
                'topic' => 'NobuAI health readiness test',
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ($postIds as $postId) {
            DB::table('posts')->where('id', $postId)->update([
                'story_cluster_id' => $clusterId,
                'summary_nobuai' => "Ce qui est confirmé: Le dossier est prêt pour NobuAI.",
                'summary_generated_at' => now(),
                'summary_driver' => 'openai',
            ]);
        }

        DB::table('settings')->updateOrInsert(
            ['key' => 'grimba_translator_openai_key'],
            ['value' => 'sk-test-openai', 'created_at' => now(), 'updated_at' => now()]
        );

        Artisan::call('grimba:nobuai-health');
        $output = Artisan::output();

        $this->assertStringContainsString('NobuAI wrapper', $output);
        $this->assertStringContainsString('LLM providers: openai', $output);
        $this->assertStringContainsString('Story insights:', $output);
        $this->assertStringContainsString('ready', $output);
        $this->assertStringContainsString('pending', $output);
    }

}
