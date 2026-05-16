<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Contract tests for the canonical article-hero-card pattern.
 *
 * Runs against the live DB (consistent with HomeFeedAllocatorTest +
 * BreakingStreamTest). Asserts that every article page — single-post
 * and multi-source dossier — surfaces the same hero card shape:
 *   - 1 SOURCE card
 *   - 1 EXCERPT card
 *   - 1 share-kit aside
 *   - 0 legacy details-share rows (Sprint 13 cleanup)
 *   - 0 legacy grimba-full-article--reader duplicates of the excerpt
 *     (Sprint 1 dedupe — the legacy panel only fires for locked
 *     member-gate state, not the unlocked excerpt path).
 *
 * The tests pull the first article from the live corpus rather than
 * fixtures so they verify the actual rendered output readers see.
 */
class ArticleHeroCardTest extends TestCase
{
    public function test_article_page_renders_one_canonical_card_set(): void
    {
        $slug = $this->firstArticleSlug();
        if ($slug === null) {
            $this->markTestSkipped('No published article with a slug to smoke against.');
        }

        $html = $this->get('/article/' . $slug)->assertOk()->getContent();

        $this->assertSame(
            1,
            substr_count($html, '<article class="grimba-article-card__source-card"'),
            'article page must render exactly one SOURCE card'
        );
        $this->assertSame(
            1,
            substr_count($html, '<article class="grimba-article-card__excerpt-card'),
            'article page must render exactly one EXCERPT card'
        );
        $this->assertStringContainsString(
            '<aside class="grimba-share-kit',
            $html,
            'article page must render the unified share-kit aside'
        );
        $this->assertStringNotContainsString(
            'details-share',
            $html,
            'legacy details-share icon row must be gone (Sprint 13 cleanup)'
        );
        $this->assertSame(
            0,
            substr_count($html, '<section class="grimba-full-article grimba-full-article--reader'),
            'legacy full-article reader must not render when the excerpt card is canonical'
        );
    }

    public function test_article_card_has_editorial_ribbon_signature(): void
    {
        $slug = $this->firstArticleSlug();
        if ($slug === null) {
            $this->markTestSkipped('No published article with a slug to smoke against.');
        }

        $html = $this->get('/article/' . $slug)->assertOk()->getContent();

        // The canonical ribbon gradient appears in the inline style
        // block on the source/excerpt/insights cards. Any one card
        // having it proves the signature is alive on the page.
        $this->assertStringContainsString(
            'rgba(192, 57, 43, 0.52)',
            $html,
            'editorial ribbon gradient must be present in inline styles'
        );
    }

    public function test_share_kit_renders_canonical_icon_set(): void
    {
        $slug = $this->firstArticleSlug();
        if ($slug === null) {
            $this->markTestSkipped('No published article with a slug to smoke against.');
        }

        $html = $this->get('/article/' . $slug)->assertOk()->getContent();

        // 6 network icons + 1 copy-link button = 7 buttons
        $this->assertSame(
            7,
            substr_count($html, 'class="grimba-share-kit__btn'),
            'share-kit must render exactly 7 icon buttons (X, Bluesky, FB, WhatsApp, LinkedIn, Email, Copy)'
        );
    }

    /**
     * Returns the slug of a published article that has both a
     * canonical slug and a non-empty title — so the route resolves
     * and the partials have content to render.
     */
    private function firstArticleSlug(): ?string
    {
        // Slugs for posts use the 'blog' prefix; the /article/{slug}
        // route canonicalises blog → article transparently.
        $row = DB::table('slugs')
            ->where('reference_type', \Botble\Blog\Models\Post::class)
            ->where('prefix', 'blog')
            ->join('posts', 'posts.id', '=', 'slugs.reference_id')
            ->where('posts.status', 'published')
            ->whereNotNull('posts.name')
            ->orderByDesc('posts.id')
            ->first(['slugs.key as slug']);

        return $row->slug ?? null;
    }
}
