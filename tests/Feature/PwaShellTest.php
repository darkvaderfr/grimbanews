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

    public function test_homepage_forces_light_theme_until_dark_mode_audit_finishes(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-bs-theme="light"', false)
            ->assertSee("document.documentElement.setAttribute('data-bs-theme', 'light');", false)
            ->assertSee('grimba_theme=light', false);

        $this->withUnencryptedCookies(['grimba_theme' => 'dark'])
            ->get('/')
            ->assertOk()
            ->assertSee('data-bs-theme="light"', false)
            ->assertSee('grimba_theme=light', false);
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
}
