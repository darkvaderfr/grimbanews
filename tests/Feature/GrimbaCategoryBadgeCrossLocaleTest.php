<?php

namespace Tests\Feature;

use App\Support\GrimbaEditorialCategories;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-CAT-09 + S-CAT-10 — pin the cross-locale + cross-surface
 * release contract for category badges.
 *
 * Two contracts to lock:
 *
 *   1. CROSS-LOCALE: the badge renders in BOTH ?lang=fr and
 *      ?lang=en. The badge body is `{{ __($name) }}` so locale
 *      affects the rendered text. A regression that breaks the
 *      translation lookup fails this suite.
 *
 *   2. CROSS-SURFACE AGREEMENT: every public reader surface
 *      routes through `primaryTopicFor()`. Adding a new card
 *      surface that bypasses the helper would NOT fail any
 *      individual surface's badge-presence test, but WOULD fail
 *      this band-level test if it surfaced a regional/housekeeping
 *      label.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaCategoryBadgeCrossLocaleTest extends TestCase
{
    /** @return array<int, array{0: string}> */
    public static function readerSurfaces(): array
    {
        return [
            ['/'],
            ['/breaking'],
            ['/latest'],
            ['/dossiers'],
        ];
    }

    /**
     * @dataProvider readerSurfaces
     */
    public function test_surface_renders_badges_in_french(string $path): void
    {
        $html = $this->get($path . (str_contains($path, '?') ? '&' : '?') . 'lang=fr')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            'data-grimba-cat-badge',
            $html,
            "{$path}?lang=fr must render at least one category badge.",
        );
    }

    /**
     * @dataProvider readerSurfaces
     */
    public function test_surface_renders_badges_in_english(string $path): void
    {
        $html = $this->get($path . (str_contains($path, '?') ? '&' : '?') . 'lang=en')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            'data-grimba-cat-badge',
            $html,
            "{$path}?lang=en must render at least one category badge.",
        );
    }

    public function test_band_release_smoke_no_skip_list_leaks_anywhere(): void
    {
        // The band's load-bearing invariant: NO badge anywhere on
        // any surface in any locale shows a regional bin or
        // housekeeping bucket. Test the full matrix.
        $surfaces = ['/', '/breaking', '/latest', '/dossiers'];
        $locales  = ['fr', 'en'];

        $regional = GrimbaEditorialCategories::editionNames();
        $housekeeping = array_merge(GrimbaEditorialCategories::internalReviewNames(), ['À la une']);
        $blacklist = array_merge($regional, $housekeeping);

        $totalBadgesSeen = 0;
        foreach ($surfaces as $surface) {
            foreach ($locales as $locale) {
                $url = $surface . (str_contains($surface, '?') ? '&' : '?') . 'lang=' . $locale;
                $html = $this->get($url)->assertOk()->getContent();

                preg_match_all(
                    '#data-grimba-cat-badge[^>]*>\s*(?:<i[^>]*></i>\s*)?<span>([^<]+)</span>#',
                    $html,
                    $matches,
                );

                $labels = $matches[1] ?? [];
                $totalBadgesSeen += count($labels);

                foreach ($labels as $label) {
                    $clean = html_entity_decode(trim($label), ENT_QUOTES | ENT_HTML5);
                    $this->assertNotContains(
                        $clean,
                        $blacklist,
                        "Badge '{$clean}' leaked onto {$url} — must be filtered by primaryTopicFor() skip-list.",
                    );
                }
            }
        }

        $this->assertGreaterThan(
            8,
            $totalBadgesSeen,
            'Across 4 surfaces × 2 locales = 8 page loads, the corpus should produce more than 8 badges.',
        );
    }
}
