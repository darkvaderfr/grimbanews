<?php

namespace Tests\Unit;

use App\Support\GrimbaLanguageSettings;
use App\Support\GrimbaTranslationRules;
use Tests\TestCase;

/**
 * S-LSAT-09 — pin the rule engine's contract.
 *
 * The engine is pure (no DB, no IO). The tests pass synthetic
 * stdClass rows with `original_language` / `editorial_region` /
 * `views` / `translated_to` set, and assert the Decision matches
 * the documented matrix.
 *
 * Setup/tearDown snapshots all `grimba_lang_*` settings so tests
 * can mutate freely without polluting each other.
 */
class GrimbaTranslationRulesTest extends TestCase
{
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

    private function makePost(array $overrides): object
    {
        return (object) array_merge([
            'id' => 1,
            'original_language' => 'fr',
            'translated_to' => null,
            'views' => 0,
            'editorial_region' => 'europe',
        ], $overrides);
    }

    public function test_post_without_language_returns_no_translate(): void
    {
        $d = GrimbaTranslationRules::decide($this->makePost(['original_language' => null]));
        $this->assertFalse($d->shouldTranslate);
        $this->assertNull($d->targetLocale);
        $this->assertSame('no-detectable-language', $d->reason);
    }

    public function test_post_already_translated_skipped(): void
    {
        $d = GrimbaTranslationRules::decide($this->makePost([
            'original_language' => 'fr',
            'translated_to' => 'en',
            'views' => 9999,
        ]));
        $this->assertFalse($d->shouldTranslate);
        $this->assertSame('en', $d->targetLocale);
        $this->assertSame('already-translated', $d->reason);
    }

    public function test_le_monde_at_500_views_translates_to_en(): void
    {
        // The exact scenario Vader described: a French post hits the
        // global popularity threshold and gets translated to English.
        $d = GrimbaTranslationRules::decide($this->makePost([
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'views' => 500,
        ]));
        $this->assertTrue($d->shouldTranslate);
        $this->assertSame('en', $d->targetLocale);
        $this->assertSame(1, $d->priority);
        $this->assertStringContainsString('popularity-threshold', $d->reason);
    }

    public function test_le_monde_at_499_views_does_not_translate(): void
    {
        $d = GrimbaTranslationRules::decide($this->makePost([
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'views' => 499,
        ]));
        $this->assertFalse($d->shouldTranslate);
        $this->assertStringContainsString('below-threshold', $d->reason);
    }

    public function test_africa_region_uses_lower_threshold(): void
    {
        $d = GrimbaTranslationRules::decide($this->makePost([
            'original_language' => 'fr',
            'editorial_region' => 'africa',
            'views' => 100,
        ]));
        $this->assertTrue($d->shouldTranslate);
        $this->assertSame('en', $d->targetLocale);
        $this->assertStringContainsString('force-both-region:africa', $d->reason);
    }

    public function test_africa_post_at_99_views_does_not_translate(): void
    {
        $d = GrimbaTranslationRules::decide($this->makePost([
            'original_language' => 'fr',
            'editorial_region' => 'africa',
            'views' => 99,
        ]));
        $this->assertFalse($d->shouldTranslate);
        $this->assertStringContainsString('region-forced-below-threshold', $d->reason);
    }

    public function test_english_post_targets_french(): void
    {
        $d = GrimbaTranslationRules::decide($this->makePost([
            'original_language' => 'en',
            'editorial_region' => 'americas',
            'views' => 5000,
        ]));
        $this->assertTrue($d->shouldTranslate);
        $this->assertSame('fr', $d->targetLocale);
    }

    public function test_operator_can_set_lower_threshold_for_global_rule(): void
    {
        setting()->set('grimba_lang_popularity_threshold', '100');
        setting()->save();
        GrimbaLanguageSettings::flush();

        $d = GrimbaTranslationRules::decide($this->makePost([
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'views' => 100,
        ]));
        $this->assertTrue($d->shouldTranslate);
    }

    public function test_select_translatable_respects_daily_cap(): void
    {
        setting()->set('grimba_lang_rule_engine_daily_cap', '3');
        setting()->save();
        GrimbaLanguageSettings::flush();

        // 5 africa posts above the africa threshold.
        $posts = array_map(fn ($i) => $this->makePost([
            'id' => $i,
            'original_language' => 'fr',
            'editorial_region' => 'africa',
            'views' => 200,
        ]), range(1, 5));

        $picked = GrimbaTranslationRules::selectTranslatable($posts, 0);
        $this->assertCount(3, $picked, 'Daily cap of 3 must bound the work set.');
    }

    public function test_select_translatable_subtracts_already_today(): void
    {
        setting()->set('grimba_lang_rule_engine_daily_cap', '5');
        setting()->save();
        GrimbaLanguageSettings::flush();

        $posts = array_map(fn ($i) => $this->makePost([
            'id' => $i,
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'views' => 1000,
        ]), range(1, 10));

        // 4 already burned today → only 1 slot remains.
        $picked = GrimbaTranslationRules::selectTranslatable($posts, 4);
        $this->assertCount(1, $picked);
    }

    public function test_select_translatable_empty_when_cap_burned(): void
    {
        setting()->set('grimba_lang_rule_engine_daily_cap', '5');
        setting()->save();
        GrimbaLanguageSettings::flush();

        $posts = [$this->makePost(['views' => 1000])];
        $picked = GrimbaTranslationRules::selectTranslatable($posts, 10);
        $this->assertSame([], $picked);
    }

    public function test_force_both_regions_none_sentinel_disables_africa_rule(): void
    {
        setting()->set('grimba_lang_region_force_both', 'none');
        setting()->save();
        GrimbaLanguageSettings::flush();

        // Africa post at 100 views: with the `none` sentinel, the
        // africa rule is OFF — falls back to the global 500 rule.
        $d = GrimbaTranslationRules::decide($this->makePost([
            'original_language' => 'fr',
            'editorial_region' => 'africa',
            'views' => 100,
        ]));
        $this->assertFalse($d->shouldTranslate);
        $this->assertStringContainsString('below-threshold', $d->reason);
    }
}
