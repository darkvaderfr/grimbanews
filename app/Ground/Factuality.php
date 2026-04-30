<?php

namespace App\Ground;

/**
 * 5-tier factuality classification for sources.
 *
 * Mirrors Ground.news's tiers (Very Low, Low, Mixed, High, Very High).
 * Aggregation source: editorial seed + Ad Fontes + MBFC averages once wired.
 * Until then, derived from `news_sources.credibility_score` (0-100):
 *   0-20 → very_low
 *   21-40 → low
 *   41-60 → mixed
 *   61-80 → high
 *   81-100 → very_high
 *   null → unknown
 */
class Factuality
{
    public static function tier($score): string
    {
        if ($score === null || $score === '' || ! is_numeric($score)) {
            return 'unknown';
        }

        $n = (int) $score;

        return match (true) {
            $n <= 20 => 'very_low',
            $n <= 40 => 'low',
            $n <= 60 => 'mixed',
            $n <= 80 => 'high',
            default => 'very_high',
        };
    }

    public static function label(string $tier): string
    {
        return match ($tier) {
            'very_low' => __('Très basse'),
            'low' => __('Basse'),
            'mixed' => __('Mixte'),
            'high' => __('Haute'),
            'very_high' => __('Très haute'),
            default => __('Non évaluée'),
        };
    }

    public static function color(string $tier): string
    {
        return match ($tier) {
            'very_low' => '#b91c1c',
            'low' => '#e84c3d',
            'mixed' => '#f59e0b',
            'high' => '#16a34a',
            'very_high' => '#14532d',
            default => '#9ca3af',
        };
    }

    public static function glyph(string $tier): string
    {
        return match ($tier) {
            'very_low' => '⚠',
            'low' => '!',
            'mixed' => '~',
            'high' => '✓',
            'very_high' => '✓✓',
            default => '?',
        };
    }
}
