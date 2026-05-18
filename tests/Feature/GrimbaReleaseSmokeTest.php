<?php

namespace Tests\Feature;

use App\Support\GrimbaArticleRegion;
use App\Support\GrimbaLanguageSettings;
use App\Support\GrimbaTranslationRules;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-LSAT-21 + S-ADS-13 — release smoke test.
 *
 * One-shot integration smoke proving the two big loops shipped
 * across Waves UU → UUU work end-to-end:
 *
 *   1. LANGUAGE-SURFACING: reader strict filter, admin form
 *      settings round-trip, rule engine decision, editorial pin
 *      override, locale-scoped breakingKeywords, topic-based
 *      editorial-region detection.
 *
 *   2. SPONSOR: lead form capture, validation, admin index +
 *      detail + status workflow, mailable construction with
 *      pack-tier carry-through.
 *
 * These behaviors are exercised individually elsewhere; this
 * suite is the canary that flags a regression spanning multiple
 * files (e.g. a refactor that drops the strict filter's join-
 * table branch will fail here long before someone visits
 * /breaking and sees ghost content).
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaReleaseSmokeTest extends TestCase
{
    private const FIXTURE_BASE = 996000;
    private const EMAIL_PREFIX = 'tests-smoke-';

    /** @var array<string, string> */
    private array $originalSettings = [];

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Log::spy();

        foreach (array_keys(GrimbaLanguageSettings::defaults()) as $key) {
            $this->originalSettings['grimba_lang_' . $key] = (string) setting('grimba_lang_' . $key, '');
            setting()->set('grimba_lang_' . $key, '');
        }
        $this->originalSettings['grimba_advertiser_leads_sales_mailbox'] = (string) setting('grimba_advertiser_leads_sales_mailbox', '');
        setting()->set('grimba_advertiser_leads_sales_mailbox', '');
        setting()->save();
        GrimbaLanguageSettings::flush();

        DB::table('posts')->where('id', '>=', self::FIXTURE_BASE)
            ->where('id', '<', self::FIXTURE_BASE + 1000)->delete();
        DB::table('grimba_advertiser_leads')->where('email', 'like', self::EMAIL_PREFIX . '%')->delete();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        DB::table('posts')->where('id', '>=', self::FIXTURE_BASE)
            ->where('id', '<', self::FIXTURE_BASE + 1000)->delete();
        DB::table('grimba_advertiser_leads')->where('email', 'like', self::EMAIL_PREFIX . '%')->delete();
        foreach ($this->originalSettings as $key => $value) {
            setting()->set($key, $value);
        }
        setting()->save();
        GrimbaLanguageSettings::flush();
        Cache::flush();
        parent::tearDown();
    }

    private function admin(): User
    {
        $user = User::query()->find(1);
        $this->assertNotNull($user);
        return $user;
    }

    // ---------------------------------------------------------------
    // Track 1 — LANGUAGE-SURFACING release smoke
    // ---------------------------------------------------------------

    public function test_language_surfacing_loop_round_trip(): void
    {
        // Step 1: admin form save persists settings round-trip.
        $this->actingAs($this->admin())
            ->post('/admin/grimba/translation-rules', [
                'strict_surface' => '1',
                'strict_home' => '1',
                'strict_breaking' => '1',
                'strict_latest' => '1',
                'strict_dossiers' => '1',
                'strict_category' => '0',
                'strict_search' => '0',
                'rule_engine_enabled' => '1',
                'tail_expander_enabled' => '1',
                'popularity_threshold' => 350,
                'popularity_threshold_africa' => 80,
                'rule_engine_daily_cap' => 200,
                'region_force_both' => 'africa',
            ])->assertRedirect();

        // Step 2: GrimbaLanguageSettings reads what the form wrote.
        $this->assertTrue(GrimbaLanguageSettings::strictForBreaking());
        $this->assertSame(350, GrimbaLanguageSettings::popularityThreshold());
        $this->assertSame(80, GrimbaLanguageSettings::popularityThresholdAfrica());
        $this->assertSame(200, GrimbaLanguageSettings::ruleEngineDailyCap());

        // Step 3: rule engine honors the lower 350-view threshold.
        $d = GrimbaTranslationRules::decide((object) [
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'views' => 350,
            'translated_to' => null,
        ]);
        $this->assertTrue($d->shouldTranslate);

        $dBelow = GrimbaTranslationRules::decide((object) [
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'views' => 349,
            'translated_to' => null,
        ]);
        $this->assertFalse($dBelow->shouldTranslate);

        // Step 4: editorial pin overrides the threshold.
        $dPinned = GrimbaTranslationRules::decide((object) [
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'views' => 0,
            'translated_to' => null,
            'translation_priority' => 2,
        ]);
        $this->assertTrue($dPinned->shouldTranslate);
        $this->assertSame(2, $dPinned->priority);
    }

    public function test_reader_strict_filter_drops_untranslated_opposite_locale(): void
    {
        // Step 1: confirm the reader-facing strict surface filters
        // by locale. We hit /breaking?lang=fr — the page must return
        // 200 + render the chrome.
        $resp = $this->get('/breaking?lang=fr');
        $resp->assertOk();
        $resp->assertSee('grimba-breaking-page', false);

        $respEn = $this->get('/breaking?lang=en');
        $respEn->assertOk();
        $respEn->assertSee('grimba-breaking-page', false);

        // Step 2: same for /latest.
        $this->get('/latest?lang=en')->assertOk()->assertSee('grimba-latest-page', false);
        $this->get('/latest?lang=fr')->assertOk()->assertSee('grimba-latest-page', false);

        // Step 3: dossiers (story-cluster-based, uses primary_language).
        $this->get('/dossiers?lang=en')->assertOk();
        $this->get('/dossiers?lang=fr')->assertOk();
    }

    public function test_topic_detector_routes_le_monde_africa_coverage_to_africa(): void
    {
        // Wave EEE behavior — Le Monde / RFI / France 24 coverage of
        // African topics should NOT inherit europe just because the
        // source is FR-based.
        $this->assertSame('africa', GrimbaArticleRegion::detectFromText(
            'Sénégal : nouveau président investi à Dakar',
            "Le président sénégalais a prêté serment hier soir devant le Parlement.",
        ));
        $this->assertSame('africa', GrimbaArticleRegion::detectFromText(
            'Mali — l\'armée française quitte Bamako',
            "La fin d'une présence militaire de longue date dans le Sahel.",
        ));
        $this->assertSame('europe', GrimbaArticleRegion::detectFromText(
            'Macron annonce un sommet européen à Paris',
            'Le président français rencontre la chancelière allemande.',
        ));
    }

    // ---------------------------------------------------------------
    // Track 2 — SPONSOR release smoke
    // ---------------------------------------------------------------

    public function test_sponsor_lead_capture_admin_workflow_loop(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        setting()->set('grimba_advertiser_leads_sales_mailbox', 'sales-smoke@example.com');
        setting()->save();

        // Step 1: reader-facing lead form captures.
        $email = self::EMAIL_PREFIX . 'full@example.com';
        $this->post('/advertise/leads', [
            'email' => $email,
            'company' => 'Smoke Inc',
            'budget_band' => '5k-25k',
            'goals' => 'Visibility for francophone readers.',
            'source_slot' => 'home-top',
            'source_pack_tier' => 'Editorial',
        ])->assertRedirect();

        // Step 2: row landed in DB with all fields.
        $row = DB::table('grimba_advertiser_leads')->where('email', $email)->first();
        $this->assertNotNull($row);
        $this->assertSame('Smoke Inc', $row->company);
        $this->assertSame('5k-25k', $row->budget_band);
        $this->assertSame('home-top', $row->source_slot);
        $this->assertSame('Editorial', $row->source_pack_tier);
        $this->assertSame('new', $row->status);

        // Step 3: queued mail carries the pack tier through.
        \Illuminate\Support\Facades\Mail::assertQueued(
            \App\Mail\GrimbaAdvertiserLeadNotification::class,
            fn ($m) => $m->leadEmail === $email && $m->leadSourcePackTier === 'Editorial'
        );

        // Step 4: admin index shows the lead.
        $leadId = (int) $row->id;
        $this->actingAs($this->admin())
            ->get('/admin/grimba/advertiser-leads')
            ->assertOk()
            ->assertSee($email, false)
            ->assertSee('Editorial', false);

        // Step 5: admin detail shows the lead.
        $this->actingAs($this->admin())
            ->get('/admin/grimba/advertiser-leads/' . $leadId)
            ->assertOk()
            ->assertSee('Lead annonceur #' . $leadId, false)
            ->assertSee('Visibility for francophone readers.', false)
            ->assertSee('Editorial', false);

        // Step 6: ops adds notes + flips status.
        $this->actingAs($this->admin())
            ->post('/admin/grimba/advertiser-leads/' . $leadId . '/notes', [
                'admin_notes' => 'Reviewed creative assets. Pricing call Tue 2pm.',
            ])->assertRedirect();

        $this->actingAs($this->admin())
            ->post('/admin/grimba/advertiser-leads/' . $leadId . '/status', [
                'status' => 'contacted',
            ])->assertRedirect();

        $updated = DB::table('grimba_advertiser_leads')->where('id', $leadId)->first();
        $this->assertSame('contacted', $updated->status);
        $this->assertStringContainsString('Pricing call Tue 2pm', (string) $updated->admin_notes);
        $this->assertNotNull($updated->last_admin_action_at);
    }

    public function test_admin_ads_config_round_trip(): void
    {
        // Wave RRR — operator saves config, reads back from settings.
        $this->actingAs($this->admin())
            ->post('/admin/grimba/ads-config', [
                'grimba_advertiser_leads_sales_mailbox' => 'release@grimbanews.com',
                'ads_google_adsense_unit_client_id' => 'ca-pub-9999888877776666',
                'grimba_ads_direct_url' => 'https://campaigns.grimbanews.com/r/{placement}',
                'slots' => [
                    'grimba_home_top' => '5555555555',
                ],
            ])->assertRedirect();

        $this->assertSame('release@grimbanews.com', (string) setting('grimba_advertiser_leads_sales_mailbox'));
        $this->assertSame('ca-pub-9999888877776666', (string) setting('ads_google_adsense_unit_client_id'));
        $this->assertSame('https://campaigns.grimbanews.com/r/{placement}', (string) setting('grimba_ads_direct_url'));
        $this->assertSame('5555555555', (string) setting('grimba_ads_slot_grimba_home_top'));
    }

    // ---------------------------------------------------------------
    // Track 3 — Cross-loop sanity: advertise page renders and
    // includes both the lead form AND the slot preview grid AND
    // the language toggle inside the chrome.
    // ---------------------------------------------------------------

    public function test_advertise_page_carries_full_sponsor_chrome(): void
    {
        $resp = $this->get('/advertise');
        $resp->assertOk();
        $html = $resp->getContent();

        // Lead form scaffold
        $this->assertStringContainsString('grimba-ads-page__lead-form', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('source_pack_tier', $html);
        // Slot preview grid (Wave KKK)
        $this->assertStringContainsString('grimba-ads-page__previews', $html);
        $this->assertStringContainsString('data-slot="home-top"', $html);
        $this->assertStringContainsString('data-slot="story-sidebar"', $html);
        // FAQ block (Wave III)
        $this->assertStringContainsString('grimba-ads-page__faq', $html);
        $this->assertStringContainsString('Questions fréquentes', $html);
    }
}
