<?php

namespace Tests\Feature;

use Botble\Blog\Models\Post;
use Botble\Member\Models\Member;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                    'source_id' => null,
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

    private function createSource(string $name, string $country, string $bias = 'center'): int
    {
        return (int) DB::table('news_sources')->insertGetId([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
            'website' => 'https://example.com/' . Str::slug($name),
            'bias_rating' => $bias,
            'bias_score' => match ($bias) {
                'left' => -2,
                'right' => 2,
                default => 0,
            },
            'ownership_type' => 'private',
            'credibility_score' => 85,
            'country' => $country,
            'language' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function setting(string $key, string $value): void
    {
        $store = app(SettingStore::class);
        $store->set($key, $value);
        $store->save();
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
        $ids = $this->publishedPostIds(2, 13);
        $post = $this->assignCluster($ids, 910006, ['left', 'center']);
        $generatedAt = \Carbon\Carbon::parse(DB::table('posts')->whereIn('id', $ids)->max('updated_at'))->addMinute();

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
                'summary_generated_at' => $generatedAt,
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

    public function test_story_marks_nobuai_insights_stale_when_new_coverage_arrives(): void
    {
        $ids = $this->publishedPostIds(2, 19);
        $post = $this->assignCluster($ids, 910008, ['left', 'right']);

        DB::table('posts')
            ->where('story_cluster_id', 910008)
            ->update([
                'summary_nobuai' => 'Ce qui est confirmé: le dossier dispose déjà d’un résumé.',
                'summary_generated_at' => now()->subDay(),
                'summary_driver' => 'openai',
                'updated_at' => now(),
            ]);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('Insights par NobuAI')
            ->assertSee('NobuAI à rafraîchir')
            ->assertSee('une nouvelle couverture est arrivée après ce résumé');
    }

    public function test_anonymous_reader_can_read_extracted_full_article_by_default(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(2, 22), 910009, ['left', 'center']);

        DB::table('posts')->where('id', $post->id)->update([
            'full_content' => '<p>Texte intégral visible publiquement avec une enquête complète sur les données publiques. ' . str_repeat('Le lecteur peut rester dans GrimbaNews pour lire les détails vérifiés. ', 4) . '</p>',
            'full_fetched_at' => now(),
            'full_extract_error' => null,
        ]);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee("Lire l'article complet")
            ->assertSee('Texte intégral')
            ->assertSee('grimba-full-article--reader', false)
            ->assertSee('Texte intégral visible publiquement avec une enquête complète')
            ->assertDontSee('Réservé aux abonnés')
            ->assertDontSee('grimba-full-article--locked', false);
    }

    public function test_story_page_shows_readable_feed_body_when_full_extraction_is_blocked(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(2, 24), 910010, ['left', 'center']);
        $fallbackText = 'Extrait RSS lisible affiché dans le dossier quand l’éditeur bloque l’extraction. '
            . str_repeat('Cette phrase donne au lecteur un contexte utile sans quitter GrimbaNews. ', 4);

        DB::table('posts')->where('id', $post->id)->update([
            'content' => '<p><a href="https://example.test/fallback-story">Lire l’article original</a></p><p>' . $fallbackText . '</p>',
            'full_content' => null,
            'full_fetched_at' => now(),
            'full_extract_error' => 'http 403',
        ]);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('Extrait disponible')
            ->assertSee("Lire l'extrait disponible")
            ->assertSee('grimba-full-article--reader', false)
            ->assertSee('Extrait RSS lisible affiché dans le dossier')
            ->assertDontSee('Lire l’article original</a></p><p>Extrait RSS', false);
    }

    public function test_logged_in_member_can_read_extracted_full_article(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(2, 26), 910011, ['left', 'center']);
        $member = Member::query()->first();

        $this->assertNotNull($member, 'Fixture database must contain at least one member account.');

        DB::table('posts')->where('id', $post->id)->update([
            'full_content' => '<p>Texte intégral visible par un membre connecté avec les détails complets. ' . str_repeat('La version membre conserve le texte extrait dans l’interface de lecture. ', 4) . '</p>',
            'full_fetched_at' => now(),
            'full_extract_error' => null,
        ]);

        $this->actingAs($member, 'member')
            ->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee("Lire l'article complet")
            ->assertSee('Texte intégral')
            ->assertSee('grimba-full-article--reader', false)
            ->assertSee('Texte intégral visible par un membre connecté')
            ->assertDontSee('Réservé aux abonnés')
            ->assertDontSee('<details class="grimba-full-article', false);
    }

    public function test_full_article_gate_can_still_be_enabled_by_setting(): void
    {
        $this->setting('grimba_full_article_public', '');
        $post = $this->assignCluster($this->publishedPostIds(2, 28), 910012, ['left', 'center']);

        DB::table('posts')->where('id', $post->id)->update([
            'full_content' => '<p>Texte intégral verrouillable avec les détails complets. ' . str_repeat('La barrière d’accès ne doit pas exposer le contenu aux lecteurs anonymes. ', 4) . '</p>',
            'full_fetched_at' => now(),
            'full_extract_error' => null,
        ]);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('Réservé aux abonnés')
            ->assertSee('grimba-full-article--locked', false)
            ->assertDontSee('Texte intégral verrouillable avec les détails complets');

        $this->setting('grimba_full_article_public', '1');
    }

    public function test_article_list_shows_full_cluster_across_region_scope_and_categories(): void
    {
        $ids = $this->publishedPostIds(2, 30);
        $post = $this->assignCluster($ids, 910013, ['left', 'center']);
        $sourceA = $this->createSource('Region Scoped Wire A ' . Str::random(6), 'US', 'left');
        $sourceB = $this->createSource('Region Scoped Wire B ' . Str::random(6), 'US', 'center');
        $categoryId = (int) DB::table('categories')->where('name', 'Monde')->value('id');
        $internalCategoryId = (int) DB::table('categories')->where('name', 'Trusted Source Credibility')->value('id');

        $this->assertGreaterThan(0, $categoryId, 'Fixture category must exist.');
        if ($internalCategoryId <= 0) {
            $internalCategoryId = (int) DB::table('categories')->insertGetId([
                'name' => 'Trusted Source Credibility',
                'parent_id' => 0,
                'description' => 'Internal review bucket fixture.',
                'status' => 'published',
                'author_id' => 1,
                'author_type' => \Botble\ACL\Models\User::class,
                'order' => 16,
                'is_featured' => 0,
                'is_default' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($ids as $index => $id) {
            DB::table('posts')->where('id', $id)->update([
                'source_id' => $index === 0 ? $sourceA : $sourceB,
                'source_name' => $index === 0 ? 'Region Scoped Wire A' : 'Region Scoped Wire B',
                'bias_rating' => $index === 0 ? 'left' : 'center',
                'description' => $index === 0
                    ? 'Primary region-scope fixture description.'
                    : 'Sibling region-scope fixture description.',
                'original_language' => 'fr',
                'translated_to' => null,
                'translated_name' => null,
                'translated_description' => null,
            ]);

            DB::table('post_categories')->insertOrIgnore([
                ['post_id' => $id, 'category_id' => $categoryId],
                ['post_id' => $id, 'category_id' => $internalCategoryId],
            ]);
        }

        $this->withUnencryptedCookies($this->readerCookies(['grimba_region' => 'africa']))
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('class="grimba-story container', false)
            ->assertSee('<span style="opacity:0.55;">2</span>', false)
            ->assertSee('articles')
            ->assertSee('Region Scoped Wire A')
            ->assertSee('Region Scoped Wire B')
            ->assertSee('Monde')
            ->assertDontSee('Trusted Source Credibility')
            ->assertSee('Sibling region-scope fixture description.');
    }

    public function test_newsapi_truncation_marker_is_scrubbed_from_article_body(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(1, 32), 910014, ['center']);

        DB::table('posts')->where('id', $post->id)->update([
            'description' => 'Prominent Jewish American leader and Israel defender Abraham Abe Foxman has died at age 86. [+4285 chars]',
            'content' => '<p>Prominent Jewish American leader and Israel defender Abraham Abe Foxman has died at age 86. The Anti-Defamation League confirmed his death on Sunday, calling… [+4285 chars]</p>',
        ]);

        $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('Prominent Jewish American leader and Israel defender')
            ->assertDontSee('[+4285 chars]')
            ->assertDontSee('4285 chars');
    }
}
