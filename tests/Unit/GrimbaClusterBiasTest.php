<?php

namespace Tests\Unit;

use App\Support\GrimbaClusterBias;
use Tests\TestCase;

class GrimbaClusterBiasTest extends TestCase
{
    /**
     * Wave MMMMMMMM (Vader 2026-05-20) — "fifty fifty left right
     * breakdown" → "Middle Ground" / "Juste milieu", never "Gauche"
     * or "Droite". Center-only majority stays "Centre". Lopsided
     * counts pick the higher side.
     */

    public function test_left_majority_resolves_to_left(): void
    {
        $resolved = GrimbaClusterBias::resolve(['left' => 5, 'center' => 1, 'right' => 1]);
        $this->assertSame('left', $resolved['key']);
    }

    public function test_right_majority_resolves_to_right(): void
    {
        $resolved = GrimbaClusterBias::resolve(['left' => 1, 'center' => 1, 'right' => 5]);
        $this->assertSame('right', $resolved['key']);
    }

    public function test_center_majority_resolves_to_center(): void
    {
        $resolved = GrimbaClusterBias::resolve(['left' => 1, 'center' => 5, 'right' => 1]);
        $this->assertSame('center', $resolved['key']);
    }

    public function test_fifty_fifty_left_right_resolves_to_middle_ground(): void
    {
        $resolved = GrimbaClusterBias::resolve(['left' => 3, 'center' => 0, 'right' => 3]);
        $this->assertSame('middle_ground', $resolved['key']);
    }

    public function test_fifty_fifty_left_right_with_some_center_still_middle_ground(): void
    {
        // L=3, R=3, C=2 — L+R tie is the top; still Middle Ground.
        $resolved = GrimbaClusterBias::resolve(['left' => 3, 'center' => 2, 'right' => 3]);
        $this->assertSame('middle_ground', $resolved['key']);
    }

    public function test_center_dominant_over_tied_extremes_resolves_to_center(): void
    {
        // Edge case: L=2, R=2, C=5 — extremes are tied, but center
        // has more than either single extreme. Helper rule (per
        // GrimbaClusterBias.php) is "L==R AND L>=C" → Middle Ground.
        // Here L=R=2, C=5, so C > L=R; center wins.
        //
        // The trade-off: Middle Ground signals "covered from both
        // sides" — true here. Centre signals "the dominant editorial
        // position is centrist" — also true here. We pick Centre
        // because the count gap is decisive (5 vs 2). A future call
        // from Vader could flip this rule; ping him if uncertain.
        $resolved = GrimbaClusterBias::resolve(['left' => 2, 'center' => 5, 'right' => 2]);
        $this->assertSame('center', $resolved['key']);
    }

    public function test_empty_resolves_to_unknown(): void
    {
        $resolved = GrimbaClusterBias::resolve([]);
        $this->assertSame('unknown', $resolved['key']);
    }

    public function test_all_zeros_resolves_to_unknown(): void
    {
        $resolved = GrimbaClusterBias::resolve(['left' => 0, 'center' => 0, 'right' => 0]);
        $this->assertSame('unknown', $resolved['key']);
    }

    public function test_single_side_only_picks_that_side(): void
    {
        $resolved = GrimbaClusterBias::resolve(['left' => 4, 'center' => 0, 'right' => 0]);
        $this->assertSame('left', $resolved['key']);

        $resolved2 = GrimbaClusterBias::resolve(['left' => 0, 'center' => 0, 'right' => 4]);
        $this->assertSame('right', $resolved2['key']);
    }

    public function test_middle_ground_color_distinct_from_left_right_center(): void
    {
        // The chip color must be visually distinct so the reader can
        // tell at a glance "this is balanced", not the somewhat-
        // grey "center" or the partisan blue/red.
        $colors = [
            GrimbaClusterBias::resolve(['left' => 1, 'right' => 0])['color'],
            GrimbaClusterBias::resolve(['left' => 0, 'center' => 1, 'right' => 0])['color'],
            GrimbaClusterBias::resolve(['left' => 0, 'right' => 1])['color'],
            GrimbaClusterBias::resolve(['left' => 1, 'right' => 1])['color'],
        ];
        $this->assertCount(4, array_unique($colors), 'Middle Ground color must be distinct from left/center/right.');
    }
}
