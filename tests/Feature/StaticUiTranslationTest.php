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

    public function test_core_public_chrome_keys_are_saved_in_catalogs(): void
    {
        $keys = [
            'Tous les dossiers en cours — chaque histoire vue sous plusieurs angles.',
            'Comparaison des sources',
            'Comparez comment les médias couvrent la même histoire.',
            'Recherche : :query',
            'Explorez les articles, sources et dossiers de GrimbaNews.',
            'Comment GrimbaNews classe les biais, repère les angles morts et note la crédibilité des sources.',
            'Carte de la concentration des médias suivis par GrimbaNews.',
            'Biais, propriété, crédibilité et origine des sources suivies.',
            ':source — biais déclaré :bias. Couverture archivée par GrimbaNews.',
            "Les histoires qu'un seul camp couvre",
            "Un angle mort est une histoire importante rapportée presque exclusivement par un côté du spectre politique. GrimbaNews les signale pour que vous sachiez ce qu'on ne vous raconte pas.",
            'Aucun angle mort identifié pour le moment.',
            'Histoire liée',
            'Dossier',
            'Même histoire, plusieurs angles. Comparez comment chaque média couvre le sujet — et repérez les silences.',
            "Aucune source n'a été trouvée pour ce dossier.",
            '404 — Page introuvable',
            'Erreur 404',
            'Cette page a disparu du radar.',
            "Le lien que vous avez suivi n'existe plus, a été déplacé, ou n'a jamais existé. Ça arrive. Voici par où repartir.",
            "Retour à l'accueil",
            'Voir le fil',
            '500 — Erreur interne',
            'Erreur 500',
            "Quelque chose s'est cassé côté serveur.",
            "Une erreur interne nous empêche de servir cette page pour l'instant. L'équipe a été notifiée. Réessayez dans quelques minutes, ou revenez à la liste des dernières histoires.",
            '503 — Service indisponible',
            'Maintenance',
            'GrimbaNews est en maintenance.',
            'Nous améliorons la plateforme. Nous revenons très vite. Merci pour votre patience.',
            'Généré par NobuAI :time.',
            'Généré par NobuAI.',
        ];

        foreach ([
            lang_path('en.json'),
            lang_path('fr.json'),
            base_path('platform/themes/echo/lang/en.json'),
            base_path('platform/themes/echo/lang/fr.json'),
        ] as $path) {
            $catalog = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $catalog, $path . ' is missing ' . $key);
            }
        }
    }

    public function test_english_story_shell_uses_saved_catalog_copy(): void
    {
        $clusterId = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('story_cluster_id')
            ->select('story_cluster_id')
            ->groupBy('story_cluster_id')
            ->havingRaw('COUNT(*) >= 2')
            ->orderBy('story_cluster_id')
            ->value('story_cluster_id');

        $this->assertNotNull($clusterId, 'Fixture database must contain a multi-post clustered published story.');

        $postId = DB::table('posts')
            ->where('status', 'published')
            ->where('story_cluster_id', $clusterId)
            ->orderBy('id')
            ->value('id');

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

    public function test_english_blindspot_shell_uses_saved_catalog_copy(): void
    {
        $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])
            ->get('/angles-morts')
            ->assertOk()
            ->assertSee('Blindspot')
            ->assertSee('Stories only one side covers')
            ->assertSee('reported almost exclusively by one side');
    }
}
