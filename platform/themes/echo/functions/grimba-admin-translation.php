<?php

/*
 * GrimbaNews — admin settings page for the S84 translation provider
 * chain.
 *
 * Routes under /admin/grimba/translation :
 *   GET   /            → form (one password field per provider + driver pin)
 *   POST  /            → save settings (uses Botble's setting(...)->save())
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
            $translator   = app(GrimbaTranslator::class);
            $nobuAi       = app(GrimbaNobuAi::class);
            $nobuConfigured = $nobuAi->configuredDrivers();

            return view('grimba-admin.translation.index', compact(
                'drivers', 'settings', 'pinned', 'models', 'modelDrivers', 'translator', 'nobuConfigured', 'autoPublish'
            ));
        })->name('translation.index');

        Route::post('translation', function (Request $request) {
            $drivers = ['deepl', 'mistral', 'openrouter', 'openai', 'anthropic', 'google', 'xai', 'perplexity', 'groq', 'libre'];
            $modelDrivers = ['mistral', 'openrouter', 'openai', 'anthropic', 'google', 'xai', 'perplexity', 'groq'];

            /** @var SettingStore $store */
            $store = app('core.setting');

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
            $sample    = (string) $request->input('sample', 'The quick brown fox jumps over the lazy dog.');

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
                $out = $m->invoke($translator, $pinDriver, $sample, 'en', 'fr');
                $ms = (int) round((microtime(true) - $start) * 1000);
                return back()->with('success_msg', $out
                    ? sprintf('Test %s OK (%dms) : %s', $pinDriver, $ms, \Illuminate\Support\Str::limit($out, 120))
                    : sprintf('Test %s : aucune réponse (clé absente ou erreur amont).', $pinDriver)
                );
            }

            $start = microtime(true);
            $res = $translator->translate($sample, 'en', 'fr');
            $ms = (int) round((microtime(true) - $start) * 1000);
            if ($res) {
                return back()->with('success_msg', sprintf(
                    'Test auto OK (%dms) via %s : %s',
                    $ms, $res['driver'], \Illuminate\Support\Str::limit($res['text'], 120)
                ));
            }
            return back()->with('success_msg', 'Test auto : aucun fournisseur configuré ou tous en échec.');
        })->name('translation.test');

        Route::post('translation/nobuai-test', function (Request $request) {
            $prompt = trim((string) $request->input('prompt', 'Return exactly OK.'));
            if ($prompt === '') {
                $prompt = 'Return exactly OK.';
            }

            /** @var GrimbaNobuAi $nobuAi */
            $nobuAi = app(GrimbaNobuAi::class);
            if ($nobuAi->configuredDrivers() === []) {
                return back()->with('success_msg', 'NobuAI : aucune clé LLM configurée. Ajoutez OpenAI, OpenRouter, Anthropic, xAI, Mistral, Gemini, Perplexity ou Groq.');
            }

            $start = microtime(true);
            $res = $nobuAi->complete($prompt);
            $ms = (int) round((microtime(true) - $start) * 1000);

            if ($res) {
                return back()->with('success_msg', sprintf(
                    'NobuAI OK (%dms) via %s : %s',
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
