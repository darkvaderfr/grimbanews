<?php

namespace Tests\Feature;

use App\Console\Commands\GrimbaTranslateByRule;
use App\Support\GrimbaLanguageSettings;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-LSAT-19 — pin the translation-monitor admin dashboard
 * contract. Closes the operator visibility loop on the 500-view
 * auto-translate Vader asked for.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaTranslationMonitorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Log::spy();
        GrimbaTranslateByRule::clearDecisions();
        Cache::forget('grimba_rule_engine_calls:' . now()->format('Y-m-d'));
    }

    protected function tearDown(): void
    {
        GrimbaTranslateByRule::clearDecisions();
        Cache::forget('grimba_rule_engine_calls:' . now()->format('Y-m-d'));
        parent::tearDown();
    }

    private function admin(): User
    {
        $user = User::query()->find(1);
        $this->assertNotNull($user);
        return $user;
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->get('/admin/grimba/translation-monitor')->assertRedirect('/admin/login');
        $this->post('/admin/grimba/translation-monitor/clear-decisions')->assertRedirect('/admin/login');
    }

    public function test_admin_dashboard_renders_with_no_decisions(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/grimba/translation-monitor')
            ->assertOk()
            ->assertSee('Moniteur de traduction', false)
            ->assertSee('Décisions récentes du moteur', false)
            ->assertSee('Traductions récentes', false)
            ->assertSee('Aucune décision', false);
    }

    public function test_dashboard_surfaces_calls_today_counter(): void
    {
        // Bump the cache counter by 7 — dashboard should show it.
        GrimbaTranslateByRule::recordCall(7);

        $this->actingAs($this->admin())
            ->get('/admin/grimba/translation-monitor')
            ->assertOk()
            ->assertSee('7', false); // calls-today metric value
    }

    public function test_dashboard_renders_recent_decisions(): void
    {
        // Push 3 fake decisions, verify they all surface.
        GrimbaTranslateByRule::recordDecision([
            'ts'      => now()->subMinutes(2)->toIso8601String(),
            'post_id' => 88001,
            'title'   => 'Le Monde — Macron speech',
            'from'    => 'fr',
            'to'      => 'en',
            'region'  => 'europe',
            'views'   => 750,
            'reason'  => 'popularity-threshold views=750>=500',
            'outcome' => 'ok',
            'driver'  => 'nobuai',
        ]);
        GrimbaTranslateByRule::recordDecision([
            'ts'      => now()->subMinutes(1)->toIso8601String(),
            'post_id' => 88002,
            'title'   => 'RFI — Senegal vote',
            'from'    => 'fr',
            'to'      => 'en',
            'region'  => 'africa',
            'views'   => 120,
            'reason'  => 'force-both-region:africa views=120>=100',
            'outcome' => 'ok',
        ]);
        GrimbaTranslateByRule::recordDecision([
            'ts'      => now()->toIso8601String(),
            'post_id' => 88003,
            'title'   => 'AP — Hurricane update',
            'from'    => 'en',
            'to'      => 'fr',
            'region'  => 'americas',
            'views'   => 0,
            'reason'  => 'editorial-pin priority=2',
            'outcome' => 'dry',
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/grimba/translation-monitor')
            ->assertOk()
            ->assertSee('Macron speech', false)
            ->assertSee('Senegal vote', false)
            ->assertSee('Hurricane update', false)
            ->assertSee('popularity-threshold', false)
            ->assertSee('force-both-region', false)
            ->assertSee('editorial-pin', false);
    }

    public function test_clear_decisions_action_empties_the_log(): void
    {
        GrimbaTranslateByRule::recordDecision(['post_id' => 99001, 'title' => 'Pre-clear fixture']);
        $this->assertCount(1, GrimbaTranslateByRule::recentDecisions(100));

        $this->actingAs($this->admin())
            ->post('/admin/grimba/translation-monitor/clear-decisions')
            ->assertRedirect();

        $this->assertSame([], GrimbaTranslateByRule::recentDecisions(100));
    }

    public function test_engine_disabled_badge_shows_when_setting_off(): void
    {
        setting()->set('grimba_lang_rule_engine_enabled', '0');
        setting()->save();
        GrimbaLanguageSettings::flush();

        try {
            $this->actingAs($this->admin())
                ->get('/admin/grimba/translation-monitor')
                ->assertOk()
                ->assertSee('Moteur DÉSACTIVÉ', false);
        } finally {
            setting()->set('grimba_lang_rule_engine_enabled', '');
            setting()->save();
            GrimbaLanguageSettings::flush();
        }
    }

    public function test_provider_visibility_card_renders(): void
    {
        // S-LSAT-19b — the provider-visibility card surfaces
        // whether any translator drivers are configured. Without
        // it, a silent "no drivers" failure mode is invisible to
        // operators.
        $this->actingAs($this->admin())
            ->get('/admin/grimba/translation-monitor')
            ->assertOk()
            ->assertSee('Drivers traducteur', false);
        // The test fixture environment may or may not have keys
        // configured; we just assert the card section exists with
        // the canonical label.
    }

    public function test_decisions_log_caps_at_100_entries(): void
    {
        // Push 150 entries; only the most recent 100 should survive.
        for ($i = 1; $i <= 150; $i++) {
            GrimbaTranslateByRule::recordDecision([
                'post_id' => $i,
                'title' => 'Fixture ' . $i,
                'outcome' => 'ok',
            ]);
        }
        $log = GrimbaTranslateByRule::recentDecisions(100);
        $this->assertCount(100, $log, 'Cap MUST hold at 100.');
        // recordDecision prepends, so the FIRST entry is the LAST pushed (id=150).
        $this->assertSame(150, (int) ($log[0]['post_id'] ?? 0));
    }
}
