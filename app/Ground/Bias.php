<?php

namespace App\Ground;

/**
 * 7-tier bias classification for sources.
 *
 * Lives under App\Ground because App\Support is root-owned
 * (cache-dir-style permission residue). Pure-PHP helpers, no DB.
 *
 * The 7 tiers use GrimbaNews's source-level resolution:
 * Far Left, Left, Lean Left, Center, Lean Right, Right, Far Right.
 * The story-level coverage bar still compresses to 3 sides.
 */
class Bias
{
    /**
     * Resolve a 7-tier bias slug for a source row.
     * Heuristic until AllSides/Ad Fontes/MBFC averages are wired:
     *  - bias_score < -0.7 → far_left
     *  - bias_score in [-0.7, -0.4) → left
     *  - bias_score in [-0.4, -0.15) → lean_left
     *  - bias_score in [-0.15, 0.15] → center
     *  - bias_score in (0.15, 0.4] → lean_right
     *  - bias_score in (0.4, 0.7] → right
     *  - bias_score > 0.7 → far_right
     *  - bias_score null but bias_rating in {left,center,right} → fall back to that 3-tier value
     */
    public static function tier(?string $biasRating, $biasScore = null): string
    {
        if ($biasScore !== null && is_numeric($biasScore)) {
            $score = (float) $biasScore;

            return match (true) {
                $score < -0.7 => 'far_left',
                $score < -0.4 => 'left',
                $score < -0.15 => 'lean_left',
                $score <= 0.15 => 'center',
                $score <= 0.4 => 'lean_right',
                $score <= 0.7 => 'right',
                default => 'far_right',
            };
        }

        return match (strtolower((string) $biasRating)) {
            'left' => 'left',
            'center' => 'center',
            'right' => 'right',
            'far_left', 'far-left' => 'far_left',
            'lean_left', 'lean-left' => 'lean_left',
            'lean_right', 'lean-right' => 'lean_right',
            'far_right', 'far-right' => 'far_right',
            default => 'unknown',
        };
    }

    public static function label(string $tier): string
    {
        return match ($tier) {
            'far_left' => __('Extrême gauche'),
            'left' => __('Gauche'),
            'lean_left' => __('Centre gauche'),
            'center' => __('Centre'),
            'lean_right' => __('Centre droit'),
            'right' => __('Droite'),
            'far_right' => __('Extrême droite'),
            default => __('Non évalué'),
        };
    }

    public static function shortLabel(string $tier): string
    {
        return match ($tier) {
            'far_left' => __('XG'),
            'left' => __('G'),
            'lean_left' => __('CG'),
            'center' => __('C'),
            'lean_right' => __('CD'),
            'right' => __('D'),
            'far_right' => __('XD'),
            default => __('?'),
        };
    }

    public static function color(string $tier): string
    {
        return match ($tier) {
            'far_left' => '#1d4ed8',
            'left' => '#3b82f6',
            'lean_left' => '#7aa6f9',
            'center' => '#a8a8a8',
            'lean_right' => '#f49b94',
            'right' => '#e84c3d',
            'far_right' => '#b91c1c',
            default => '#9ca3af',
        };
    }

    /**
     * Compresses 7-tier to 3-tier (for story-level coverage bars).
     * Returns: left | center | right | unknown.
     */
    public static function side(string $tier): string
    {
        return match ($tier) {
            'far_left', 'left', 'lean_left' => 'left',
            'center' => 'center',
            'lean_right', 'right', 'far_right' => 'right',
            default => 'unknown',
        };
    }
}
