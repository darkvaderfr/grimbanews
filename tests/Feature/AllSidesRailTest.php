<?php

namespace Tests\Feature;

use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AllSidesRailTest extends TestCase
{
    /**
     * Vader 2026-05-18 — paid down from test debt. The post-dossier-
     * reinvention rail keeps `grimba-all-sides__card` / `__title` /
     * `__headline` classes and the per-card link still resolves to
     * `/comparatif/{clusterId}`, but the old `-webkit-text-fill-color`
     * dark-mode hack was retired. The rail uses production data; the
     * fixture insert is no longer required because the home selects
     * its all-sides clusters via `GrimbaHomeFeed`, not by clusterId.
     */
    public function test_all_sides_cards_link_to_cluster_comparison_not_blog_index(): void
    {
        $response = $this->get('/')->assertOk();

        $body = $response->getContent();

        // The all-sides rail renders production data — confirm the
        // canonical card markup is present.
        $this->assertStringContainsString('grimba-all-sides__card', $body);
        $this->assertStringContainsString('grimba-all-sides__title', $body);
        $this->assertStringContainsString('grimba-all-sides__headline', $body);

        // Every card link points at the dossier comparison page.
        $this->assertMatchesRegularExpression(
            '#href="[^"]*?/comparatif/\d+"#',
            $body,
            'All-sides cards must link to /comparatif/{clusterId}.'
        );

        // Inline-style anti-patterns from the legacy rail are gone.
        $this->assertStringNotContainsString('onmouseover="this.style.transform', $body);

        // Spot-check the canonical CSS contracts (rail is glass-card
        // + scroll-snap + responsive grid).
        $css = file_get_contents(public_path('themes/echo/css/grimba-home.css'));
        $this->assertStringContainsString('.grimba-all-sides__title', $css);
        $this->assertStringContainsString('.grimba-all-sides__headline', $css);
        $this->assertStringContainsString('html.grimba-home-html[data-bs-theme="dark"] .grimba-all-sides__card', $css);
        $this->assertStringContainsString('scroll-snap-type: x proximity;', $css);
        $this->assertStringContainsString('grid-auto-columns: minmax(270px, 84vw) !important;', $css);
    }
}
