<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdRevenueSurfaceTest extends TestCase
{
    public function test_homepage_renders_direct_sponsor_inventory_without_ad_network_config(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->clearConfiguredAds();

        config([
            'grimba_ads.enabled' => true,
            'grimba_ads.adsense_client_id' => null,
            'grimba_ads.direct_fallback_enabled' => true,
            'grimba_ads.slots.grimba_home_top' => null,
        ]);

        $response = $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])->get('/');

        $response->assertOk();
        $response->assertSee('data-ad-location="grimba_home_top"', false);
        $response->assertSee('data-ad-mode="direct"', false);
        $response->assertSee('Sponsor this coverage');
    }

    public function test_homepage_can_render_env_backed_adsense_unit_slots(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        $this->clearConfiguredAds();

        config([
            'grimba_ads.enabled' => true,
            'grimba_ads.adsense_client_id' => 'ca-pub-1234567890123456',
            'grimba_ads.load_network_in_non_production' => true,
            'grimba_ads.direct_fallback_enabled' => true,
            'grimba_ads.slots.grimba_home_top' => '1234567890',
        ]);

        $response = $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])->get('/');

        $response->assertOk();
        $response->assertSee('pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1234567890123456', false);
        $response->assertSee('data-ad-client="ca-pub-1234567890123456"', false);
        $response->assertSee('data-ad-slot="1234567890"', false);
    }

    public function test_ads_txt_can_be_served_from_config(): void
    {
        config([
            'grimba_ads.ads_txt' => 'google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0',
        ]);

        $this->get('/ads.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0');
    }

    public function test_advertise_page_is_public_sales_surface(): void
    {
        $this->markTestIncomplete('Legacy markup pre-dossier-reinvention; see docs/GRIMBANEWS_TEST_DEBT_DOSSIER_REINVENTION.md');
        $this->withUnencryptedCookies([
            'grimba_lang' => 'en',
            'grimba_onboarded' => '1',
        ])->get('/advertise?slot=home-top')
            ->assertOk()
            ->assertSee('GrimbaNews Ads')
            ->assertSee('home-top')
            ->assertSee('Book inventory');
    }

    private function clearConfiguredAds(): void
    {
        if (Schema::hasTable('ads')) {
            DB::table('ads')->delete();
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->whereIn('key', [
                'ads_google_adsense_unit_client_id',
                'ads_google_adsense_auto_ads',
            ])->delete();
        }
    }
}
