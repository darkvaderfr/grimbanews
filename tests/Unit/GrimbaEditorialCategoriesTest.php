<?php

namespace Tests\Unit;

use App\Support\GrimbaEditorialCategories;
use Tests\TestCase;

/**
 * S-CAT-01 — pin the primaryTopicFor() decision contract.
 *
 * The reader card badge surfaces use this to render "this is
 * Politique / Sports / Culture" on every card. Regional bins
 * (Europe, Afrique, Amériques, International) MUST be skipped
 * because the editorial region already conveys that.
 */
class GrimbaEditorialCategoriesTest extends TestCase
{
    private function makePost(array $catNames): object
    {
        $cats = collect(array_map(fn (string $n) => (object) ['name' => $n], $catNames));
        return (object) ['categories' => $cats];
    }

    public function test_returns_null_when_post_has_no_categories(): void
    {
        $this->assertNull(GrimbaEditorialCategories::primaryTopicFor($this->makePost([])));
    }

    public function test_prefers_topic_category_over_regional(): void
    {
        // A post tagged BOTH 'Europe' (regional) and 'Politique'
        // (topic) should surface the topic.
        $t = GrimbaEditorialCategories::primaryTopicFor($this->makePost(['Europe', 'Politique']));
        $this->assertNotNull($t);
        $this->assertSame('Politique', $t->name);
    }

    public function test_skips_a_la_une_front_page_bucket(): void
    {
        // À la une is editorial housekeeping, not a topic.
        $t = GrimbaEditorialCategories::primaryTopicFor($this->makePost(['À la une', 'Sports']));
        $this->assertNotNull($t);
        $this->assertSame('Sports', $t->name);
    }

    public function test_skips_internal_review_buckets(): void
    {
        // 'Trusted Source Credibility' / 'Unclassified Source Bias'
        // are ops-only, never reader-facing.
        $t = GrimbaEditorialCategories::primaryTopicFor($this->makePost(['Trusted Source Credibility', 'Culture']));
        $this->assertNotNull($t);
        $this->assertSame('Culture', $t->name);
    }

    public function test_returns_null_when_only_regional_and_housekeeping_tags(): void
    {
        // Post with only Europe + À la une → no topic → null.
        // Badge partial guards on null so the card just doesn't
        // show a topic chip, which is the right behavior.
        $t = GrimbaEditorialCategories::primaryTopicFor($this->makePost(['Europe', 'À la une']));
        $this->assertNull($t);
    }

    public function test_falls_back_to_first_non_skipped_when_no_topic_matches(): void
    {
        // Unusual case: a post with only categories not in topic
        // names AND not in skip names. Falls back to the first
        // such category. (We use Immigration since it IS in
        // topicNames — pick something genuinely unknown to test.)
        $t = GrimbaEditorialCategories::primaryTopicFor($this->makePost(['Healthy']));
        $this->assertNotNull($t);
        $this->assertSame('Healthy', $t->name);
    }

    public function test_handles_array_input_gracefully(): void
    {
        // primaryTopicFor must work even when categories is a
        // plain array (rare but Botble's lazy-load surfaces can
        // produce this).
        $post = (object) [
            'categories' => [
                (object) ['name' => 'Sports'],
            ],
        ];
        $t = GrimbaEditorialCategories::primaryTopicFor($post);
        $this->assertSame('Sports', $t->name);
    }
}
