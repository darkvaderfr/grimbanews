<?php

namespace Tests\Feature;

use App\Support\GrimbaSourceBreakdown;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GrimbaSourceBreakdownTest extends TestCase
{
    public function test_source_breakdown_normalizes_bias_factuality_and_ownership(): void
    {
        $posts = new Collection([
            (object) [
                'id' => 1,
                'source_id' => null,
                'source_name' => 'A',
                'bias_rating' => 'left',
                'credibility_score' => 91,
                'ownership_type' => 'independent',
            ],
            (object) [
                'id' => 2,
                'source_id' => null,
                'source_name' => 'B',
                'bias_rating' => 'center',
                'credibility_score' => 76,
                'ownership_type' => 'media_conglomerate',
            ],
            (object) [
                'id' => 3,
                'source_id' => null,
                'source_name' => 'C',
                'bias_rating' => 'right',
                'credibility_score' => 46,
                'ownership_type' => 'government',
            ],
        ]);

        $breakdown = GrimbaSourceBreakdown::fromPosts($posts);

        $this->assertSame(3, $breakdown['total']);
        $this->assertSame(1, $breakdown['biasBuckets']->firstWhere('key', 'left')->count);
        $this->assertSame(1, $breakdown['factBuckets']['very-high']->items->count());
        $this->assertSame(1, $breakdown['factBuckets']['low']->items->count());
        $this->assertSame('Conglomérat média', $breakdown['ownershipBuckets']->firstWhere('label', 'Conglomérat média')->label);
        $this->assertStringContainsString('transparent', $breakdown['donutGradient']);
    }
}
