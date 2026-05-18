<?php

namespace Tests\Feature;

use App\Console\Commands\GrimbaTranslateByRule;
use App\Support\GrimbaLanguageSettings;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * S-LSAT-10 — pin the artisan command's selection + cap behavior.
 *
 * The command bridges GrimbaTranslationRules (pure-function decisions)
 * and the actual provider chain. These tests focus on the SELECTION
 * + CAP semantics — the translator call itself is exercised
 * indirectly via the wider GrimbaTranslator suite + the dry-run path.
 *
 * Fixture posts live in a dedicated ID range (993xxx) to avoid
 * collisions with the live corpus + other test files.
 */
class GrimbaTranslateByRuleCommandTest extends TestCase
{
    private const FIXTURE_BASE = 993000;

    /** @var array<string, string> */
    private array $originalSettings = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (array_keys(GrimbaLanguageSettings::defaults()) as $key) {
            $this->originalSettings['grimba_lang_' . $key] = (string) setting('grimba_lang_' . $key, '');
            setting()->set('grimba_lang_' . $key, '');
        }
        setting()->save();
        GrimbaLanguageSettings::flush();

        // Wipe the daily-cap cache between tests so each test starts
        // from 0/cap.
        Cache::forget('grimba_rule_engine_calls:' . now()->format('Y-m-d'));

        // Clean fixture posts.
        DB::table('posts')->where('id', '>=', self::FIXTURE_BASE)
            ->where('id', '<', self::FIXTURE_BASE + 1000)
            ->delete();
    }

    protected function tearDown(): void
    {
        DB::table('posts')->where('id', '>=', self::FIXTURE_BASE)
            ->where('id', '<', self::FIXTURE_BASE + 1000)
            ->delete();
        Cache::forget('grimba_rule_engine_calls:' . now()->format('Y-m-d'));
        foreach ($this->originalSettings as $key => $value) {
            setting()->set($key, $value);
        }
        setting()->save();
        GrimbaLanguageSettings::flush();
        parent::tearDown();
    }

    private function insertFixture(int $id, array $overrides): int
    {
        $now = now();
        $defaults = [
            'id' => $id,
            'name' => 'Fixture post ' . $id,
            'description' => 'Body.',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Fixture Wire',
            'source_id' => 1,
            'original_language' => 'fr',
            'translated_to' => null,
            'editorial_region' => 'europe',
            'views' => 0,
            'translation_priority' => 0,
            'published_at' => $now->copy()->subHours(1),
            'created_at' => $now->copy()->subHours(1),
            'updated_at' => $now->copy()->subHours(1),
        ];
        DB::table('posts')->insert(array_merge($defaults, $overrides));
        return $id;
    }

    public function test_dry_run_does_not_call_providers_or_mutate(): void
    {
        $id = $this->insertFixture(self::FIXTURE_BASE + 1, [
            'name' => 'Le Monde — 800 views post',
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'views' => 800,
        ]);

        $this->artisan('grimba:translate-by-rule', ['--dry-run' => true, '--limit' => 10])
            ->assertExitCode(0);

        // Row must remain un-translated, priority untouched.
        $row = DB::table('posts')->where('id', $id)->first();
        $this->assertNull($row->translated_to);
        $this->assertSame(0, (int) $row->translation_priority);
        $this->assertSame(0, GrimbaTranslateByRule::callsToday(), 'Dry run must not burn the daily cap.');
    }

    public function test_disabled_flag_short_circuits(): void
    {
        setting()->set('grimba_lang_rule_engine_enabled', '0');
        setting()->save();
        GrimbaLanguageSettings::flush();

        $id = $this->insertFixture(self::FIXTURE_BASE + 2, [
            'original_language' => 'fr',
            'views' => 5000,
        ]);

        $this->artisan('grimba:translate-by-rule', ['--dry-run' => true])
            ->expectsOutputToContain('disabled')
            ->assertExitCode(0);
    }

    public function test_daily_cap_zero_short_circuits_non_dry_run(): void
    {
        $this->insertFixture(self::FIXTURE_BASE + 3, [
            'original_language' => 'fr',
            'views' => 5000,
        ]);

        // Pretend we've already burned the cap today.
        Cache::put(
            'grimba_rule_engine_calls:' . now()->format('Y-m-d'),
            1000,
            now()->addHours(36),
        );

        $this->artisan('grimba:translate-by-rule')
            ->expectsOutputToContain('daily cap')
            ->assertExitCode(0);
    }

    public function test_call_counter_persists_via_cache_key(): void
    {
        GrimbaTranslateByRule::recordCall(3);
        $this->assertSame(3, GrimbaTranslateByRule::callsToday());
        GrimbaTranslateByRule::recordCall(2);
        $this->assertSame(5, GrimbaTranslateByRule::callsToday());
    }

    public function test_fixture_low_view_post_stays_unpriotized(): void
    {
        $id = $this->insertFixture(self::FIXTURE_BASE + 4, [
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'views' => 100, // below the 500 default
        ]);

        // Note: command runs over the live corpus too, which has
        // posts above the 500 threshold — we can't assert global
        // output absence. But we CAN assert that our specific
        // fixture row was not touched.
        $this->artisan('grimba:translate-by-rule', ['--dry-run' => true, '--limit' => 200])
            ->assertExitCode(0);

        $row = DB::table('posts')->where('id', $id)->first();
        $this->assertNull($row->translated_to);
        $this->assertSame(0, (int) $row->translation_priority, 'Low-view fixture must NOT have its priority bumped.');
    }
}
