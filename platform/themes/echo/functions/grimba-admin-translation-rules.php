<?php

/*
 * S-LSAT-14 / S-LSAT-15 (Vader 2026-05-18) — admin form for the
 * `grimba_lang_*` settings driving the language-surfacing engine
 * and the rule-driven auto-translator.
 *
 * Vader's directive: "We should also have in the admin dashboard
 * article translation conditions that can be updated."
 *
 * Routes:
 *   GET  /admin/grimba/translation-rules   → form
 *   POST /admin/grimba/translation-rules   → save handler
 *                                            + GrimbaLanguageSettings::flush()
 *                                            + audit log line
 *
 * The form mutates 13 settings keys (matching `GrimbaLanguageSettings::defaults()`).
 * Validation lives on the save handler so a typo'd integer doesn't
 * silently poison the rule engine.
 */

use App\Console\Commands\GrimbaTranslateByRule;
use App\Support\GrimbaLanguageSettings;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('translation-rules', function (Request $request) {
            $defaults = GrimbaLanguageSettings::defaults();
            $current = GrimbaLanguageSettings::all();

            $callsToday = GrimbaTranslateByRule::callsToday();
            $cap = $current['rule_engine_daily_cap'] ?? $defaults['rule_engine_daily_cap'];

            return view('grimba-admin.translation-rules.index', [
                'defaults' => $defaults,
                'current' => $current,
                'callsToday' => $callsToday,
                'dailyCap' => $cap,
            ]);
        })->name('translation-rules.index');

        Route::post('translation-rules', function (Request $request) {
            $data = $request->validate([
                'strict_surface' => ['nullable', 'in:0,1'],
                'strict_home' => ['nullable', 'in:0,1'],
                'strict_breaking' => ['nullable', 'in:0,1'],
                'strict_latest' => ['nullable', 'in:0,1'],
                'strict_dossiers' => ['nullable', 'in:0,1'],
                'strict_category' => ['nullable', 'in:0,1'],
                'strict_search' => ['nullable', 'in:0,1'],
                'rule_engine_enabled' => ['nullable', 'in:0,1'],
                'tail_expander_enabled' => ['nullable', 'in:0,1'],
                'popularity_threshold' => ['nullable', 'integer', 'min:10', 'max:100000'],
                'popularity_threshold_africa' => ['nullable', 'integer', 'min:10', 'max:100000'],
                'rule_engine_daily_cap' => ['nullable', 'integer', 'min:1', 'max:100000'],
                'region_force_both' => ['nullable', 'string', 'max:256'],
            ]);

            // Persist via Botble's setting store. Booleans go through
            // the unchecked-checkbox dance: HTML form omits the key
            // when the box is unchecked, so we default-fill explicitly.
            $boolKeys = [
                'strict_surface', 'strict_home', 'strict_breaking', 'strict_latest',
                'strict_dossiers', 'strict_category', 'strict_search',
                'rule_engine_enabled', 'tail_expander_enabled',
            ];
            foreach ($boolKeys as $k) {
                $value = $request->input($k);
                setting()->set('grimba_lang_' . $k, $value === '1' ? '1' : '0');
            }
            foreach (['popularity_threshold', 'popularity_threshold_africa', 'rule_engine_daily_cap'] as $k) {
                if (array_key_exists($k, $data) && $data[$k] !== null && $data[$k] !== '') {
                    setting()->set('grimba_lang_' . $k, (string) $data[$k]);
                }
            }
            if (array_key_exists('region_force_both', $data) && $data['region_force_both'] !== null) {
                setting()->set('grimba_lang_region_force_both', trim($data['region_force_both']));
            }
            setting()->save();

            // Critical: flush the cached reader so the change takes
            // effect immediately. Without this, the 5-min TTL window
            // would leave operators wondering if the form actually
            // saved.
            GrimbaLanguageSettings::flush();

            // Audit-log breadcrumb so we know who changed what.
            Log::info('[GrimbaTranslationRules] admin update', [
                'user_id' => $request->user()?->id,
                'payload' => array_filter($data, fn ($v) => $v !== null && $v !== ''),
            ]);

            return back()->with('success_msg', __("Conditions de traduction mises à jour. Le cache a été vidé — l'effet est immédiat."));
        })->name('translation-rules.save');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-translation-rules')
                ->priority(50)
                ->parentId('grimba-root')
                ->name('Règles de traduction')
                ->icon('ti ti-language')
                ->route('grimba.translation-rules.index')
        );
    });
});
