<?php

namespace Tests\Unit;

use App\Support\GrimbaLanguageSettings;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * S-LSAT-03 — pin the contract of the `grimba_lang_*` settings reader.
 *
 * The class is the single source of truth for ~13 reader-locale + rule-
 * engine settings keys. If any default changes shape (bool → int,
 * threshold value), this test catches the drift before reader pages
 * silently start surfacing the wrong content.
 */
class GrimbaLanguageSettingsTest extends TestCase
{
    /** @var array<string, string> */
    private array $originalSettings = [];

    protected function setUp(): void
    {
        parent::setUp();
        // Snapshot every grimba_lang_* key so each test starts from
        // a clean default state. The SettingStore is global; without
        // this isolation, tests leak into each other.
        foreach (array_keys(GrimbaLanguageSettings::defaults()) as $key) {
            $this->originalSettings['grimba_lang_' . $key] = (string) setting('grimba_lang_' . $key, '');
            setting()->set('grimba_lang_' . $key, '');
        }
        setting()->save();
        GrimbaLanguageSettings::flush();
    }

    protected function tearDown(): void
    {
        foreach ($this->originalSettings as $key => $value) {
            setting()->set($key, $value);
        }
        setting()->save();
        GrimbaLanguageSettings::flush();
        parent::tearDown();
    }

    public function test_defaults_match_documented_contract(): void
    {
        $defaults = GrimbaLanguageSettings::defaults();
        $this->assertTrue($defaults['strict_surface']);
        $this->assertTrue($defaults['strict_home']);
        $this->assertTrue($defaults['strict_breaking']);
        $this->assertTrue($defaults['strict_latest']);
        $this->assertTrue($defaults['strict_dossiers']);
        $this->assertFalse($defaults['strict_category'], 'category stays soft to avoid empty-page regressions on thin categories');
        $this->assertFalse($defaults['strict_search'], 'search stays comprehensive');
        $this->assertSame(500, $defaults['popularity_threshold']);
        $this->assertSame(100, $defaults['popularity_threshold_africa']);
        $this->assertSame('africa', $defaults['region_force_both']);
        $this->assertSame(500, $defaults['rule_engine_daily_cap']);
        $this->assertTrue($defaults['rule_engine_enabled']);
        $this->assertTrue($defaults['tail_expander_enabled']);
    }

    public function test_master_switch_off_disables_all_per_surface_strict_modes(): void
    {
        setting()->set('grimba_lang_strict_surface', '0');
        setting()->save();
        GrimbaLanguageSettings::flush();

        $this->assertFalse(GrimbaLanguageSettings::strictSurface());
        $this->assertFalse(GrimbaLanguageSettings::strictForHome());
        $this->assertFalse(GrimbaLanguageSettings::strictForBreaking());
        $this->assertFalse(GrimbaLanguageSettings::strictForLatest());
        $this->assertFalse(GrimbaLanguageSettings::strictForDossiers());
    }

    public function test_strict_for_dispatches_by_surface_name(): void
    {
        // With defaults: home/breaking/latest/dossiers = strict, category/search = soft.
        $this->assertTrue(GrimbaLanguageSettings::strictFor('home'));
        $this->assertTrue(GrimbaLanguageSettings::strictFor('breaking'));
        $this->assertTrue(GrimbaLanguageSettings::strictFor('latest'));
        $this->assertTrue(GrimbaLanguageSettings::strictFor('dossiers'));
        $this->assertTrue(GrimbaLanguageSettings::strictFor('comparatif'), 'comparatif must alias to dossiers');
        $this->assertFalse(GrimbaLanguageSettings::strictFor('category'));
        $this->assertFalse(GrimbaLanguageSettings::strictFor('blog'), 'blog must alias to category');
        $this->assertFalse(GrimbaLanguageSettings::strictFor('search'));
    }

    public function test_strict_for_unknown_surface_falls_back_to_master_switch(): void
    {
        $this->assertTrue(GrimbaLanguageSettings::strictFor('unknown-surface-xyz'));
    }

