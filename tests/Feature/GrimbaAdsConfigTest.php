<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-ADS-12 — pin the /admin/grimba/ads-config workflow shipped in
 * Wave RRR. Covers route registration, auth guard, form render,
 * setting persistence, and the regex/email validators that guard
 * the AdSense client + per-slot IDs.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaAdsConfigTest extends TestCase
{
    private array $snapshot = [];

    private const SNAPSHOT_KEYS = [
        'grimba_advertiser_leads_sales_mailbox',
        'ads_google_adsense_unit_client_id',
        'grimba_ads_direct_url',
        'grimba_ads_slot_grimba_home_top',
        'grimba_ads_slot_grimba_home_mid',
        'grimba_ads_slot_grimba_article_top',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        // The admin save handler writes a Log::info audit
        // breadcrumb. The test harness's log driver treats it as
        // an error; Log::spy() bypasses the real driver.
        \Illuminate\Support\Facades\Log::spy();

        foreach (self::SNAPSHOT_KEYS as $key) {
            $this->snapshot[$key] = (string) setting($key, '');
            setting()->set($key, '');
        }
        setting()->save();
    }

    protected function tearDown(): void
    {
        foreach ($this->snapshot as $key => $value) {
            setting()->set($key, $value);
        }
        setting()->save();
        parent::tearDown();
    }

    private function admin(): User
    {
        $user = User::query()->find(1);
        $this->assertNotNull($user, 'Fixture DB must contain the system admin user.');
        return $user;
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->get('/admin/grimba/ads-config')->assertRedirect('/admin/login');
        $this->post('/admin/grimba/ads-config')->assertRedirect('/admin/login');
    }

    public function test_form_renders_for_authenticated_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/grimba/ads-config')
            ->assertOk()
            ->assertSee('Config publicités', false)
            ->assertSee('Mailbox équipe ventes', false)
            ->assertSee('Client ID AdSense', false)
            ->assertSee('Slots AdSense par emplacement', false)
            // Spot-check the slot grid renders the placement labels
            // we wired up in Wave RRR.
            ->assertSee('Home top', false)
            ->assertSee('Dossier — sidebar', false);
    }

    public function test_form_save_persists_clean_payload(): void
    {
        $response = $this->actingAs($this->admin())
            ->post('/admin/grimba/ads-config', [
                'grimba_advertiser_leads_sales_mailbox' => 'sales@grimbanews.com',
                'ads_google_adsense_unit_client_id' => 'ca-pub-1234567890123456',
                'grimba_ads_direct_url' => 'https://campaigns.grimbanews.com/?p={placement}',
                'slots' => [
                    'grimba_home_top' => '1234567890',
                    'grimba_home_mid' => '9876543210',
                ],
            ]);
        $response->assertRedirect();

        // The validator's error redirect lands on /admin/grimba/ads-config
        // with a flash `errors` bag; a successful save also redirects
        // back but with `success_msg` flash. If we redirected with
        // errors, the settings stay empty. Surface the bag if so.
        $session = session()->all();
        $errors = $session['errors'] ?? null;
        $this->assertNull(
            $errors instanceof \Illuminate\Support\ViewErrorBag && $errors->getBag('default')->any()
                ? $errors->getBag('default')->all()
                : null,
            'Validator must accept the clean payload.'
        );

        $this->assertSame('sales@grimbanews.com', (string) setting('grimba_advertiser_leads_sales_mailbox'));
        $this->assertSame('ca-pub-1234567890123456', (string) setting('ads_google_adsense_unit_client_id'));
        $this->assertSame('https://campaigns.grimbanews.com/?p={placement}', (string) setting('grimba_ads_direct_url'));
        $this->assertSame('1234567890', (string) setting('grimba_ads_slot_grimba_home_top'));
        $this->assertSame('9876543210', (string) setting('grimba_ads_slot_grimba_home_mid'));
    }

    public function test_form_save_rejects_invalid_adsense_client(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/grimba/ads-config', [
                'ads_google_adsense_unit_client_id' => 'definitely-not-an-adsense-id',
            ])
            ->assertRedirect();

        // Validator rejects → setting stays empty (snapshot baseline).
        $this->assertSame('', (string) setting('ads_google_adsense_unit_client_id'));
    }

    public function test_form_save_rejects_invalid_slot_id_pattern(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/grimba/ads-config', [
                // 3 chars — below the 4+ digit floor.
                'slots' => ['grimba_home_top' => '123'],
            ])
            ->assertRedirect();

        $this->assertSame('', (string) setting('grimba_ads_slot_grimba_home_top'));
    }

    public function test_form_save_rejects_invalid_email_for_mailbox(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/grimba/ads-config', [
                'grimba_advertiser_leads_sales_mailbox' => 'not-an-email',
            ])
            ->assertRedirect();

        // Handler short-circuits with error_msg and skips persist.
        $this->assertSame('', (string) setting('grimba_advertiser_leads_sales_mailbox'));
    }

    public function test_empty_mailbox_clears_setting(): void
    {
        // Prime with a value, then submit an empty payload — handler
        // clears the setting cleanly.
        setting()->set('grimba_advertiser_leads_sales_mailbox', 'old@example.com');
        setting()->save();

        $this->actingAs($this->admin())
            ->post('/admin/grimba/ads-config', [
                'grimba_advertiser_leads_sales_mailbox' => '',
            ])
            ->assertRedirect();

        $this->assertSame('', (string) setting('grimba_advertiser_leads_sales_mailbox'));
    }
}
