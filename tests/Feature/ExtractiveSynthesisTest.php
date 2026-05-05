<?php

namespace Tests\Feature;

use Botble\Blog\Models\Post;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExtractiveSynthesisTest extends TestCase
{
    private function readerCookies(): array
    {
        return [
            'grimba_lang' => 'fr',
            'grimba_onboarded' => '1',
        ];
    }

    /**
     * @return array<int>
     */
    private function publishedPostIds(int $count, int $offset): array
    {
        $ids = DB::table('posts')
            ->where('status', 'published')
            ->whereNotNull('name')
            ->orderBy('id')
            ->skip($offset)
            ->limit($count)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount($count, $ids, 'Fixture database must contain enough published posts.');

        return $ids;
    }

    /**
     * @param array<int, array{source: string, description: string, bias?: string}> $rows
     */
    private function assignCluster(array $ids, int $clusterId, array $rows): Post
    {
        DB::table('posts')
            ->where('story_cluster_id', $clusterId)
            ->update(['story_cluster_id' => null]);

        foreach ($ids as $index => $id) {
            DB::table('posts')
                ->where('id', $id)
                ->update([
                    'story_cluster_id' => $clusterId,
                    'source_id' => null,
                    'source_name' => $rows[$index]['source'],
                    'description' => $rows[$index]['description'],
                    'bias_rating' => $rows[$index]['bias'] ?? 'center',
                    'original_language' => 'fr',
                    'translated_name' => null,
                    'translated_description' => null,
                    'translated_to' => null,
                    'summary_nobuai' => null,
                    'summary_generated_at' => null,
                    'summary_driver' => null,
                    'updated_at' => now()->subSeconds($index),
                    'created_at' => now()->subSeconds($index),
                ]);
        }

        $post = Post::query()->find($ids[0]);
        $this->assertNotNull($post, 'Fixture post must still resolve through the Blog model.');

        return $post;
    }

    private function pathFor(Post $post): string
    {
        $path = parse_url($post->url, PHP_URL_PATH);

        $this->assertIsString($path);
        $this->assertNotSame('', $path);

        return $path;
    }

    private function insightsHtmlFor(Post $post): string
    {
        $html = $this->withUnencryptedCookies($this->readerCookies())
            ->get($this->pathFor($post))
            ->assertOk()
            ->assertSee('Synthèse multi-sources')
            ->getContent();

        preg_match('/<details open class="grimba-insights"[\s\S]*?<\/details>/', $html, $matches);
        $this->assertNotEmpty($matches[0] ?? '', 'Story page must render the extractive insights block.');

        return $matches[0];
    }

    public function test_extractive_synthesis_attributes_each_bullet_to_a_unique_source(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(3, 12), 920001, [
            [
                'source' => 'Alpha Fixtures',
                'description' => 'Alpha lead sentence explains the pensions vote with concrete legislative stakes. Extra context follows.',
                'bias' => 'left',
            ],
            [
                'source' => 'Beta Fixtures',
                'description' => 'Beta lead sentence frames the same vote around coalition discipline and timing. Extra context follows.',
                'bias' => 'center',
            ],
            [
                'source' => 'Gamma Fixtures',
                'description' => 'Gamma lead sentence focuses on market pressure and budget credibility. Extra context follows.',
                'bias' => 'right',
            ],
        ]);

        $insights = $this->insightsHtmlFor($post);

        $this->assertStringContainsString('Alpha lead sentence explains the pensions vote', $insights);
        $this->assertStringContainsString('Beta lead sentence frames the same vote', $insights);
        $this->assertStringContainsString('Gamma lead sentence focuses on market pressure', $insights);
        $this->assertSame(1, substr_count($insights, 'Alpha Fixtures'));
        $this->assertSame(1, substr_count($insights, 'Beta Fixtures'));
        $this->assertSame(1, substr_count($insights, 'Gamma Fixtures'));
    }

    public function test_extractive_synthesis_dedupes_near_identical_leads(): void
    {
        $post = $this->assignCluster($this->publishedPostIds(3, 15), 920002, [
            [
                'source' => 'Delta Fixtures',
                'description' => 'Shared lead sentence repeats the same first forty characters before diverging into detail A.',
                'bias' => 'left',
            ],
            [
                'source' => 'Echo Fixtures',
                'description' => 'Shared lead sentence repeats the same first forty characters before diverging into detail B.',
                'bias' => 'center',
            ],
            [
                'source' => 'Foxtrot Fixtures',
                'description' => 'Distinct synthesis sentence gives readers a genuinely separate source angle. Extra context follows.',
                'bias' => 'right',
            ],
        ]);

        $insights = $this->insightsHtmlFor($post);

        $this->assertStringContainsString('Delta Fixtures', $insights);
        $this->assertStringNotContainsString('Echo Fixtures', $insights);
        $this->assertStringContainsString('Foxtrot Fixtures', $insights);
        $this->assertSame(1, substr_count($insights, 'Shared lead sentence repeats the same first forty characters'));
    }

    public function test_extractive_synthesis_limits_output_to_five_bullets(): void
    {
        $rows = [];
        foreach (range(1, 7) as $index) {
            $rows[] = [
                'source' => "Limit Fixture {$index}",
                'description' => "Limit bullet {$index} contains a sufficiently long first sentence for the extractive summary test. Extra context follows.",
                'bias' => ['left', 'center', 'right'][$index % 3],
            ];
        }

        $post = $this->assignCluster($this->publishedPostIds(7, 18), 920003, $rows);
        $insights = $this->insightsHtmlFor($post);

        $this->assertSame(5, substr_count($insights, 'Limit Fixture '));
        $this->assertStringContainsString('Limit Fixture 1', $insights);
        $this->assertStringContainsString('Limit Fixture 5', $insights);
        $this->assertStringNotContainsString('Limit Fixture 6', $insights);
        $this->assertStringNotContainsString('Limit Fixture 7', $insights);
    }
}
