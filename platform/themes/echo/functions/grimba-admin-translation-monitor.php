<?php

/*
 * S-LSAT-19 (Vader 2026-05-18) — rule-engine observability dash.
 *
 * Operators tuning the 500-view auto-translate via Wave DDD's
 * `/admin/grimba/translation-rules` form previously had zero
 * visibility into what the engine actually DID. This page surfaces:
 *
 *   - Today's call burn vs daily cap (from the cache counter).
 *   - Recent rule decisions (rolling 100-entry log via cache).
 *   - Pending priority-2 (editorial pin) queue depth.
 *   - Pending priority-1 (rule-fired) queue depth.
 *   - Recent translations across the wider GrimbaTranslator chain
 *     (so the operator can see if rule-driven AND manual flows
 *     are working).
 *
 * Closes the operator loop on Vader's 500-view directive:
 *   capture → form → cron → DB → dashboard.
 */

use App\Console\Commands\GrimbaTranslateByRule;
use App\Support\GrimbaLanguageSettings;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('translation-monitor', function () {
            $cap = GrimbaLanguageSettings::ruleEngineDailyCap();
            $callsToday = GrimbaTranslateByRule::callsToday();
            $decisions = GrimbaTranslateByRule::recentDecisions(50);

            // S-LSAT-19b — provider visibility. Operators need to
            // know which translator drivers are wired so a silent
            // "no provider available" failure mode isn't invisible.
            $providerStatus = ['enabled' => false, 'configured' => []];
            try {
                $translator = app(\App\Services\GrimbaTranslator::class);
                $providerStatus['enabled'] = $translator->enabled();
                $providerStatus['configured'] = $translator->configuredDrivers();
            } catch (\Throwable $e) {
                // Service container couldn't resolve — admin still
                // gets a "no providers" tile rather than a 500.
            }

            $pinnedQueue = 0;
            $ruleQueue = 0;
            $recentlyTranslated = collect();
            if (Schema::hasColumn('posts', 'translation_priority')) {
                $pinnedQueue = (int) DB::table('posts')
                    ->where('status', 'published')
                    ->where('translation_priority', '>=', 2)
                    ->whereNull('translated_to')
                    ->count();
                $ruleQueue = (int) DB::table('posts')
                    ->where('status', 'published')
                    ->where('translation_priority', '=', 1)
                    ->whereNull('translated_to')
                    ->count();
            }
            if (Schema::hasColumn('posts', 'translated_at')) {
                $recentlyTranslated = DB::table('posts')
                    ->where('status', 'published')
                    ->whereNotNull('translated_to')
                    ->whereNotNull('translated_at')
                    ->where('translated_at', '>=', now()->subDay())
                    ->orderByDesc('translated_at')
                    ->limit(20)
                    ->get(['id', 'name', 'translated_name', 'original_language', 'translated_to', 'translated_at', 'translation_driver']);
            }

            $enabled = GrimbaLanguageSettings::ruleEngineEnabled();

            return view('grimba-admin.translation-monitor.index', compact(
                'cap',
                'callsToday',
                'decisions',
                'pinnedQueue',
                'ruleQueue',
                'recentlyTranslated',
                'enabled',
                'providerStatus',
            ));
        })->name('translation-monitor.index');

        Route::post('translation-monitor/clear-decisions', function (Request $request) {
            GrimbaTranslateByRule::clearDecisions();
            \Illuminate\Support\Facades\Log::info('[GrimbaTranslationMonitor] decisions log cleared', [
                'user_id' => $request->user()?->id,
            ]);
            return back()->with('success_msg', __('Journal des décisions vidé.'));
        })->name('translation-monitor.clear');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-translation-monitor')
                ->priority(52)
                ->parentId('grimba-root')
                ->name('Moniteur traduction')
                ->icon('ti ti-activity')
                ->route('grimba.translation-monitor.index')
        );
    });
});
