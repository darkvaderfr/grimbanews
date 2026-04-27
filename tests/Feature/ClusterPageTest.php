<?php

namespace Tests\Feature;

use Botble\Blog\Models\Post;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClusterPageTest extends TestCase
{
    private function readerCookies(array $extra = []): array
    {
        return array_merge([
            'grimba_lang' => 'fr',
            'grimba_onboarded' => '1',
        ], $extra);
    }

    /**
     * @return array<int>
     */
    private function publishedPostIds(int $count, int $offset): array
    {
        $ids = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('name')
            ->orderBy('id')
            ->skip($offset)
            ->limit($count)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount($count, $ids, 'Fixture database must contain enough published posts.');

        return $ids;
    }

    /**
     * @param array<int> $ids
     * @param array<int, string> $biases
     */
    private function assignCluster(array $ids, int $clusterId, array $biases): Post
    {
        DB::table('posts')
            ->where('story_cluster_id', $clusterId)
            ->update(['story_cluster_id' => null]);

        foreach ($ids as $index => $id) {
            DB::table('posts')
                ->where('id', $id)
                ->update([
                    'story_cluster_id' => $clusterId,
                    'bias_rating' => $biases[$index] ?? 'unknown',
                    'summary_nobuai' => null,
                    'summary_generated_at' => null,
                    'summary_driver' => null,
                ]);
        }

        $post = Post::query()->find($ids[0]);
        $this->assertNotNull($post, 'Fixture post must still resolve through the Blog model.');

        return $post;
    }

    private function pathFor(Post $post): string
    {
        $path = parse_url($post->url, PHP_URL_PATH);

        $this->assertIsString($path);
        $this->assertNotSame('', $path);

        return $path;
    }

    public function test_cluster_size_one_uses_legacy_article_layout(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(1, 0), 910001, ['left']);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('grimba-orphan-hero', false)
            ->assertSee('Article')
            ->assertDontSee('class="grimba-story container', false)
            ->assertDontSee('Comparaison des biais');
    }

    public function test_cluster_size_two_or_more_uses_story_layout(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(2, 3), 910002, ['left', 'center']);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('class="grimba-story container', false)
            ->assertSee('Histoire')
            ->assertSee('2 couvertures')
            ->assertSee('Comparaison des biais');
    }

    public function test_one_sided_cluster_shows_coverage_gap_callout(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(2, 6), 910003, ['right', 'right']);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('class="grimba-story container', false)
            ->assertSee('Couverture déséquilibrée')
            ->assertSee('Voir cet angle mort');
    }

    public function test_multi_bias_cluster_does_not_show_coverage_gap_callout(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(3, 8), 910004, ['left', 'center', 'right']);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('class="grimba-story container', false)
            ->assertSee('Comparaison des biais')
            ->assertDontSee('Couverture déséquilibrée');
    }

    public function test_public_nobuai_insights_never_leak_provider_names(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(2, 11), 910005, ['left', 'right']);

        DB::table('posts')
            ->where('story_cluster_id', 910005)
            ->update([
                'summary_nobuai' => "Ce qui est confirmé: OpenAI confirme deux cadrages.\nPourquoi ça compte: Claude ne doit jamais apparaître côté lecteur.\nPourquoi ça compte: Claude ne doit jamais apparaître côté lecteur.",
                'summary_generated_at' => now(),
                'summary_driver' => 'openai',
            ]);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('Insights par NobuAI')
            ->assertSee('NobuAI confirme deux cadrages')
            ->assertSee('NobuAI ne doit jamais apparaître côté lecteur')
            ->assertDontSee('OpenAI')
            ->assertDontSee('Claude')
            ->assertDontSee('summary_driver');
    }

    public function test_public_nobuai_insights_show_groundnews_style_labels_and_note(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(2, 13), 910006, ['left', 'center']);

        DB::table('posts')
            ->where('story_cluster_id', 910006)
            ->update([
                'summary_nobuai' => implode("\n", [
                    'Ce qui est confirmé: deux sources décrivent le même calendrier.',
                    'Ce que dit la gauche: le cadrage insiste sur le coût social.',
                    'Ce que dit le centre: le cadrage insiste sur la procédure.',
                    'Angle mort: aucune source de droite publiée dans ce dossier.',
                    'Pourquoi ça compte: le lecteur voit consensus et lacunes séparément.',
                ]),
                'summary_generated_at' => now()->subMinutes(12),
                'summary_driver' => 'openai',
            ]);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('Insights par NobuAI')
            ->assertSee('Ce qui est confirmé')
            ->assertSee('Ce que dit la gauche')
            ->assertSee('Ce que dit le centre')
            ->assertSee('Angle mort')
            ->assertSee('Pourquoi ça compte')
            ->assertSee('Généré par NobuAI')
            ->assertDontSee('Première phrase de chaque source');
    }

    public function test_story_source_drilldown_links_sources_to_angles(): void
    {
        $ids = $this->publishedPostIds(3, 16);
        $post = $this->assignCluster($ids, 910007, ['left', 'center', 'right']);

        foreach ($ids as $index => $id) {
            DB::table('posts')
                ->where('id', $id)
                ->update([
                    'source_name' => ['Drill Left', 'Drill Center', 'Drill Right'][$index],
                    'description' => [
                        'La source de gauche soutient cet angle avec un détail social précis.',
                        'La source du centre soutient cet angle avec une chronologie procédurale.',
                        'La source de droite soutient cet angle avec une lecture budgétaire.',
                    ][$index],
                ]);
        }

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('Détail des sources')
            ->assertSee('Qui soutient quel angle ?')
            ->assertSee('Drill Left')
            ->assertSee('Drill Center')
            ->assertSee('Drill Right')
            ->assertSee('La source de gauche soutient cet angle')
            ->assertSee('Voir dans le dossier')
            ->assertSee('Lire cette source')
            ->assertSee('href="#story-article-' . $ids[0] . '"', false)
            ->assertDontSee('OpenAI')
            ->assertDontSee('Claude');
    }
}
