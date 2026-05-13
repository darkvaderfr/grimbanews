<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaShellTest extends TestCase
{
    public function test_public_shell_advertises_manifest_and_service_worker(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('manifest.webmanifest')
            ->assertSee('grimba-sw.js')
            ->assertSee('apple-mobile-web-app-title');
    }

    public function test_edition_picker_is_solid_and_exposes_4_canonical_regions(): void
    {
        // Fleet K (2026-05-05) — picker now exposes 4 regions:
        // Africa / Europe / Americas / International. Legacy values
        // (france/uk/us/canada) are migration-only and not surfaced.
        $this->get('/')
            ->assertOk()
            ->assertSee('data-grimba-edition="africa"', false)
            ->assertSee('data-grimba-edition="europe"', false)
            ->assertSee('data-grimba-edition="americas"', false)
            ->assertSee('data-grimba-edition="international"', false)
            ->assertSee('Afrique')
            ->assertSee('Europe')
            ->assertSee('Amériques')
            ->assertSee('International')
            ->assertDontSee('data-grimba-edition="france"', false)
            ->assertDontSee('data-grimba-edition="uk"', false)
            ->assertDontSee('data-grimba-edition="us"', false)
            ->assertDontSee('data-grimba-edition="canada"', false)
            ->assertSee('.grimba-edition-toggle', false)
            ->assertSee('background: #1a1713;', false)
            ->assertSee('grimba-header__tools', false)
            ->assertSee('html[data-bs-theme="dark"] .grimba-edition-toggle__option', false)
            ->assertSee('color: #fffaf0;', false)
            ->assertSee('html[data-bs-theme="dark"] .grimba-edition-toggle__count', false);
    }

    public function test_header_tool_css_does_not_override_edition_toggle_links(): void
    {
        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));

        $this->assertStringContainsString('max-height: 96px;', $css);
        $this->assertStringContainsString('.grimba-header__tools > a', $css);
        $this->assertStringContainsString('.grimba-header__tools .grimba-edition-toggle', $css);
        $this->assertStringContainsString(':not([class*="grimba-edition-toggle__option"])', $css);
        $this->assertStringNotContainsString('.grimba-header__tools a {', $css);
    }

    public function test_mobile_shell_css_guards_against_logged_in_overflow(): void
    {
        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));
        $cookieConsent = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/partials/cookie-consent.blade.php');

        $this->get('/')
            ->assertOk()
            ->assertSee('grimba-header__actions', false)
            ->assertSee('grimba-mobile-nav__icon', false);

        $this->assertStringContainsString('body.show-admin-bar', $css);
        $this->assertStringContainsString('#admin_bar', $css);
        $this->assertStringContainsString('min-width: 0 !important;', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr) auto;', $css);
        $this->assertStringContainsString('.grimba-header__actions', $css);
        $this->assertStringContainsString('.grimba-translation-note .gn-nobuai-chip', $css);
        $this->assertStringContainsString('.grimba-chips .container-xxl', $css);
        $this->assertStringContainsString('contain: layout paint;', $css);
        $this->assertStringContainsString('max-width: min(78vw, 18rem);', $css);
        $this->assertStringContainsString('.grimba-search-page .pagination', $css);
        $this->assertStringContainsString('flex-wrap: wrap;', $css);
        $this->assertStringContainsString('.grimba-mobile-nav__item.is-active .grimba-mobile-nav__icon', $css);
        $this->assertStringContainsString('.phpdebugbar-openhandler', $css);
        $this->assertStringContainsString('body.grimba-home .site-notice.js-site-notice', $css);
        $this->assertStringContainsString('body.grimba-home a.grimba-briefing__headline', $css);
        $this->assertStringContainsString('color: #fffaf0;', $css);
        $this->assertStringContainsString('.grimba-urgency .container-xxl', $css);
        $this->assertStringContainsString('min-height: 32px;', $css);
        $this->assertStringContainsString('.grimba-translation-note-wrap', $css);
        $this->assertStringContainsString('-webkit-line-clamp: 2;', $css);
        $this->assertStringContainsString('bottom: calc(92px + env(safe-area-inset-bottom));', $cookieConsent);
        $this->assertStringContainsString('max-height: min(420px, calc(100vh - 150px - env(safe-area-inset-bottom)));', $cookieConsent);
        $this->assertStringContainsString('max-height: 8.5rem;', $cookieConsent);
        $this->assertStringContainsString('grid-template-columns: 1fr;', $cookieConsent);
    }

    public function test_cookie_banner_respects_existing_consent_cookie_values(): void
    {
        foreach (['accepted', 'rejected', 'necessary', 'essential'] as $value) {
            $this->withUnencryptedCookies(['grimba_cookie_consent' => $value])
                ->get('/')
                ->assertOk()
                ->assertDontSee('id="grimba-cookie-consent"', false);
        }
    }

    public function test_dark_mode_editorial_selection_surfaces_have_explicit_contrast(): void
    {
        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));
        $categoryView = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/views/category.blade.php');
        $sourceView = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/views/source.blade.php');
        $ownersView = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/views/owners.blade.php');

        $this->get('/')
            ->assertOk()
            ->assertSee('grimba-similar__chip', false);

        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-similar__chip', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-topic-source-card', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-similar-source', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-owner-source-card', $css);
        $this->assertStringContainsString('background: rgba(246, 241, 232, 0.08) !important;', $css);
        $this->assertStringContainsString('color: #fffaf0 !important;', $css);
        $this->assertStringContainsString('class="grimba-topic-source-card"', $categoryView);
        $this->assertStringContainsString('class="grimba-similar-source"', $sourceView);
        $this->assertStringContainsString('class="grimba-owner-source-card"', $ownersView);
    }

    public function test_vault_save_buttons_are_css_driven_in_dark_mode(): void
    {
        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));
        $saveButton = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/partials/save-button.blade.php');
        $vaultScript = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/partials/home/vault-script.blade.php');

        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-save-btn', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-save-btn[aria-pressed="true"]', $css);
        $this->assertStringContainsString('btn.style.removeProperty(\'background\');', $vaultScript);
        $this->assertStringContainsString('btn.style.removeProperty(\'color\');', $vaultScript);
        $this->assertStringNotContainsString('background:rgba(255,255,255,0.6)', $saveButton);
        $this->assertStringNotContainsString('btn.style.background = saved', $vaultScript);
    }

    public function test_vault_empty_state_uses_explicit_dark_mode_contrast_classes(): void
    {
        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));
        $vaultView = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/views/coffre.blade.php');

        $this->get('/coffre')
            ->assertOk()
            ->assertSee('grimba-coffre__lede', false)
            ->assertSee('grimba-coffre__empty-copy', false);

        $this->assertStringContainsString('.grimba-coffre__lede', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-coffre__lede', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-coffre__empty-copy', $css);
        $this->assertStringContainsString('color: rgba(255, 250, 240, 0.78) !important;', $css);
        $this->assertStringContainsString('padding-bottom: calc(5.25rem + env(safe-area-inset-bottom)) !important;', $css);
        $this->assertStringNotContainsString('class="mb-0 opacity-85"', $vaultView);
        $this->assertStringNotContainsString('style="font-size:48px; line-height:1; margin-bottom:14px; opacity:0.4;"', $vaultView);
    }

    public function test_auth_and_local_form_controls_use_theme_classes(): void
    {
        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));
        $loginView = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/views/member/auth/login.blade.php');
        $registerView = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/views/member/auth/register.blade.php');
        $localView = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/views/local.blade.php');

        $this->assertStringContainsString('.grimba-form-pill', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-form-pill', $css);
        $this->assertStringContainsString('.grimba-auth-grid', $css);
        $this->assertStringContainsString('.grimba-local__field--country', $css);
        $this->assertStringContainsString('.grimba-local__submit', $css);
        $this->assertStringContainsString('padding-bottom: calc(7rem + env(safe-area-inset-bottom)) !important;', $css);
        $this->assertStringContainsString('class="grimba-form-pill mb-3"', $loginView);
        $this->assertStringContainsString('class="grimba-auth-grid"', $registerView);
        $this->assertStringContainsString('class="grimba-form-pill grimba-local__input--country"', $localView);
        $this->assertStringContainsString('grimba-local__panel', $localView);
        $this->assertStringNotContainsString('background:rgba(255,255,255,0.7)', $loginView . $registerView . $localView);
    }

    public function test_command_palette_shell_and_index_are_available(): void
    {
        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));

        $this->get('/')
            ->assertOk()
            ->assertSee('id="grimba-command-palette"', false)
            ->assertSee('data-grimba-command-form', false)
            ->assertSee('command-palette.json', false)
            ->assertSee('grimba_command_palette_index_v1', false)
            ->assertSee("key === 'k'", false)
            ->assertSee('event.metaKey || event.ctrlKey', false);

        $this->get('/sources')
            ->assertOk()
            ->assertSee('id="grimba-command-palette"', false);

        $response = $this->get('/command-palette.json')
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=300, public, s-maxage=300')
            ->assertJsonPath('ttl_seconds', 300);

        $items = $response->json('items');
        $this->assertNotEmpty($items);
        $this->assertContains('nav', array_column($items, 'type'));
        $this->assertContains('story', array_column($items, 'type'));
        $this->assertArrayHasKey('url', $items[0]);

        foreach ($items as $item) {
            $this->assertStringNotContainsString('categorie=', (string) ($item['url'] ?? ''));
        }

        $this->assertStringContainsString('.grimba-command-palette.is-open', $css);
        $this->assertStringContainsString('display: flex;', $css);
        $this->assertStringContainsString('justify-content: center;', $css);
    }

    public function test_article_shell_css_guards_against_dead_sidebar_and_overflow(): void
    {
        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));
        $view = file_get_contents(dirname(__DIR__, 2) . '/platform/themes/echo/views/post.blade.php');

        $this->assertStringContainsString('grimba-article-shell', $view);
        $this->assertStringContainsString('grimba-article-primary', $view);
        $this->assertStringContainsString('$__gnHasPrimarySidebar', $view);
        $this->assertStringContainsString('mx-auto', $view);
        $this->assertStringContainsString('body.grimba-home .grimba-article-shell', $css);
        $this->assertStringContainsString('overflow-x: clip;', $css);
        $this->assertStringContainsString('overflow-wrap: anywhere;', $css);
        $this->assertStringContainsString('body.grimba-home .grimba-article-shell .container', $css);
        $this->assertStringContainsString('body.grimba-home .grimba-sub-main > .container-xxl', $css);
        $this->assertStringContainsString('flex-wrap: wrap;', $css);
        $this->assertStringContainsString('overflow-x: visible;', $css);
        $this->assertStringContainsString('body.grimba-home[id^="post-"] .grimba-mobile-nav', $css);
        $this->assertStringContainsString('display: none !important;', $css);
    }

    public function test_region_choice_suppresses_onboarding_overlay_across_editions(): void
    {
        foreach ([
            'africa'        => 'Afrique',
            'europe'        => 'Europe',
            'americas'      => 'Amériques',
            'international' => 'International',
        ] as $region => $label) {
            $this->withUnencryptedCookies(['grimba_region' => $region])
                ->get('/')
                ->assertOk()
                ->assertSee($label)
                ->assertSee('data-grimba-edition="' . $region . '"', false)
                ->assertDontSee('grimba-onboard-modal', false)
                ->assertDontSee('grimba-newsletter-modal is-open', false);
        }
    }

    public function test_fresh_public_pages_do_not_auto_open_onboarding_overlay(): void
    {
        foreach (['/', '/sources'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('grimba-onboard-modal', false)
                ->assertDontSee('grimba-newsletter-modal grimba-onboard-modal is-open', false)
                ->assertDontSee('id="newsletter-popup"', false)
                ->assertDontSee('vendor/core/plugins/newsletter/js/newsletter.js', false)
                ->assertDontSee('<div class="modal-backdrop', false)
                ->assertSee('cleanupStockBackdrop', false)
                ->assertDontSee('mfp-bg', false)
                ->assertSee('aria-hidden="true"', false);
        }
    }

    public function test_homepage_respects_grimba_theme_cookie_for_dark_light_auto(): void
    {
        // No cookie → SSR defaults to light (auto preference; client may swap to dark on prefers-color-scheme).
        $this->get('/')
            ->assertOk()
            ->assertSee('data-bs-theme="light"', false)
            ->assertSee('data-theme="light"', false)
            ->assertSee('data-grimba-theme-pref="auto"', false)
            ->assertSee("matchMedia('(prefers-color-scheme: dark)')", false);

        // Explicit dark → SSR paints dark immediately so there's no flash.
        $this->withUnencryptedCookies(['grimba_theme' => 'dark'])
            ->get('/')
            ->assertOk()
            ->assertSee('data-bs-theme="dark"', false)
            ->assertSee('data-theme="light"', false)
            ->assertSee('data-grimba-theme-pref="dark"', false);

        // Explicit light → SSR paints light.
        $this->withUnencryptedCookies(['grimba_theme' => 'light'])
            ->get('/')
            ->assertOk()
            ->assertSee('data-bs-theme="light"', false)
            ->assertSee('data-grimba-theme-pref="light"', false);

        // Garbage value falls back to auto.
        $this->withUnencryptedCookies(['grimba_theme' => 'banana'])
            ->get('/')
            ->assertOk()
            ->assertSee('data-grimba-theme-pref="auto"', false);
    }

    public function test_homepage_does_not_clobber_user_theme_storage(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // Previous regression: boot script force-overwrote grimba_theme to "light"
        // and rewrote echo-theme/themeMode in localStorage on every page load,
        // making it impossible to switch to dark or auto. Guard against that.
        $this->assertStringNotContainsString("document.cookie = 'grimba_theme=light;", $html);
        $this->assertStringNotContainsString("window.localStorage.setItem('echo-theme', 'light');", $html);
        $this->assertStringNotContainsString("window.localStorage.setItem('themeMode', 'light');", $html);
    }

    public function test_legacy_edition_cookie_maps_to_4region_canonical_without_stock_overlays(): void
    {
        // Fleet K (2026-05-05): legacy cookies now fold into 4 canonical
        // regions instead of forcing everything to International.
        // uk / france → europe; us / canada → americas; afrique → africa;
        // monde / unknown → international.
        $this->withUnencryptedCookies(['grimba_region' => 'uk'])
            ->get('/')
            ->assertOk()
            ->assertSee('Europe')
            ->assertSee('data-grimba-edition="europe"', false)
            ->assertDontSee('Édition UK')
            ->assertDontSee('grimba-newsletter-modal is-open', false)
            ->assertDontSee('grimba-onboard-modal is-open', false)
            ->assertDontSee('id="newsletter-popup"', false)
            ->assertDontSee('vendor/core/plugins/newsletter/js/newsletter.js', false)
            ->assertDontSee('<div class="modal-backdrop', false)
            ->assertSee('cleanupStockBackdrop', false)
            ->assertDontSee('mfp-bg', false);
    }

    public function test_explicit_onboarding_query_can_open_the_modal(): void
    {
        $this->get('/?onboarding=1')
            ->assertOk()
            ->assertSee('grimba-onboard-modal', false)
            ->assertSee('is-open', false)
            ->assertSee('aria-hidden="false"', false);
    }

    public function test_legacy_region_switch_maps_to_canonical_edition_and_marks_reader_onboarded(): void
    {
        $this->postJson('/region/set', ['region' => 'uk'])
            ->assertOk()
            ->assertJsonPath('region', 'europe')
            ->assertPlainCookie('grimba_region', 'europe')
            ->assertPlainCookie('grimba_onboarded', '1');
    }

    public function test_homepage_hero_copy_uses_readable_ink_plate(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('.grimba-hero__text', false)
            ->assertSee('rgba(11, 10, 8, .93)', false)
            ->assertSee('.grimba-hero .grimba-hero__desc', false)
            ->assertSee('backdrop-filter: none;', false);
    }

    public function test_manifest_and_offline_shell_assets_exist(): void
    {
        $manifestPath = public_path('manifest.webmanifest');

        $this->assertFileExists($manifestPath);
        $this->assertFileExists(public_path('grimba-sw.js'));
        $this->assertFileExists(public_path('offline.html'));

        $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('GrimbaNews', $manifest['name']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertNotEmpty($manifest['icons']);
    }

    public function test_homepage_css_defuses_orphan_bootstrap_backdrops(): void
    {
        $css = file_get_contents(dirname(__DIR__, 2) . '/public/themes/echo/css/grimba-home.css');

        $this->assertStringContainsString('body.grimba-home .modal-backdrop', $css);
        $this->assertStringContainsString('body.grimba-home.modal-open', $css);
        $this->assertStringContainsString('pointer-events: none !important', $css);
    }

    public function test_homepage_css_neutralizes_stock_echo_dark_theme_path(): void
    {
        $css = file_get_contents(dirname(__DIR__, 2) . '/public/themes/echo/css/grimba-home.css');

        $this->assertStringContainsString('--background-color-dark: var(--gn-paper);', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-theme="dark"]', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-theme="dark"] body.grimba-home', $css);
        $this->assertStringContainsString('color-scheme: light;', $css);
    }
}
