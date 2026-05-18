<?php

namespace Tests\Feature;

use App\Support\GrimbaDossierLanguage;
use Botble\ACL\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * S-LANG-11 dossier-language modal — Vader 2026-05-17.
 *
 * Pin the modal-threshold semantics so a future tweak to the
 * `grimba_dossier_lang_modal_min` setting doesn't accidentally
 * flip how clusters resolve. Threshold default is 0.6 (60% modal
 * share required); below that, primary_language is NULL.
 *
 * Test deliberately does NOT use RefreshDatabase — it scopes its
 * inserts to dedicated cluster IDs in the 990xxx range so it can
 * run alongside the seeded production fixtures.
 */
class GrimbaDossierLanguageTest extends TestCase
{
    private const TEST_CLUSTER_BASE = 990500;

    /** @var int[] */
    private array $createdClusterIds = [];
    /** @var int[] */
    private array $createdPostIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (! Schema::hasColumn('story_clusters', 'primary_language')) {
            $this->markTestSkipped('story_clusters.primary_language column not yet migrated.');
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->createdPostIds as $id) {
            DB::table('posts')->where('id', $id)->delete();
        }
        foreach ($this->createdClusterIds as $id) {
            DB::table('story_clusters')->where('id', $id)->delete();
        }
        parent::tearDown();
    }

    public function test_strong_fr_majority_resolves_to_fr(): void
    {
        $clusterId = $this->makeCluster('FR-strong');
        $this->makePost($clusterId, 'fr');
        $this->makePost($clusterId, 'fr');
        $this->makePost($clusterId, 'fr');
        $this->makePost($clusterId, 'en');

        $result = GrimbaDossierLanguage::recompute($clusterId);

        $this->assertSame('fr', $result['primary_language']);
        $this->assertSame(3, $result['mix']['fr']);
        $this->assertSame(1, $result['mix']['en']);
    }

    public function test_strong_en_majority_resolves_to_en(): void
    {
        $clusterId = $this->makeCluster('EN-strong');
        for ($i = 0; $i < 4; $i++) {
            $this->makePost($clusterId, 'en');
        }
        $this->makePost($clusterId, 'fr');

        $result = GrimbaDossierLanguage::recompute($clusterId);

        $this->assertSame('en', $result['primary_language']);
        $this->assertSame(4, $result['mix']['en']);
    }

    public function test_exact_tie_resolves_to_null(): void
    {
        $clusterId = $this->makeCluster('tie');
        $this->makePost($clusterId, 'fr');
        $this->makePost($clusterId, 'fr');
        $this->makePost($clusterId, 'en');
        $this->makePost($clusterId, 'en');

        $result = GrimbaDossierLanguage::recompute($clusterId);

        $this->assertNull($result['primary_language'], '2:2 split should not cross 0.6 threshold');
    }

    public function test_borderline_4_3_below_default_threshold_resolves_to_null(): void
    {
        // 4 FR / 3 EN = 4/7 = 0.571... < 0.6 default threshold → NULL.
        $clusterId = $this->makeCluster('4-3');
        for ($i = 0; $i < 4; $i++) {
            $this->makePost($clusterId, 'fr');
        }
        for ($i = 0; $i < 3; $i++) {
            $this->makePost($clusterId, 'en');
        }

        $result = GrimbaDossierLanguage::recompute($clusterId);

        $this->assertNull(
            $result['primary_language'],
            '4:3 (0.571 share) sits BELOW the 0.6 modal threshold by design'
        );
    }

    public function test_5_3_clears_threshold_resolves_to_fr(): void
    {
        // 5 FR / 3 EN = 5/8 = 0.625 ≥ 0.6 → FR.
        $clusterId = $this->makeCluster('5-3');
        for ($i = 0; $i < 5; $i++) {
            $this->makePost($clusterId, 'fr');
        }
        for ($i = 0; $i < 3; $i++) {
            $this->makePost($clusterId, 'en');
        }

        $result = GrimbaDossierLanguage::recompute($clusterId);

        $this->assertSame('fr', $result['primary_language']);
    }

    public function test_null_posts_excluded_from_denominator(): void
    {
        // 2 FR + 1 EN + 5 NULL → known total = 3 → FR share = 2/3 = 0.667 ≥ 0.6 → FR.
        // NULL posts should NOT count toward the denominator.
        $clusterId = $this->makeCluster('null-excluded');
        $this->makePost($clusterId, 'fr');
        $this->makePost($clusterId, 'fr');
        $this->makePost($clusterId, 'en');
        $this->makePost($clusterId, '');
        $this->makePost($clusterId, '');
        $this->makePost($clusterId, '');

        $result = GrimbaDossierLanguage::recompute($clusterId);

        $this->assertSame('fr', $result['primary_language']);
        $this->assertSame(3, $result['mix']['unknown']);
    }

    public function test_all_null_cluster_resolves_to_null(): void
    {
        $clusterId = $this->makeCluster('all-null');
        $this->makePost($clusterId, '');
        $this->makePost($clusterId, '');

        $result = GrimbaDossierLanguage::recompute($clusterId);

        $this->assertNull($result['primary_language']);
        $this->assertSame(2, $result['mix']['unknown']);
    }

    public function test_recompute_writes_through_to_columns(): void
    {
        $clusterId = $this->makeCluster('writes-through');
        $this->makePost($clusterId, 'en');
        $this->makePost($clusterId, 'en');
        $this->makePost($clusterId, 'en');

        GrimbaDossierLanguage::recompute($clusterId);

        $row = DB::table('story_clusters')->where('id', $clusterId)->first();
        $this->assertSame('en', $row->primary_language);
        $this->assertNotNull($row->language_mix_json);
        $this->assertNotNull($row->language_recomputed_at);

        $mix = json_decode((string) $row->language_mix_json, true);
        $this->assertSame(3, $mix['en']);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function makeCluster(string $tag): int
    {
        $id = self::TEST_CLUSTER_BASE + count($this->createdClusterIds);
        DB::table('story_clusters')->insert([
            'id' => $id,
            'topic' => 'Dossier-lang test: ' . $tag,
            'description' => 'Auto-generated test fixture.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdClusterIds[] = $id;
        return $id;
    }

    private function makePost(int $clusterId, string $lang): int
    {
        $suffix = Str::lower(Str::random(6));
        $id = (int) DB::table('posts')->insertGetId([
            'name' => 'Dossier-lang fixture ' . $clusterId . ' ' . $suffix,
            'description' => 'fixture',
            'content' => '<p>fixture</p>',
            'status' => 'published',
            'author_id' => 1,
            'author_type' => User::class,
            'is_featured' => 0,
            'views' => 0,
            'bias_rating' => 'center',
            'is_blindspot' => 0,
            'source_name' => 'fixture',
            'original_language' => $lang,
            'story_cluster_id' => $clusterId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdPostIds[] = $id;
        return $id;
    }
}
