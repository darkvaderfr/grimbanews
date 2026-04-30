<?php

/*
 * GrimbaNews — admin settings page for the S84 translation provider
 * chain.
 *
 * Routes under /admin/grimba/translation :
 *   GET   /            → form (one password field per provider + driver pin)
 *   POST  /            → save settings through Botble's SettingStore
 *   POST  /test        → round-trip "Hello world" through the chain and
 *                        report which driver won + the translation
 *
 * Settings persisted (all scoped with grimba_translator_ prefix):
 *   grimba_translator_deepl_key
 *   grimba_translator_mistral_key
 *   grimba_translator_openrouter_key
 *   grimba_translator_openai_key      (falls back to ai_writer_openai_key)
 *   grimba_translator_anthropic_key
 *   grimba_translator_google_key
 *   grimba_translator_xai_key
 *   grimba_translator_perplexity_key
 *   grimba_translator_groq_key
 *   grimba_translator_libre_key
 *   grimba_translator_driver          (auto | <driver-name>)
 *   grimba_translator_<driver>_model  (optional model override for LLM providers)
 */

use App\Services\GrimbaNobuAi;
use App\Services\GrimbaTranslator;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('translation', function () {
            $drivers  = ['deepl', 'mistral', 'openrouter', 'openai', 'anthropic', 'google', 'xai', 'perplexity', 'groq', 'libre'];
            $modelDrivers = ['mistral', 'openrouter', 'openai', 'anthropic', 'google', 'xai', 'perplexity', 'groq'];
            $settings = [];
            foreach ($drivers as $d) {
                $settings[$d] = (string) setting('grimba_translator_' . $d . '_key', '');
            }
            $models = [];
            foreach ($modelDrivers as $d) {
                $models[$d] = (string) setting('grimba_translator_' . $d . '_model', '');
            }
            $pinned       = (string) setting('grimba_translator_driver', 'auto');
            $autoPublish  = (bool) setting('grimba_ingest_auto_publish', false);
            $nobuProfileDefaults = [
                'mission' => "Servir les publics africains et les intellectuels du continent et de la diaspora avec une lecture rigoureuse des rapports de pouvoir, de souveraineté et d'intérêt public.",
                'soul' => "NobuAI est l'éditeur en chef analytique de GrimbaNews: panafricain, anti-colonial dans sa grille de lecture, pluraliste dans les sources, sobre dans le ton, et strictement lié aux faits disponibles.",
                'capabilities' => "Identifier les conséquences pour l'Afrique, les angles morts des médias dominants, les intérêts institutionnels ou économiques, les continuités historiques, les effets sur les diasporas et les questions de souveraineté.",
                'anchors' => "Traditions panafricaines associées à Kwame Nkrumah, Patrice Lumumba, Nelson Mandela, Nathalie Yamb et d'autres penseurs ou acteurs de souveraineté africaine, sans imiter leur voix ni leur attribuer des propos.",
                'guardrails' => "Ne pas inventer de faits, ne pas appeler à soutenir un parti ou un candidat, distinguer clairement fait/analyse/incertitude, citer les limites des sources, et refuser les conclusions non étayées.",
            ];
            $nobuProfile = [];
            foreach ($nobuProfileDefaults as $key => $default) {
                $nobuProfile[$key] = (string) setting('grimba_nobuai_editorial_' . $key, $default);
            }
            $translator   = app(GrimbaTranslator::class);
            $nobuAi       = app(GrimbaNobuAi::class);
            $nobuConfigured = $nobuAi->configuredDrivers();
            $nobuFailures = $nobuAi->failureDiagnostics();
            $nobuSystemPreview = $nobuAi->editorialSystemPrompt(
                'Preview the active editor-in-chief profile for evidence-bound GrimbaNews story synthesis.'
            );
            $translationFailures = collect();
            $translationFailureStats = [
                'total' => 0,
                'locales' => [],
            ];

            if (Schema::hasTable('grimba_translation_failures')) {
                $translationFailures = DB::table('grimba_translation_failures as failures')
                    ->leftJoin('posts', 'posts.id', '=', 'failures.post_id')
                    ->orderByDesc('failures.failed_at')
                    ->limit(8)
                    ->get([
                        'failures.post_id',
                        'failures.locale',
                        'failures.source_language',
                        'failures.driver_chain',
                        'failures.error_message',
                        'failures.attempts',
                        'failures.failed_at',
                        'posts.name as post_name',
                    ]);

                $translationFailureStats = [
                    'total' => (int) DB::table('grimba_translation_failures')->count(),
                    'locales' => DB::table('grimba_translation_failures')
                        ->selectRaw('locale, count(*) as total')
                        ->groupBy('locale')
                        ->pluck('total', 'locale')
                        ->map(fn ($count) => (int) $count)
                        ->all(),
                ];
            }

            return view('grimba-admin.translation.index', compact(
                'drivers', 'settings', 'pinned', 'models', 'modelDrivers', 'translator', 'nobuConfigured', 'nobuFailures', 'translationFailures', 'translationFailureStats', 'autoPublish', 'nobuProfile', 'nobuSystemPreview'
            ));
        })->name('translation.index');

        Route::post('translation', function (Request $request) {
            $drivers = ['deepl', 'mistral', 'openrouter', 'openai', 'anthropic', 'google', 'xai', 'perplexity', 'groq', 'libre'];
            $modelDrivers = ['mistral', 'openrouter', 'openai', 'anthropic', 'google', 'xai', 'perplexity', 'groq'];

            /** @var SettingStore $store */
            $store = app(SettingStore::class);

            foreach ($drivers as $d) {
                $fieldName = $d . '_key';
                $new = (string) $request->input($fieldName, '');
                // Empty string means "leave current value alone". Explicit
                // "__clear__" sentinel clears the setting — give the user
                // a way to undo a key without typing it in.
                if ($new === '__clear__') {
                    $store->set('grimba_translator_' . $d . '_key', '');
                } elseif ($new !== '') {
                    $store->set('grimba_translator_' . $d . '_key', $new);
                }
            }

            $pinned = (string) $request->input('driver', 'auto');
            if (! in_array($pinned, array_merge(['auto'], $drivers), true)) {
                $pinned = 'auto';
            }
            $store->set('grimba_translator_driver', $pinned);

            foreach ($modelDrivers as $d) {
                $store->set('grimba_translator_' . $d . '_model', trim((string) $request->input($d . '_model', '')));
            }

            foreach (['mission', 'soul', 'capabilities', 'anchors', 'guardrails'] as $field) {
                $value = trim(strip_tags((string) $request->input('nobuai_' . $field, '')));
                $store->set('grimba_nobuai_editorial_' . $field, mb_substr($value, 0, 4000));
            }

            // S92: RSS auto-publish toggle. Checkbox semantics — absent
            // input means "off", so honour the submit explicitly.
            $store->set('grimba_ingest_auto_publish', $request->boolean('ingest_auto_publish') ? '1' : '');

            $store->save();

            return redirect()
                ->route('grimba.translation.index')
                ->with('success_msg', 'Paramètres de traduction mis à jour.');
        })->name('translation.save');

        Route::post('translation/test', function (Request $request) {
            $pinDriver = (string) $request->input('driver', '');
            $from      = strtolower(substr((string) $request->input('from', 'en'), 0, 2)) ?: 'en';
            $to        = strtolower(substr((string) $request->input('to', 'fr'), 0, 2)) ?: 'fr';
            $sample    = (string) $request->input('sample', $from === 'fr'
                ? 'Le renard brun rapide saute par-dessus le chien paresseux.'
                : 'The quick brown fox jumps over the lazy dog.');

            if (! in_array($from, ['en', 'fr'], true)) {
                $from = 'en';
            }
            if (! in_array($to, ['en', 'fr'], true) || $to === $from) {
                $to = $from === 'en' ? 'fr' : 'en';
            }

            /** @var GrimbaTranslator $translator */
            $translator = app(GrimbaTranslator::class);

            // Temporarily override the pinned driver for the test via env().
            // (Setting-backed driver is read via setting() so we can't
            // override it just for one request without writing to DB.)
            if ($pinDriver && $pinDriver !== 'auto') {
                // Direct dispatch via reflection — safer than mutating state.
                $r = new ReflectionClass($translator);
                $m = $r->getMethod('dispatch');
                $m->setAccessible(true);
                $start = microtime(true);
                $out = $m->invoke($translator, $pinDriver, $sample, $from, $to);
                $ms = (int) round((microtime(true) - $start) * 1000);
                return back()->with('success_msg', $out
                    ? sprintf('Test %s %s→%s OK (%dms) : %s', $pinDriver, strtoupper($from), strtoupper($to), $ms, \Illuminate\Support\Str::limit($out, 120))
                    : sprintf('Test %s : aucune réponse (clé absente ou erreur amont).', $pinDriver)
                );
            }

            $start = microtime(true);
            $res = $translator->translate($sample, $from, $to);
            $ms = (int) round((microtime(true) - $start) * 1000);
            if ($res) {
                return back()->with('success_msg', sprintf(
                    'Test auto %s→%s OK (%dms) via %s : %s',
                    strtoupper($from), strtoupper($to), $ms, $res['driver'], \Illuminate\Support\Str::limit($res['text'], 120)
                ));
            }
            return back()->with('success_msg', 'Test auto : aucun fournisseur configuré ou tous en échec.');
        })->name('translation.test');

        Route::post('translation/nobuai-test', function (Request $request) {
            $topic = trim(strip_tags((string) $request->input('topic', 'Dette africaine et financement climatique')));
            $sample = trim(strip_tags((string) $request->input('sample', 'Deux articles décrivent le même sommet: une source insiste sur les promesses de financement, une autre souligne les conditions imposées aux pays africains.')));
            if ($topic === '') {
                $topic = 'Dette africaine et financement climatique';
            }
            if ($sample === '') {
                $sample = 'Deux articles décrivent le même dossier depuis des cadrages différents.';
            }

            $prompt = implode("\n", [
                'Sujet: ' . mb_substr($topic, 0, 240),
                '',
                'Articles de test:',
                '- Source: Test Afrique | angle: center | résumé: ' . mb_substr($sample, 0, 900),
                '',
                'Tâche: Produis 3 lignes en français, format strict "Libellé: texte".',
                'Inclure une ligne "Perspective africaine" seulement si elle est supportée par le résumé fourni.',
                'Rester factuel, non partisan, et signaler les limites de preuve.',
            ]);

            /** @var GrimbaNobuAi $nobuAi */
            $nobuAi = app(GrimbaNobuAi::class);
            if ($nobuAi->configuredDrivers() === []) {
                return back()->with('success_msg', 'NobuAI : aucune clé LLM configurée. Ajoutez OpenAI, OpenRouter, Anthropic, xAI, Mistral, Gemini, Perplexity ou Groq.');
            }

            $start = microtime(true);
            $res = $nobuAi->complete(
                $prompt,
                $nobuAi->editorialSystemPrompt(
                    'Test the active editable editor-in-chief profile. Use only the supplied sample article facts.'
                )
            );
            $ms = (int) round((microtime(true) - $start) * 1000);

            if ($res) {
                return back()->with('success_msg', sprintf(
                    'NobuAI profile test OK (%dms) via %s : %s',
                    $ms, $res['driver'], \Illuminate\Support\Str::limit($res['text'], 120)
                ));
            }

            return back()->with('success_msg', sprintf('NobuAI : échec de tous les fournisseurs configurés (%dms).', $ms));
        })->name('translation.nobuai-test');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }
    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()
            ->registerItem(
                DashboardMenuItem::make()
                    ->id('grimba-translation')
                    ->priority(18)
                    ->parentId('grimba-root')
                    ->name('Traduction')
                    ->icon('ti ti-language')
                    ->route('grimba.translation.index')
            );
    });
});
