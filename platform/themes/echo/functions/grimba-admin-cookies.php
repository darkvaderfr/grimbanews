<?php

/*
 * S145 — admin settings for the site-wide cookie consent banner.
 *
 * Routes under /admin/grimba/cookies :
 *   GET   /  → form
 *   POST  /  → save
 *
 * Settings persisted (grimba_cookie_ prefix):
 *   grimba_cookie_active        — bool
 *   grimba_cookie_title         — string
 *   grimba_cookie_body          — text
 *   grimba_cookie_accept_label  — string
 *   grimba_cookie_reject_label  — string
 *   grimba_cookie_more_label    — string
 *   grimba_cookie_more_url      — string
 */

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('cookies', function () {
            return view('grimba-admin.cookies.index', [
                'active'        => (bool) setting('grimba_cookie_active', true),
                'title'         => (string) setting('grimba_cookie_title', 'Cookies'),
                'body'          => (string) setting('grimba_cookie_body',
                    "GrimbaNews utilise des cookies essentiels pour le fonctionnement du site (préférences linguistiques, mode de lecture, historique de lecture local) et, avec votre accord, des cookies de mesure d'audience anonymisée. Vous pouvez changer d'avis à tout moment depuis la page de confidentialité."
                ),
                'accept_label'  => (string) setting('grimba_cookie_accept_label', 'Accepter'),
                'reject_label'  => (string) setting('grimba_cookie_reject_label', 'Refuser les non-essentiels'),
                'more_label'    => (string) setting('grimba_cookie_more_label', 'En savoir plus'),
                'more_url'      => (string) setting('grimba_cookie_more_url', '/confidentialite'),
            ]);
        })->name('cookies.index');

        Route::post('cookies', function (Request $request) {
            /** @var SettingStore $store */
            $store = app(SettingStore::class);

            $store->set('grimba_cookie_active',       (bool)   $request->input('active', false));
            $store->set('grimba_cookie_title',        (string) $request->input('title', 'Cookies'));
            $store->set('grimba_cookie_body',         (string) $request->input('body', ''));
            $store->set('grimba_cookie_accept_label', (string) $request->input('accept_label', 'Accepter'));
            $store->set('grimba_cookie_reject_label', (string) $request->input('reject_label', 'Refuser'));
            $store->set('grimba_cookie_more_label',   (string) $request->input('more_label', 'En savoir plus'));
            $store->set('grimba_cookie_more_url',     (string) $request->input('more_url', '/confidentialite'));
            $store->save();

            return redirect()
                ->route('grimba.cookies.index')
                ->with('success_msg', 'Réglages cookies enregistrés.');
        })->name('cookies.save');
    });

DashboardMenu::default()->beforeRetrieving(function (): void {
    DashboardMenu::make()->registerItem(
        DashboardMenuItem::make()
            ->id('grimba-cookies')
            ->priority(70)
            ->parentId('grimba-root')
            ->name('Cookies')
            ->icon('ti ti-cookie')
            ->route('grimba.cookies.index')
    );
});
