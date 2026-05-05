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
        $this->assertStringContainsString('.grimba-mobile-nav__item.is-active .grimba-mobile-nav__icon', $css);
        $this->assertStringContainsString('.phpdebugbar-openhandler', $css);
        $this->assertStringContainsString('body.grimba-home .site-notice.js-site-notice', $css);
        $this->assertStringContainsString('body.grimba-home a.grimba-briefing__headline', $css);
        $this->assertStringContainsString('color: #fffaf0;', $css);
        $this->assertStringContainsString('bottom: calc(92px + env(safe-area-inset-bottom));', $cookieConsent);
    }

    public function test_command_palette_shell_and_index_are_available(): void
    {
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
