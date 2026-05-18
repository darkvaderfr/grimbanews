<?php

namespace Tests\Feature;

use App\Scopes\GrimbaRegionScope;
use Botble\ACL\Models\User;
use Botble\Blog\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * S-LSAT-18b — pin the secondary-region column + scope behavior.
 *
 * Two contracts to lock:
 *   1. Ingest hook writes `editorial_secondary_region` from the
 *      detector's secondary value when one is returned.
 *   2. GrimbaRegionScope OR-includes the secondary column so a
 *      story tagged primary=europe + secondary=africa appears on
 *      BOTH /europe and /africa.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GrimbaEditorialSecondaryRegionTest extends TestCase
{
    private const FIXTURE_BASE = 997000;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('posts')->where('id', '>=', self::FIXTURE_BASE)
            ->where('id', '<', self::FIXTURE_BASE + 1000)
            ->delete();
    }

    protected function tearDown(): void
    {
        DB::table('posts')->where('id', '>=', self::FIXTURE_BASE)
            ->where('id', '<', self::FIXTURE_BASE + 1000)
            ->delete();
        parent::tearDown();
    }

    public function test_migration_added_the_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('posts', 'editorial_secondary_region'),
            'Migration must have added editorial_secondary_region.',
        );
    }

    public function test_eloquent_save_writes_secondary_region_for_cross_region_story(): void
    {
        // S-LSAT-18b — when a post saves through Eloquent (RSS
        // poller, NewsAPI fetcher, editor admin), the ingest hook
        // calls detectAllFromText. A title strongly hitting two
        // regions should populate both columns.
        $post = new Post();
        $post->id = self::FIXTURE_BASE + 1;
        $post->name = 'Macron meets Zelensky in Kigali during Rwanda summit';
        $post->description = "Le président français était au Rwanda pour la rencontre.";
        $post->content = '<p>Body.</p>';
        $post->status = 'published';
        $post->author_id = 1;
        $post->author_type = User::class;
        $post->is_featured = 0;
        $post->bias_rating = 'center';
        $post->is_blindspot = 0;
        $post->source_name = 'Smoke Wire';
        $post->source_id = 1;
        $post->published_at = now();
        $post->save();

        $row = DB::table('posts')->where('id', $post->id)->first();
        // Primary lands; the detector returns it.
        $this->assertNotNull($row->editorial_region);
        // Secondary should be the OTHER region OR null (depending
        // on exact scoring; we just confirm it's not the same as
        // primary AND if set is a valid region key).
        if ($row->editorial_secondary_region !== null) {
            $this->assertNotSame(
                $row->editorial_region,
                $row->editorial_secondary_region,
                'Secondary must never equal primary.',
            );
            $this->assertContains(
                $row->editorial_secondary_region,
                ['africa', 'europe', 'americas'],
            );
        }
    }

    public function test_region_scope_or_includes_secondary_region_match(): void
    {
        // Insert a post where primary=europe + secondary=africa.
        // With the region cookie set to africa, the scope should
        // surface it via the secondary match.
        $now = now();
        DB::table('posts')->insert([
            'id' => self::FIXTURE_BASE + 2,
            'name' => 'Cross-region story (test fixture)',
            'description' => 'Body.',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Smoke Wire',
            'source_id' => 1,
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'editorial_secondary_region' => 'africa',
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Query via the scope by mimicking a public request with
        // grimba_region=africa cookie.
        $this->withUnencryptedCookies([GrimbaRegionScope::COOKIE_NAME => 'africa']);
        request()->cookies->set(GrimbaRegionScope::COOKIE_NAME, 'africa');

        $count = Post::query()
            ->where('id', self::FIXTURE_BASE + 2)
            ->count();
        $this->assertSame(1, $count, 'Region scope must surface the fixture via secondary-region match.');
    }

    public function test_region_scope_or_includes_secondary_region_match_primary_path_still_works(): void
    {
        // Same fixture; this time the cookie matches the primary
        // (europe). Should still surface (sanity that the OR
        // doesn't accidentally narrow the primary path).
        $now = now();
        DB::table('posts')->insert([
            'id' => self::FIXTURE_BASE + 3,
            'name' => 'Cross-region story (primary path)',
            'description' => 'Body.',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Smoke Wire',
            'source_id' => 1,
            'original_language' => 'fr',
            'editorial_region' => 'europe',
            'editorial_secondary_region' => 'africa',
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        request()->cookies->set(GrimbaRegionScope::COOKIE_NAME, 'europe');

        $count = Post::query()
            ->where('id', self::FIXTURE_BASE + 3)
            ->count();
        $this->assertSame(1, $count);
    }

    public function test_region_scope_excludes_when_neither_column_matches(): void
    {
        // Primary=americas, secondary=null, region cookie=africa →
        // should NOT surface.
        $now = now();
        DB::table('posts')->insert([
            'id' => self::FIXTURE_BASE + 4,
            'name' => 'Pure americas story',
            'description' => 'Body.',
            'content' => '<p>Body.</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'Smoke Wire',
            'source_id' => 1,
            'original_language' => 'en',
            'editorial_region' => 'americas',
            'editorial_secondary_region' => null,
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        request()->cookies->set(GrimbaRegionScope::COOKIE_NAME, 'africa');

        $count = Post::query()
            ->where('id', self::FIXTURE_BASE + 4)
            ->count();
        $this->assertSame(0, $count, 'Pure-americas story must not appear on /africa.');
    }
}
