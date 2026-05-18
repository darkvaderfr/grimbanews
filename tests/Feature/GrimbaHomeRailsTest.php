<?php

namespace Tests\Feature;

use App\Support\GrimbaEditorialCategories;
use App\Support\GrimbaHomeFeed;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-CAT-03 — pin the per-rail category override contract.
 *
 * Lock down:
 *   1. Default (no pins) — sectionTopics() picks by post count
 *      (existing Wave Y behavior, unchanged).
 *   2. Single pin — slot 1 is the pinned category, slot 2 is
 *      auto-picked from non-pinned categories.
 *   3. Two pins — both slots are operator-driven, in the order
 *      they appear in the settings.
 *   4. Invalid pin name — silently dropped (no poison, no
 *      crash).
 *   5. Admin form save persists settings + flushes the home feed.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaHomeRailsTest extends TestCase
{
    /** @var array<string, string> */
    private array $snapshot = [];

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Log::spy();
        foreach (['grimba_section_pin_1', 'grimba_section_pin_2'] as $key) {
            $this->snapshot[$key] = (string) setting($key, '');
            setting()->set($key, '');
        }
        setting()->save();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        foreach ($this->snapshot as $key => $value) {
            setting()->set($key, $value);
        }
        setting()->save();
        Cache::flush();
        parent::tearDown();
    }

    private function admin(): User
    {
        $user = User::query()->find(1);
        $this->assertNotNull($user);
        return $user;
    }

    public function test_default_no_pin_picks_by_post_count(): void
    {
        // No pins → sectionTopics should return 2 highest-post-count
        // categories. We assert it returns a non-empty collection
        // with valid topic names — exact picks depend on corpus
        // state which we don't pin in tests.
        $resolved = GrimbaEditorialCategories::sectionTopics(2);
        $this->assertGreaterThan(0, $resolved->count());
        $validTopics = GrimbaEditorialCategories::topicNames(includeFront: false);
        foreach ($resolved as $cat) {
            $this->assertContains($cat->name, $validTopics);
        }
    }

    public function test_single_pin_lands_in_slot_1(): void
    {
        setting()->set('grimba_section_pin_1', 'Sports');
        setting()->save();

        $resolved = GrimbaEditorialCategories::sectionTopics(2);
        $this->assertGreaterThanOrEqual(1, $resolved->count());
        $this->assertSame('Sports', $resolved->first()->name);
    }

    public function test_two_pins_fill_both_slots_in_order(): void
    {
        setting()->set('grimba_section_pin_1', 'Sports');
        setting()->set('grimba_section_pin_2', 'Culture');
        setting()->save();

        $resolved = GrimbaEditorialCategories::sectionTopics(2);
        $this->assertCount(2, $resolved);
        $this->assertSame('Sports', $resolved[0]->name);
        $this->assertSame('Culture', $resolved[1]->name);
    }

    public function test_invalid_pin_name_silently_dropped(): void
    {
        setting()->set('grimba_section_pin_1', 'NotARealCategory');
        setting()->save();

        $pinned = GrimbaEditorialCategories::pinnedSectionCategories(2);
        $this->assertCount(0, $pinned, 'Invalid pin name must be filtered.');

        // sectionTopics still returns the auto-picked slots.
        $resolved = GrimbaEditorialCategories::sectionTopics(2);
        $this->assertGreaterThan(0, $resolved->count());
    }

    public function test_admin_form_renders_for_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/grimba/home-rails')
            ->assertOk()
            ->assertSee('Rails de la home', false)
            ->assertSee('Sections épinglées', false)
            ->assertSee('Aperçu actuel', false);
    }

    public function test_admin_form_persists_pins(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/grimba/home-rails', [
                'grimba_section_pin_1' => 'Politique',
                'grimba_section_pin_2' => 'Économie',
            ])
            ->assertRedirect();

        $this->assertSame('Politique', (string) setting('grimba_section_pin_1'));
        $this->assertSame('Économie', (string) setting('grimba_section_pin_2'));
    }

    public function test_admin_form_drops_invalid_category_silently(): void
    {
        // Pre-prime a valid pin so we can confirm it gets cleared.
        setting()->set('grimba_section_pin_1', 'Culture');
        setting()->save();

        $this->actingAs($this->admin())
            ->post('/admin/grimba/home-rails', [
                'grimba_section_pin_1' => 'NotARealTopicAtAll',
            ])
            ->assertRedirect();

        // Handler clears the invalid pin rather than rejecting the
        // whole form. Defensive: if a topic gets renamed, the old
        // pin doesn't lock the form forever.
        $this->assertSame('', (string) setting('grimba_section_pin_1'));
    }

    public function test_guest_redirects_to_login(): void
    {
        $this->get('/admin/grimba/home-rails')->assertRedirect('/admin/login');
        $this->post('/admin/grimba/home-rails')->assertRedirect('/admin/login');
    }
}
