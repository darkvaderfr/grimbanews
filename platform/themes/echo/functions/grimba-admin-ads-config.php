<?php

/*
 * S-ADS-08 (Vader 2026-05-18) — ads configuration admin page.
 *
 * Surfaces the three operator-tunable axes of the ads stack:
 *
 *   1. Sales mailbox  (`grimba_advertiser_leads_sales_mailbox`)
 *      — recipient of the Wave OOO queued lead notifications.
 *   2. AdSense client ID  (`ads_google_adsense_unit_client_id`)
 *      — shared with stock Botble Ads; surfaced here for visibility.
 *   3. Per-slot Ad IDs  (`grimba_ads_slot_*` for 12 placements)
 *      — settings-first read in `GrimbaAds::slotId()`; env config
 *      stays as the fallback.
 *
 * Without this page, all three live in .env / config and require a
 * deploy to change. Now they update in real time.
 */

use App\Support\GrimbaAds;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('ads-config', function () {
            $slotKeys = [
                'grimba_home_top'         => 'Home top',
                'grimba_home_mid'         => 'Home middle',
                'grimba_home_native'      => 'Home native (in-feed)',
                'grimba_chrome_top'       => 'Page top (chrome)',
                'grimba_chrome_bottom'    => 'Page bottom (chrome)',
                'grimba_sources_top'      => 'Sources top',
                'grimba_sources_mid'      => 'Sources mid',
                'grimba_article_top'      => 'Article top',
                'grimba_article_mid'      => 'Article mid',
                'grimba_story_after_hero' => 'Dossier — after hero',
                'grimba_story_mid'        => 'Dossier — mid',
                'grimba_story_sidebar'    => 'Dossier — sidebar',
            ];

            return view('grimba-admin.ads-config.index', [
                'slotKeys' => $slotKeys,
                'salesMailbox' => (string) setting('grimba_advertiser_leads_sales_mailbox', ''),
                'clientId' => (string) setting('ads_google_adsense_unit_client_id', ''),
                'directUrl' => (string) setting('grimba_ads_direct_url', ''),
            ]);
        })->name('ads-config.index');

        Route::post('ads-config', function (Request $request) {
            $data = $request->validate([
                'grimba_advertiser_leads_sales_mailbox' => ['nullable', 'string', 'max:191'],
                'ads_google_adsense_unit_client_id' => ['nullable', 'regex:/^ca-pub-\d{16}$/', 'max:191'],
                // Direct URL accepts the `{placement}` substitution
                // placeholder, so we can't use Laravel's built-in
                // `url` rule (it rejects the curly braces). Custom
                // regex: must start with http(s):// and contain only
                // URL-safe chars + the `{placement}` token.
                'grimba_ads_direct_url' => [
                    'nullable',
                    'regex:/^https?:\/\/[A-Za-z0-9._~%\-\/?#\[\]@!$&\'()*+,;=:{}]+$/',
                    'max:255',
                ],
                'slots' => ['nullable', 'array'],
                'slots.*' => ['nullable', 'string', 'regex:/^\d{4,}$/', 'max:24'],
            ]);

            // Sales mailbox: validate as a real email before persisting.
            $mailbox = trim((string) ($data['grimba_advertiser_leads_sales_mailbox'] ?? ''));
            if ($mailbox !== '' && ! filter_var($mailbox, FILTER_VALIDATE_EMAIL)) {
                return back()->withInput()->with('error_msg', __('Adresse email mailbox invalide.'));
            }
            setting()->set('grimba_advertiser_leads_sales_mailbox', $mailbox);

            setting()->set('ads_google_adsense_unit_client_id', trim((string) ($data['ads_google_adsense_unit_client_id'] ?? '')));
            setting()->set('grimba_ads_direct_url', trim((string) ($data['grimba_ads_direct_url'] ?? '')));

            $slotsIn = (array) ($data['slots'] ?? []);
            $valid = [
                'grimba_home_top', 'grimba_home_mid', 'grimba_home_native',
                'grimba_chrome_top', 'grimba_chrome_bottom',
                'grimba_sources_top', 'grimba_sources_mid',
                'grimba_article_top', 'grimba_article_mid',
                'grimba_story_after_hero', 'grimba_story_mid', 'grimba_story_sidebar',
            ];
            foreach ($valid as $key) {
                $value = trim((string) ($slotsIn[$key] ?? ''));
                setting()->set('grimba_ads_slot_' . $key, $value);
            }

            setting()->save();

            \Illuminate\Support\Facades\Log::info('[GrimbaAdsConfig] admin update', [
                'user_id' => $request->user()?->id,
                'mailbox_set' => $mailbox !== '',
                'client_set' => isset($data['ads_google_adsense_unit_client_id']) && $data['ads_google_adsense_unit_client_id'] !== '',
            ]);

            return back()->with('success_msg', __('Configuration mise à jour.'));
        })->name('ads-config.save');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-ads-config')
                ->priority(45)
                ->parentId('grimba-root')
                ->name('Config publicités')
                ->icon('ti ti-layout-board-split')
                ->route('grimba.ads-config.index')
        );
    });
});
