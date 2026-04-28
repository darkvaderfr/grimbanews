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

    public function test_region_picker_is_solid_and_includes_canada(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-grimba-region="canada"', false)
            ->assertSee('Canada')
            ->assertSee('backdrop-filter: none !important', false)
            ->assertSee('opacity: 1 !important', false);
    }

    public function test_region_choice_suppresses_onboarding_overlay_across_editions(): void
    {
        foreach (['france', 'uk', 'us', 'canada', 'africa', 'international'] as $region) {
            $this->withUnencryptedCookies(['grimba_region' => $region])
                ->get('/')
                ->assertOk()
                ->assertSee('Édition')
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
                ->assertSee('aria-hidden="true"', false);
        }
    }

    public function test_explicit_onboarding_query_can_open_the_modal(): void
    {
        $this->get('/?onboarding=1')
            ->assertOk()
            ->assertSee('grimba-onboard-modal', false)
            ->assertSee('is-open', false)
            ->assertSee('aria-hidden="false"', false);
    }

    public function test_region_switch_marks_reader_onboarded(): void
    {
        $this->postJson('/region/set', ['region' => 'uk'])
            ->assertOk()
            ->assertPlainCookie('grimba_region', 'uk')
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
}
