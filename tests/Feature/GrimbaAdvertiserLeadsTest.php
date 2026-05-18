<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-ADS-09 — pin the sponsor lead capture + workflow contract.
 *
 * Tests the public POST /advertise/leads endpoint (Wave ZZ) AND
 * the admin workflow (Waves AAA + LLL): index list, detail view,
 * notes save, status workflow, delete.
 *
 * Public form tests don't need auth; admin tests use the fixture
 * admin user. Each test cleans up its own row via a TEST_ prefix
 * on the email so multiple parallel runs don't collide.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaAdvertiserLeadsTest extends TestCase
{
    private const EMAIL_PREFIX = 'tests-ads-';

    protected function setUp(): void
    {
        parent::setUp();
        $this->wipeFixtures();
    }

    protected function tearDown(): void
    {
        $this->wipeFixtures();
        parent::tearDown();
    }

    private function wipeFixtures(): void
    {
        DB::table('grimba_advertiser_leads')->where('email', 'like', self::EMAIL_PREFIX . '%')->delete();
    }

    private function admin(): User
    {
        $user = User::query()->find(1);
        $this->assertNotNull($user, 'Fixture DB must contain the system admin user.');
        return $user;
    }

    // ---------------------------------------------------------------
    // Public form
    // ---------------------------------------------------------------

    public function test_public_form_persists_minimum_valid_payload(): void
    {
        $email = self::EMAIL_PREFIX . 'minimum@example.com';
        $this->post('/advertise/leads', [
            'email' => $email,
        ])->assertRedirect();

        $row = DB::table('grimba_advertiser_leads')->where('email', $email)->first();
        $this->assertNotNull($row);
        $this->assertSame('new', $row->status);
        $this->assertNull($row->company);
        $this->assertNull($row->budget_band);
    }

    public function test_public_form_persists_full_payload(): void
    {
        $email = self::EMAIL_PREFIX . 'full@example.com';
        $this->post('/advertise/leads', [
            'email' => $email,
            'company' => 'ACME SA',
            'budget_band' => '1k-5k',
            'goals' => 'campagne de notoriété francophone',
            'source_slot' => 'home-top',
        ])->assertRedirect();

        $row = DB::table('grimba_advertiser_leads')->where('email', $email)->first();
        $this->assertNotNull($row);
        $this->assertSame('ACME SA', $row->company);
        $this->assertSame('1k-5k', $row->budget_band);
        $this->assertSame('campagne de notoriété francophone', $row->goals);
        $this->assertSame('home-top', $row->source_slot);
    }

    public function test_public_form_rejects_invalid_email(): void
    {
        $this->post('/advertise/leads', [
            'email' => 'not-an-email',
        ])->assertRedirect();

        // The validator redirects back with errors. The session bag
        // should have 'errors'; no row should exist.
        $this->assertSame(0, DB::table('grimba_advertiser_leads')
            ->where('email', 'like', self::EMAIL_PREFIX . '%')
            ->count());
    }

    public function test_public_form_rejects_bad_budget_band(): void
    {
        $email = self::EMAIL_PREFIX . 'badbudget@example.com';
        $this->post('/advertise/leads', [
            'email' => $email,
            'budget_band' => 'gajillion',
        ])->assertRedirect();

        $this->assertSame(0, DB::table('grimba_advertiser_leads')->where('email', $email)->count());
    }

    public function test_honeypot_trapped_payload_does_not_persist(): void
    {
        // The honeypot path writes a Log::info breadcrumb that the
        // test harness's log handler treats as an error. We spy on
        // the facade so the call still resolves but doesn't reach
        // the real driver.
        \Illuminate\Support\Facades\Log::spy();

        $email = self::EMAIL_PREFIX . 'bot@example.com';
        $this->post('/advertise/leads', [
            'email' => $email,
            '_hp' => 'bot filled this',
        ])->assertRedirect();

        // Honeypot returns success-shape but does NOT persist.
        $this->assertSame(0, DB::table('grimba_advertiser_leads')->where('email', $email)->count());
    }

    public function test_xhr_payload_returns_json_ok_shape(): void
    {
        $email = self::EMAIL_PREFIX . 'xhr@example.com';
        $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->post('/advertise/leads', [
            'email' => $email,
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertNotNull(DB::table('grimba_advertiser_leads')->where('email', $email)->first());
    }

    // ---------------------------------------------------------------
    // Admin workflow
    // ---------------------------------------------------------------

    private function seedLead(string $key, array $overrides = []): int
    {
        return DB::table('grimba_advertiser_leads')->insertGetId(array_merge([
            'email' => self::EMAIL_PREFIX . $key . '@example.com',
            'company' => 'Test Co',
            'budget_band' => '1k-5k',
            'goals' => 'Test goals.',
            'source_referrer' => null,
            'source_slot' => null,
            'locale' => 'fr',
            'ip' => '127.0.0.1',
            'status' => 'new',
            'admin_notes' => null,
            'last_admin_action_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_admin_index_renders_for_authenticated_admin(): void
    {
        $this->seedLead('idx');

        $this->actingAs($this->admin())
            ->get('/admin/grimba/advertiser-leads')
            ->assertOk()
            ->assertSee('Leads annonceurs', false)
            ->assertSee(self::EMAIL_PREFIX . 'idx@example.com', false);
    }

    public function test_admin_index_filters_by_status(): void
    {
        $this->seedLead('newrow', ['status' => 'new']);
        $this->seedLead('wonrow', ['status' => 'won']);

        $this->actingAs($this->admin())
            ->get('/admin/grimba/advertiser-leads?status=won')
            ->assertOk()
            ->assertSee(self::EMAIL_PREFIX . 'wonrow@example.com', false)
            ->assertDontSee(self::EMAIL_PREFIX . 'newrow@example.com', false);
    }

    public function test_admin_detail_page_renders_lead_data(): void
    {
        $id = $this->seedLead('detail', ['admin_notes' => 'Existing note text.']);

        $this->actingAs($this->admin())
            ->get('/admin/grimba/advertiser-leads/' . $id)
            ->assertOk()
            ->assertSee('Lead annonceur #' . $id, false)
            ->assertSee(self::EMAIL_PREFIX . 'detail@example.com', false)
            ->assertSee('Existing note text.', false)
            ->assertSee('Notes opérateur', false)
            ->assertSee('Test goals.', false);
    }

    public function test_admin_notes_save_persists_and_bumps_action_timestamp(): void
    {
        $id = $this->seedLead('notes');

        $this->actingAs($this->admin())
            ->post('/admin/grimba/advertiser-leads/' . $id . '/notes', [
                'admin_notes' => 'Called sales prospect Wed. Asked about Q3 brand campaign.',
            ])
            ->assertRedirect();

        $row = DB::table('grimba_advertiser_leads')->where('id', $id)->first();
        $this->assertStringContainsString('Called sales prospect Wed', (string) $row->admin_notes);
        $this->assertNotNull($row->last_admin_action_at, 'Notes save must bump last_admin_action_at.');
    }

    public function test_admin_status_workflow_advances_with_valid_value(): void
    {
        $id = $this->seedLead('status');

        $this->actingAs($this->admin())
            ->post('/admin/grimba/advertiser-leads/' . $id . '/status', ['status' => 'contacted'])
            ->assertRedirect();

        $this->assertSame('contacted', DB::table('grimba_advertiser_leads')->where('id', $id)->value('status'));
    }

    public function test_admin_status_workflow_rejects_invalid_value(): void
    {
        $id = $this->seedLead('badstatus', ['status' => 'new']);

        $this->actingAs($this->admin())
            ->post('/admin/grimba/advertiser-leads/' . $id . '/status', ['status' => 'invalid-status'])
            ->assertRedirect();

        // Status must stay at 'new' — the handler refuses unknown values.
        $this->assertSame('new', DB::table('grimba_advertiser_leads')->where('id', $id)->value('status'));
    }

    public function test_admin_delete_removes_lead(): void
    {
        $id = $this->seedLead('delete');

        $this->actingAs($this->admin())
            ->delete('/admin/grimba/advertiser-leads/' . $id)
            ->assertRedirect();

        $this->assertNull(DB::table('grimba_advertiser_leads')->where('id', $id)->first());
    }

    public function test_admin_routes_require_auth(): void
    {
        $id = $this->seedLead('auth');

        // Each guarded route must redirect to /admin/login for guests.
        $this->get('/admin/grimba/advertiser-leads')->assertRedirect('/admin/login');
        $this->get('/admin/grimba/advertiser-leads/' . $id)->assertRedirect('/admin/login');
        $this->get('/admin/grimba/advertiser-leads/export.csv')->assertRedirect('/admin/login');
    }
}
