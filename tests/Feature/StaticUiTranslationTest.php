<?php

namespace Tests\Feature;

use App\Ground\Regions;
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

    public function test_g4_reader_locale_targets_have_saved_catalog_keys(): void
    {
        $files = [
            base_path('platform/themes/echo/layouts/grimba-home.blade.php'),
            base_path('platform/themes/echo/layouts/grimba-chrome.blade.php'),
            base_path('platform/themes/echo/views/coffre.blade.php'),
            base_path('platform/themes/echo/views/for-you.blade.php'),
            base_path('platform/themes/echo/views/local.blade.php'),
            base_path('platform/themes/echo/partials/blog/post/partials/items/grid.blade.php'),
            base_path('platform/themes/echo/partials/bias-badge.blade.php'),
            base_path('platform/themes/echo/partials/save-button.blade.php'),
            base_path('platform/themes/echo/partials/reading-time.blade.php'),
            base_path('platform/themes/echo/partials/home/vault-script.blade.php'),
            base_path('platform/themes/echo/partials/home/coverage-bar.blade.php'),
            base_path('platform/themes/echo/partials/story/bias-distribution.blade.php'),
        ];

        $keys = [];
        foreach ($files as $file) {
            $this->assertFileExists($file);
            $contents = (string) file_get_contents($file);

            preg_match_all('/__\(\s*([\'"])(.*?)(?<!\\\\)\1/s', $contents, $matches);
            foreach ($matches[2] as $key) {
                $keys[stripslashes($key)] = true;
            }

            preg_match_all('/trans_choice\(\s*([\'"])(.*?)(?<!\\\\)\1/s', $contents, $choiceMatches);
            foreach ($choiceMatches[2] as $key) {
                $keys[stripslashes($key)] = true;
            }
        }

        $catalog = json_decode((string) file_get_contents(lang_path('en.json')), true, 512, JSON_THROW_ON_ERROR);

        foreach (array_keys($keys) as $key) {
            $this->assertArrayHasKey($key, $catalog, lang_path('en.json') . ' is missing ' . $key);
        }
    }

    public function test_english_vault_for_you_and_local_shells_use_saved_catalog_copy(): void
    {
        $cookies = [
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ];

        $this->withUnencryptedCookies($cookies)
            ->get('/coffre')
            ->assertOk()
            ->assertSee('Skip to main content')
            ->assertSee('No saved articles yet')
            ->assertSee('Browse the news')
            ->assertDontSee("Aucun article sauvegardé pour l'instant");

        $this->withUnencryptedCookies($cookies)
            ->get('/pour-vous')
            ->assertOk()
            ->assertSee('For You')
            ->assertSee('Follow topics to build your feed')
            ->assertSee('Your selection stays local to your browser')
            ->assertDontSee('Suivez des sujets pour construire votre fil');

        $this->withUnencryptedCookies($cookies + [
            'grimba_local_city' => 'Paris',
            'grimba_local_country' => 'France',
            'grimba_local_cc' => 'FR',
        ])
            ->get('/local')
            ->assertOk()
            ->assertSee('Country (ISO)')
            ->assertSee('Update')
            ->assertSee('recent stories')
            ->assertSee('cross-checked sources')
            ->assertDontSee('histoires récentes')
            ->assertDontSee('sources croisées');
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
            'Source drilldown',
            'Qui soutient quel angle ?',
            'Chaque ligne relie un camp, une source et le passage qui justifie le cadrage.',
            'Voir tous les articles',
            'Voir dans le dossier',
            'Propriété',
            'Lire cette source',
            'Source inconnue',
            'NobuAI à rafraîchir : une nouvelle couverture est arrivée après ce résumé.',
            'Réservé aux abonnés',
            'Texte intégral',
            'Connectez-vous pour lire le texte intégral extrait par GrimbaNews.',
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

    public function test_full_article_reader_styles_are_present(): void
    {
        $css = file_get_contents(dirname(__DIR__, 2) . '/public/themes/echo/css/grimba-home.css');

        $this->assertStringContainsString('.grimba-full-article--reader', $css);
        $this->assertStringContainsString('.grimba-full-article__body', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-full-article', $css);
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

        $country = DB::table('posts')
            ->leftJoin('news_sources', 'news_sources.id', '=', 'posts.source_id')
            ->where('posts.id', $postId)
            ->value('news_sources.country');

        $region = 'international';
        foreach (['africa', 'europe', 'americas'] as $candidate) {
            if (in_array($country, Regions::countries($candidate) ?? [], true)) {
                $region = $candidate;
                break;
            }
        }

        $path = '/article/' . $slug->key;

        $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
            'grimba_region' => $region,
        ])
            ->get($path)
            ->assertOk()
            ->assertSee('Story')
            ->assertSee('coverage items')
            ->assertSee('Updated')
            ->assertSee('Filter dossier articles')
            ->assertSee('Bias comparison')
            ->assertSee('Bias distribution')
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