    public function test_popularity_threshold_clamps_invalid_values(): void
    {
        setting()->set('grimba_lang_popularity_threshold', '1');
        setting()->save();
        GrimbaLanguageSettings::flush();
        $this->assertSame(
            10,
            GrimbaLanguageSettings::popularityThreshold(),
            'Floor must be 10 to prevent admin-form abuse triggering a full-corpus translation.'
        );

        setting()->set('grimba_lang_popularity_threshold', '999999999');
        setting()->save();
        GrimbaLanguageSettings::flush();
        $this->assertSame(100000, GrimbaLanguageSettings::popularityThreshold(), 'Ceiling clamps to 100k.');
    }

    public function test_effective_threshold_uses_africa_value_for_africa_region(): void
    {
        $this->assertSame(100, GrimbaLanguageSettings::effectivePopularityThreshold('africa'));
        $this->assertSame(500, GrimbaLanguageSettings::effectivePopularityThreshold('europe'));
        $this->assertSame(500, GrimbaLanguageSettings::effectivePopularityThreshold('americas'));
        $this->assertSame(500, GrimbaLanguageSettings::effectivePopularityThreshold(null));
        $this->assertSame(500, GrimbaLanguageSettings::effectivePopularityThreshold(''));
    }

    public function test_force_both_regions_parses_csv_with_trimming_and_lowercase(): void
    {
        setting()->set('grimba_lang_region_force_both', ' Africa , Americas ,  ,Europe');
        setting()->save();
        GrimbaLanguageSettings::flush();

        $regions = GrimbaLanguageSettings::forceBothRegions();
        $this->assertSame(['africa', 'americas', 'europe'], $regions);
    }

    public function test_force_both_regions_default_is_africa(): void
    {
        $this->assertSame(['africa'], GrimbaLanguageSettings::forceBothRegions());
    }

    public function test_force_both_regions_none_sentinel_disables_rule(): void
    {
        // Zen audit fix 2026-05-18: operators can disable the
        // forced-region rule by setting the value to `none`.
        setting()->set('grimba_lang_region_force_both', 'none');
        setting()->save();
        GrimbaLanguageSettings::flush();

        $this->assertSame([], GrimbaLanguageSettings::forceBothRegions());

        // Africa post → uses default (non-Africa) threshold when no
        // region is in the force-both list.
        $this->assertSame(500, GrimbaLanguageSettings::effectivePopularityThreshold('africa'));
    }

    public function test_boolean_coercion_handles_string_truthy_values(): void
    {
        foreach (['1', 'true', 'yes', 'on'] as $truthy) {
            setting()->set('grimba_lang_strict_surface', $truthy);
            setting()->save();
            GrimbaLanguageSettings::flush();
            $this->assertTrue(GrimbaLanguageSettings::strictSurface(), "'{$truthy}' must coerce to TRUE");
        }
        foreach (['0', 'false', 'no', 'off', ''] as $falsy) {
            setting()->set('grimba_lang_strict_surface', $falsy);
            setting()->save();
            GrimbaLanguageSettings::flush();
            // Empty string falls back to the default (which is true) per
            // the `coerce()` rule — that's deliberate to avoid an
            // accidental "" wipe disabling the feature.
            if ($falsy === '') {
                $this->assertTrue(GrimbaLanguageSettings::strictSurface(), 'empty string keeps default');
            } else {
                $this->assertFalse(GrimbaLanguageSettings::strictSurface(), "'{$falsy}' must coerce to FALSE");
            }
        }
    }

    public function test_flush_clears_both_in_memory_and_durable_cache(): void
    {
        // Prime the cache, then mutate the setting, then assert pre-flush
        // returns the stale value and post-flush returns the new value.
        setting()->set('grimba_lang_popularity_threshold', '750');
        setting()->save();
        GrimbaLanguageSettings::flush();

        $this->assertSame(750, GrimbaLanguageSettings::popularityThreshold());

        setting()->set('grimba_lang_popularity_threshold', '1500');
        setting()->save();
        // Without flush, the cached value persists.
        $this->assertSame(750, GrimbaLanguageSettings::popularityThreshold(), 'In-memory cache must hold');

        GrimbaLanguageSettings::flush();
        $this->assertSame(1500, GrimbaLanguageSettings::popularityThreshold());
    }
}
