<?php

/*
 * S-CAT-03 (Vader 2026-05-18) — home-rail category pin admin.
 *
 * Operators can force section blocks on the home page to ALWAYS
 * surface a specific topic category (e.g. "always show Politique
 * in section block 1, Économie in section block 2"). When pin is
 * empty, the rail falls back to the auto-pick behavior shipped in
 * Wave Y (most-posted topic categories with non-zero counts).
 *
 * Settings keys:
 *   - grimba_section_pin_1   (string, topic category name OR empty)
 *   - grimba_section_pin_2   (string)
 *
 * The pinned slots are filled first; remaining slots get
 * auto-picked categories (excluding any pinned ones to avoid
 * duplicates).
 */

use App\Support\GrimbaEditorialCategories;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('home-rails', function () {
            $topicChoices = GrimbaEditorialCategories::topicNames(includeFront: false);
            $current = [
                1 => (string) setting('grimba_section_pin_1', ''),
                2 => (string) setting('grimba_section_pin_2', ''),
            ];
            $resolved = GrimbaEditorialCategories::sectionTopics(2);
            $pinned = GrimbaEditorialCategories::pinnedSectionCategories(2);

            return view('grimba-admin.home-rails.index', compact(
                'topicChoices',
                'current',
                'resolved',
                'pinned',
            ));
        })->name('home-rails.index');

        Route::post('home-rails', function (Request $request) {
            $data = $request->validate([
                'grimba_section_pin_1' => ['nullable', 'string', 'max:120'],
                'grimba_section_pin_2' => ['nullable', 'string', 'max:120'],
            ]);

            $valid = GrimbaEditorialCategories::topicNames(includeFront: false);
            foreach ([1, 2] as $slot) {
                $key = 'grimba_section_pin_' . $slot;
                $val = trim((string) ($data[$key] ?? ''));
                if ($val !== '' && ! in_array($val, $valid, true)) {
                    // Unknown category name — clear instead of poisoning the setting.
                    $val = '';
                }
                setting()->set($key, $val);
            }
            setting()->save();

            // Flush the home-feed cache so the next page render
            // picks up the new pin order immediately.
            try {
                \App\Support\GrimbaHomeFeed::flush();
            } catch (\Throwable $e) {
                // Cache flush failure is non-fatal — the feed
                // refreshes within its TTL anyway.
            }

            \Illuminate\Support\Facades\Log::info('[GrimbaHomeRails] admin update', [
                'user_id' => $request->user()?->id,
                'pin_1' => setting('grimba_section_pin_1'),
                'pin_2' => setting('grimba_section_pin_2'),
            ]);

            return back()->with('success_msg', __('Pins de sections enregistrés. Cache home vidé.'));
        })->name('home-rails.save');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-home-rails')
                ->priority(53)
                ->parentId('grimba-root')
                ->name('Rails de la home')
                ->icon('ti ti-layout-rows')
                ->route('grimba.home-rails.index')
        );
    });
});
