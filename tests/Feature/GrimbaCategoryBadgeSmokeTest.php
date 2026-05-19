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

    public function test_breaking_renders_category_badge(): void
    {
        // Wave HHHH (S-CAT-02b) extended the badge to /breaking.
        // At least one badge must render on the page.
        $html = $this->get('/breaking')->assertOk()->getContent();
        $this->assertStringContainsString(
            'data-grimba-cat-badge',
            $html,
            '/breaking must render at least one category badge after Wave HHHH.',
        );
    }

    public function test_latest_renders_category_badge(): void
    {
        // Wave HHHH — same extension for /latest.
        $html = $this->get('/latest')->assertOk()->getContent();
        $this->assertStringContainsString('data-grimba-cat-badge', $html);
    }

    public function test_dossiers_renders_majority_topic_badge(): void
    {
        // Wave IIII — /dossiers attaches `primary_topic` per visible
        // cluster via a single grouped query and renders a badge on
        // each card. The post-shaped wrapper synthesizes the
        // expected `categories` collection so the shared partial
        // doesn't need a special code path.
        $html = $this->get('/dossiers')->assertOk()->getContent();
        $this->assertStringContainsString(
            'data-grimba-cat-badge',
            $html,
            '/dossiers must render majority-topic badges per cluster after Wave IIII.',
        );
    }

    /**
     * Wave RRRR (Vader 2026-05-18) — `$topic->url ?? ''` shorted to
     * empty string via Eloquent's `__isset` (which routes through
     * `getAttribute()` and bypasses the MacroableModels macro). The
     * fix reads `$topic->url` directly, letting `__get` fire the
     * macro chain. Lock the contract: every rendered category badge
     * on home / breaking / latest carries a real `http(s)://…` href,
     * not the empty-string regression.
     */
    public function test_home_clickable_badges_have_real_hrefs(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        // Strict badge count: class="grimba-cat-badge…" appears
        // exactly once per rendered badge tag (not in the JS literal).
        preg_match_all('#class="grimba-cat-badge#', $html, $badges);
        $totalBadges = count($badges[0]);
        $this->assertGreaterThan(
            0,
            $totalBadges,
            'Home must render at least one badge for this contract to be meaningful.'
        );
        // Count badges with a resolved http(s) href, and verify
        // none of them link to the bare homepage (which the macro
        // returns as a fallback when slug is missing — would be a
        // misleading clickable badge).
        preg_match_all('#data-grimba-cat-badge-href="([^"]+)"#', $html, $hrefs);
        $homeUrl = rtrim((string) \Botble\Base\Facades\BaseHelper::getHomepageUrl(), '/');
        $realCategoryLinks = array_filter(
            $hrefs[1] ?? [],
            fn (string $url): bool => rtrim($url, '/') !== $homeUrl
        );
        $this->assertSame(
            $totalBadges,
            count($realCategoryLinks),
            "Wave RRRR regression: home rendered {$totalBadges} badges but only " . count($realCategoryLinks) . " had real category URLs. The `??` operator on Eloquent macro-backed attributes is the usual culprit; the no-slug homepage fallback is the second."
        );
    }

    public function test_breaking_clickable_badges_have_real_hrefs(): void
    {
        $html = $this->get('/breaking')->assertOk()->getContent();
        preg_match_all('#class="grimba-cat-badge#', $html, $badges);
        $totalBadges = count($badges[0]);
        if ($totalBadges === 0) {
            $this->markTestSkipped('/breaking surface has no badges this run.');
        }
        preg_match_all('#data-grimba-cat-badge-href="https?://#', $html, $hrefs);
        $this->assertSame(
            $totalBadges,
            count($hrefs[0]),
            "/breaking rendered {$totalBadges} badges but " . count($hrefs[0]) . " were clickable."
        );
    }

    public function test_dossiers_clickable_badges_have_real_hrefs(): void
    {
        // Wave SSSS (Vader 2026-05-18) — dossier majority-vote helper
        // now passes a real Botble\Blog\Models\Category with slugable
        // eager-loaded (vs the synthesized stdClass from Wave IIII).
        // Every dossier card badge should be clickable to the category
        // listing, consistent with home / breaking / latest.
        $html = $this->get('/dossiers')->assertOk()->getContent();
        preg_match_all('#class="grimba-cat-badge#', $html, $badges);
        $totalBadges = count($badges[0]);
        if ($totalBadges === 0) {
            $this->markTestSkipped('/dossiers surface has no badges this run.');
        }
        preg_match_all('#data-grimba-cat-badge-href="([^"]+)"#', $html, $hrefs);
        $homeUrl = rtrim((string) \Botble\Base\Facades\BaseHelper::getHomepageUrl(), '/');
        $realCategoryLinks = array_filter(
            $hrefs[1] ?? [],
            fn (string $url): bool => rtrim($url, '/') !== $homeUrl
        );
        $this->assertSame(
            $totalBadges,
            count($realCategoryLinks),
            "/dossiers rendered {$totalBadges} badges but only " . count($realCategoryLinks) . " were clickable. Wave SSSS regression."
        );
    }

    public function test_latest_clickable_badges_have_real_hrefs(): void
    {
        $html = $this->get('/latest')->assertOk()->getContent();
        preg_match_all('#class="grimba-cat-badge#', $html, $badges);
        $totalBadges = count($badges[0]);
        if ($totalBadges === 0) {
            $this->markTestSkipped('/latest surface has no badges this run.');
        }
        preg_match_all('#data-grimba-cat-badge-href="https?://#', $html, $hrefs);
        $this->assertSame(
            $totalBadges,
            count($hrefs[0]),
            "/latest rendered {$totalBadges} badges but " . count($hrefs[0]) . " were clickable."
        );
    }

    public function test_article_detail_page_carries_primary_topic_pill(): void
    {
        // Wave PPPP (S-CAT-02d) — the article detail page's hero
        // card uses primaryTopicFor() so the topic surfaces match
        // what the home / breaking / latest / dossiers cards show
        // for the same post.
        $slug = \Botble\Slug\Models\Slug::query()
            ->where('reference_type', \Botble\Blog\Models\Post::class)
            ->orderByDesc('id')
            ->value('key');
        if (! $slug) {
            $this->markTestSkipped('No published post slug available in the corpus.');
        }
        // The blog/{slug} URL may 301-canonicalize to a region- or
        // category-prefixed URL. Follow the redirect chain (max 3).
        $response = $this->get('/blog/' . $slug);
        $hops = 0;
        while ($response->isRedirect() && $hops < 3) {
            $hops++;
            $loc = $response->headers->get('Location');
            if (! $loc) break;
            $parsed = parse_url($loc);
            $path = ($parsed['path'] ?? '/') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
            $response = $this->get($path);
        }
        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString(
            'grimba-article-card__pill--category',
            $html,
            'Article hero card must render a topic-category pill (Wave PPPP).',
        );
    }

    public function test_article_detail_page_ships_full_news_article_schema(): void
    {
        // Wave TTTTT (Vader 2026-05-19) — article detail page is the
        // highest-traffic surface after home. Its NewsArticle schema
        // is what Google uses to pick the article for News results
        // + Top Stories carousel. Lock the contract so a future
        // refactor doesn't silently strip a required field.
        $slug = \Botble\Slug\Models\Slug::query()
            ->where('reference_type', \Botble\Blog\Models\Post::class)
            ->orderByDesc('id')
            ->value('key');
        if (! $slug) {
            $this->markTestSkipped('No published post slug available in the corpus.');
        }
        $response = $this->get('/blog/' . $slug);
        $hops = 0;
        while ($response->isRedirect() && $hops < 3) {
            $hops++;
            $loc = $response->headers->get('Location');
            if (! $loc) break;
            $parsed = parse_url($loc);
            $path = ($parsed['path'] ?? '/') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
            $response = $this->get($path);
        }
        $response->assertOk();
        $html = $response->getContent();

        // Required: at least one NewsArticle JSON-LD block.
        $this->assertStringContainsString(
            '"NewsArticle"',
            $html,
            'Article page must ship NewsArticle JSON-LD (drives Google News + Top Stories).',
        );

        // Required schema fields per https://schema.org/NewsArticle
        // (Google's minimum for News rich results).
        foreach (['"datePublished"', '"dateModified"', '"headline"', '"mainEntityOfPage"'] as $required) {
            $this->assertStringContainsString(
                $required,
                $html,
                "Article NewsArticle schema missing {$required}."
            );
        }

        // Author + publisher refs needed for the byline attribution
        // card in Google News.
        $this->assertMatchesRegularExpression(
            '/"author"\s*:\s*[{\[]/',
            $html,
            'Article NewsArticle must carry an author block.'
        );
        $this->assertMatchesRegularExpression(
            '/"publisher"\s*:\s*[{\[]/',
            $html,
            'Article NewsArticle must carry a publisher block.'
        );

        // BreadcrumbList for the breadcrumb-card SERP layout.
        $this->assertStringContainsString(
            '"BreadcrumbList"',
            $html,
            'Article page must ship BreadcrumbList JSON-LD.'
        );
    }
}
