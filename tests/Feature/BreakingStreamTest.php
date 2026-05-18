<?php

namespace Tests\Feature;

use App\Support\GrimbaHomeFeed;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Contract tests for GrimbaHomeFeed::breaking() + latestStream().
 *
 * These tests run against the live DB (no RefreshDatabase trait, to
 * stay consistent with HomeFeedAllocatorTest). They assert
 * invariants — shape, mode semantics, cache key partitioning — not
 * absolute post counts, since the live corpus contains real breaking
 * matches and live freshness changes between test runs.
 */
class BreakingStreamTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        GrimbaHomeFeed::flush();
    }

    public function test_breaking_returns_well_formed_bundle(): void
    {
        $bundle = GrimbaHomeFeed::breaking(18);

        $this->assertArrayHasKey('mode', $bundle, 'breaking() must always return mode');
        $this->assertArrayHasKey('posts', $bundle, 'breaking() must always return posts');
        $this->assertContains($bundle['mode'], ['real', 'latest'], 'mode is one of real|latest');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $bundle['posts']);
    }

    public function test_breaking_inserts_become_visible_in_real_mode(): void
    {
        $now = now();
        $stamp = (int) ($now->timestamp);

        DB::table('posts')->insert([
            'name' => 'Breaking news: TEST-INSERT-' . $stamp . ' historic moment',
            'description' => 'Test post body.',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Test Wire',
            'source_id' => 1,
            // S-LSAT-04: the strict-locale filter on breaking() now
            // drops NULL-language posts. The test fixture lives in the
            // EN locale because its title is EN, so tag it explicitly.
            'original_language' => 'en',
            'published_at' => $now->copy()->subMinutes(30),
            'created_at' => $now->copy()->subMinutes(30),
            'updated_at' => $now->copy()->subMinutes(30),
            'editorial_region' => 'international',
        ]);

        Cache::flush();
        // Switch the reader locale to EN to match the fixture's
        // original_language so the strict filter accepts it.
        app()->setLocale('en');
        request()->merge(['lang' => 'en']);
        $bundle = GrimbaHomeFeed::breaking(18);

        $this->assertSame('real', $bundle['mode'], 'breaking() must report mode=real when a strict-phrase match exists');
        $found = $bundle['posts']->contains(fn ($p) => str_contains((string) $p->name, 'TEST-INSERT-' . $stamp));
        $this->assertTrue($found, 'real-mode breaking must include the just-inserted strict-keyword post');
    }

    public function test_substring_false_positives_do_not_match(): void
    {
        $now = now();
        $stamp = (int) ($now->timestamp);

        DB::table('posts')->insert([
            'name' => 'Ground-breaking SUBSTRING-TEST-' . $stamp . ' release this morning',
            'description' => 'Research published this morning.',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Test Science',
            'source_id' => 1,
            'published_at' => $now->copy()->subHour(),
            'created_at' => $now->copy()->subHour(),
            'updated_at' => $now->copy()->subHour(),
            'editorial_region' => 'international',
        ]);

        Cache::flush();
        $bundle = GrimbaHomeFeed::breaking(18);

        // The 'ground-breaking' substring contains 'breaking' but
        // doesn't match the strict 'breaking news' / 'breaking:' phrase
        // set. The inserted post must NOT appear in real-mode results
        // because of itself — note that real-mode might be triggered by
        // OTHER posts, so we check the inserted one specifically.
        $foundByItself = $bundle['posts']->contains(fn ($p) => str_contains((string) $p->name, 'SUBSTRING-TEST-' . $stamp));

        // If mode is 'real', the inserted post should NOT be one of
        // the strict matches. If mode is 'latest' (fallback), the
        // inserted post MAY appear in the latest pool — that's fine.
        if ($bundle['mode'] === 'real') {
            $this->assertFalse(
                $foundByItself,
                'substring "ground-breaking" must not trigger a real-mode match for the inserted post'
            );
        } else {
            $this->assertTrue(true, 'fallback latest mode is acceptable; no assertion required');
        }
    }

    public function test_latest_stream_returns_collection_capped_at_requested_count(): void
    {
        $posts = GrimbaHomeFeed::latestStream(5);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $posts);
        $this->assertLessThanOrEqual(5, $posts->count(), 'latestStream count must respect the requested cap');
        foreach ($posts as $p) {
            $this->assertNotEmpty((string) ($p->source_name ?? ''), 'latestStream must only return posts with a source name');
            $this->assertNotNull($p->id, 'every latestStream post must carry an id');
        }
    }

    public function test_latest_stream_is_sorted_published_at_desc(): void
    {
        $posts = GrimbaHomeFeed::latestStream(8);

        $previous = null;
        foreach ($posts as $p) {
            $current = $p->published_at ?? $p->created_at;
            if ($previous !== null && $current !== null) {
                $this->assertLessThanOrEqual(
                    strtotime((string) $previous),
                    strtotime((string) $current),
                    'latestStream must be sorted by published_at descending'
                );
            }
            $previous = $current;
        }
    }
}
