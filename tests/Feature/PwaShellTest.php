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

    public function test_edition_picker_is_solid_and_only_exposes_canonical_public_editions(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-grimba-edition="africa"', false)
            ->assertSee('data-grimba-edition="international"', false)
            ->assertSee('Afrique')
            ->assertSee('International')
            ->assertDontSee('data-grimba-edition="france"', false)
            ->assertDontSee('data-grimba-edition="uk"', false)
            ->assertDontSee('data-grimba-edition="us"', false)
            ->assertDontSee('data-grimba-edition="canada"', false)
            ->assertSee('.grimba-edition-toggle', false)
            ->assertSee('background: #1a1713;', false);
    }

    public function test_region_choice_suppresses_onboarding_overlay_across_editions(): void
    {
        foreach (['africa' => 'Afrique', 'international' => 'International'] as $region => $label) {
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

    public function test_legacy_edition_cookie_maps_to_international_without_stock_overlays(): void
    {
        $this->withUnencryptedCookies(['grimba_region' => 'uk'])
            ->get('/')
            ->assertOk()
            ->assertSee('International')
            ->assertSee('data-grimba-edition="international"', false)
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
            ->assertJsonPath('region', 'international')
            ->assertPlainCookie('grimba_region', 'international')
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
