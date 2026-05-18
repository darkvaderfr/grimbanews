<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-CAT-08 — release smoke for the category-anchoring band.
 * Locks the cross-surface consistency Waves DDDD + EEEE shipped.
 *
 * Vader's directive: "each article is within its category (ie
 * culture, politics, sports etc.) even for breaking news, top
 * stories, latest stories." A regression that strips the badge
 * from any rail breaks this contract — these tests fail loud.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaCategoryBadgeSmokeTest extends TestCase
{
    public function test_home_renders_category_badge_partial(): void
    {
        $html = $this->get('/')
            ->assertOk()
            ->getContent();
        // The badge partial emits a stable data-attribute selector
        // we can match without depending on category names.
        $this->assertStringContainsString(
            'data-grimba-cat-badge',
            $html,
            'Home must render at least one category badge.',
        );
    }

    public function test_home_renders_multiple_distinct_topic_badges(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // Count distinct badge labels. We expect at least 3 different
        // topics across the rails — the home shows hero + briefing +
        // topNews + most-read + latest, all primaryTopicFor()-driven.
        preg_match_all(
            '#data-grimba-cat-badge[^>]*>\s*(?:<i[^>]*></i>\s*)?<span>([^<]+)</span>#',
            $html,
            $matches,
        );

        $labels = array_unique($matches[1] ?? []);
        $this->assertGreaterThanOrEqual(
            3,
            count($labels),
            'Home must show at least 3 distinct topic categories. Got: ' . implode(', ', $labels),
        );
    }

    public function test_no_regional_category_leaks_into_badge(): void
    {
        // primaryTopicFor() must skip Europe/Afrique/Amériques/International.
        // If any of these labels show in a badge, the helper has a bug.
        $html = $this->get('/')->assertOk()->getContent();

        preg_match_all(
            '#data-grimba-cat-badge[^>]*>\s*(?:<i[^>]*></i>\s*)?<span>([^<]+)</span>#',
            $html,
            $matches,
        );

        $regionalBins = ['Afrique', 'Europe', 'Amériques', 'International'];
        foreach (($matches[1] ?? []) as $label) {
            $clean = html_entity_decode(trim($label), ENT_QUOTES | ENT_HTML5);
            $this->assertNotContains(
                $clean,
                $regionalBins,
                "Badge label '{$clean}' is a regional bin — primaryTopicFor() must skip these.",
            );
        }
    }

    public function test_no_housekeeping_category_leaks_into_badge(): void
    {
        // À la une, Trusted Source Credibility, Unclassified Source
        // Bias are editorial housekeeping — never reader-facing.
        $html = $this->get('/')->assertOk()->getContent();

        preg_match_all(
            '#data-grimba-cat-badge[^>]*>\s*(?:<i[^>]*></i>\s*)?<span>([^<]+)</span>#',
            $html,
            $matches,
        );

        $housekeeping = ['À la une', 'Trusted Source Credibility', 'Unclassified Source Bias'];
        foreach (($matches[1] ?? []) as $label) {
            $clean = html_entity_decode(trim($label), ENT_QUOTES | ENT_HTML5);
            $this->assertNotContains(
                $clean,
                $housekeeping,
                "Badge label '{$clean}' is housekeeping — primaryTopicFor() must skip.",
            );
        }
    }

    public function test_badge_styles_ship_via_at_once_block(): void
    {
        // The badge partial's @once style block must appear on the
        // page (light + dark variants + sm modifier all defined).
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('.grimba-cat-badge', $html);
        $this->assertStringContainsString('.grimba-cat-badge--sm', $html);
        $this->assertStringContainsString('.grimba-cat-badge--dark', $html);
    }

    public function test_breaking_does_not_render_topic_badge_yet(): void
    {
        // S-CAT-01/02 wired hero + briefing + most-read + topNews +
        // latest on the HOME page. /breaking is a separate template
        // that doesn't include the badge yet (queued for S-CAT-03+).
        // This test pins that scoping so a future refactor that
        // accidentally extends to breaking still passes — but if a
        // FUTURE wave deliberately adds the badge there, this test
        // gets removed in that commit.
        $html = $this->get('/breaking')->assertOk()->getContent();
        $this->assertStringNotContainsString(
            'data-grimba-cat-badge',
            $html,
            'Badge on /breaking was not in S-CAT-01/02 scope. If a later wave adds it, remove this test in the same commit.',
        );
    }
}
