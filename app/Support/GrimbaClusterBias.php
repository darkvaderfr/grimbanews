<?php

namespace App\Support;

/**
 * GrimbaClusterBias
 *
 * Resolves the majority-bias label for a cluster (or any bias-count
 * tally). The big subtle case is the L-R tie: when left and right
 * each get the same count AND that tie is the highest count, neither
 * "Gauche" nor "Droite" tells the truth — the coverage is balanced.
 *
 * Vader directive 2026-05-20: "For articles that are combined and
 * have a fifty fifty left right breakdown, we want to make sure that
 * we are not saying it's left or it's right. We want to instead call
 * that Middle Ground. And in French, Juste Milieu."
 *
 * Center-only majority is NOT Middle Ground — that's still "Centre"
 * (the editorial-bias position). Middle Ground specifically means
 * "covered equally from both extremes" — a different signal.
 */
class GrimbaClusterBias
{
    /**
     * Canonical prefix for persisted Middle Ground tags on
     * story_clusters.review_action. Centralized so a future
     * format change (e.g., versioning prefix as "mg2_") only
     * touches this file. Consumers MUST use this constant
     * rather than hardcoding 'mg_'.
     */
    public const MG_TAG_PREFIX = 'mg_';

    /**
     * SQL LIKE-pattern matching the canonical prefix. Use in
     * query builders: `->where('review_action', 'like', GrimbaClusterBias::MG_TAG_SQL_LIKE)`.
     */
    public const MG_TAG_SQL_LIKE = 'mg_%';

    /**
     * Canonical resolver key for Middle Ground. Centralized so a
     * future key-rename doesn't leave dangling string literals.
     */
    public const KEY_MIDDLE_GROUND = 'middle_ground';

    /**
     * Boolean convenience: is this review_action value a Middle
     * Ground tag (any well-formed mg_<L>_<C>_<R>)? Returns false
     * for null, empty, non-mg_, and malformed mg_-prefixed values.
     * Equivalent to parseMgTag($tag) !== null but reads cleaner
     * at call sites that only need a yes/no.
     */
    public static function isMiddleGround(?string $tag): bool
    {
        return $tag !== null && self::parseMgTag($tag) !== null;
    }

    /**
     * Boolean convenience for the live-resolver result. Use after
     * calling resolve() when you need to branch on Middle Ground vs
     * any other bias key without comparing the magic string.
     *
     * Two overloads: pass either the resolved array (preferred) or
     * the bare key string (legacy callsites).
     *
     * @param array{key: string}|string|null $resolvedOrKey
     */
    public static function isMiddleGroundKey($resolvedOrKey): bool
    {
        if ($resolvedOrKey === null) {
            return false;
        }
        if (is_array($resolvedOrKey)) {
            return ($resolvedOrKey['key'] ?? null) === self::KEY_MIDDLE_GROUND;
        }
        return $resolvedOrKey === self::KEY_MIDDLE_GROUND;
    }

    /**
     * Resolve majority-bias key + label + color from a bias-count array.
     *
     * @param array<string, int> $counts e.g. ['left' => 3, 'center' => 1, 'right' => 3]
     * @return array{key: string, label: string, color: string}
     */
    public static function resolve(array $counts): array
    {
        $left = (int) ($counts['left'] ?? 0);
        $center = (int) ($counts['center'] ?? 0);
        $right = (int) ($counts['right'] ?? 0);

        // Nothing classified → unknown
        if ($left + $center + $right <= 0) {
            return ['key' => 'unknown', 'label' => __('Non classé'), 'color' => '#6b6459'];
        }

        // The Middle Ground case: left and right tied AND that tie
        // is at least the highest. If center happens to equal the
        // L=R tie, prefer "Middle Ground" because it tells the
        // reader the bigger story — coverage spans both sides.
        if ($left > 0 && $left === $right && $left >= $center) {
            return [
                'key' => 'middle_ground',
                'label' => __('Juste milieu'),
                'color' => '#a855f7',
            ];
        }

        // Otherwise, the single highest bucket wins.
        $top = max($left, $center, $right);
        if ($top === $left) {
            return ['key' => 'left', 'label' => __('Gauche'), 'color' => '#3b82f6'];
        }
        if ($top === $right) {
            return ['key' => 'right', 'label' => __('Droite'), 'color' => '#e84c3d'];
        }
        return ['key' => 'center', 'label' => __('Centre'), 'color' => '#a8a8a8'];
    }

    /**
     * Convenience: just the label string (translated to current locale).
     */
    public static function label(array $counts): string
    {
        return self::resolve($counts)['label'];
    }

    /**
     * Convenience: just the chip color hex.
     */
    public static function color(array $counts): string
    {
        return self::resolve($counts)['color'];
    }

    /**
     * Parse the persisted "mg_<L>_<C>_<R>" tag written by
     * grimba:reclassify-clusters --persist onto
     * story_clusters.review_action. Returns null when the input
     * isn't a well-formed mg_ tag (wrong prefix, wrong segment
     * count, non-integer segments). Callers that need to defend
     * against drift in persisted tag data should check for null
     * rather than catch downstream exceptions.
     *
     * Example: "mg_2_1_2" → ['left' => 2, 'center' => 1, 'right' => 2].
     *
     * @return array{left: int, center: int, right: int}|null
     */
    public static function parseMgTag(string $tag): ?array
    {
        if (! str_starts_with($tag, 'mg_')) {
            return null;
        }
        $parts = explode('_', $tag);
        if (count($parts) !== 4) {
            return null;
        }
        if (! ctype_digit($parts[1]) || ! ctype_digit($parts[2]) || ! ctype_digit($parts[3])) {
            return null;
        }
        return [
            'left' => (int) $parts[1],
            'center' => (int) $parts[2],
            'right' => (int) $parts[3],
        ];
    }

    /**
     * Format the canonical "mg_<L>_<C>_<R>" tag for persistence on
     * story_clusters.review_action. Counterpart to parseMgTag() —
     * keep writer + reader on the same single source of truth so a
     * format change (e.g., adding a separator, swapping segment
     * order) only touches one file.
     *
     * Inputs are floored to non-negative ints (negative slots are
     * meaningless for bias counts).
     */
    public static function formatMgTag(int $left, int $center, int $right): string
    {
        return 'mg_' . max(0, $left) . '_' . max(0, $center) . '_' . max(0, $right);
    }

    /**
     * Summarize a list of persisted mg_* tags into the standard shape
     * used by admin surfaces (mg-stats, the cockpit dashboard tile,
     * future per-locale breakdowns). Returns:
     *
     *   [
     *     'count'              => int,    // valid tags
     *     'sum_left'           => int,
     *     'sum_center'         => int,
     *     'sum_right'          => int,
     *     'avg_cluster_size'   => float,  // 2dp
     *     'symmetric_count'    => int,    // center == 0
     *     'center_heavy_count' => int,    // center >= left (and > 0)
     *     'malformed_count'    => int,    // tags that failed parseMgTag()
     *   ]
     *
     * Malformed tags are reported (not silently dropped) so callers
     * can surface drift in persisted data.
     *
     * @param iterable<int, string> $tags
     * @return array{count: int, sum_left: int, sum_center: int, sum_right: int, avg_cluster_size: float, symmetric_count: int, center_heavy_count: int, malformed_count: int}
     */
    public static function summarizeMgTags(iterable $tags): array
    {
        $count = 0;
        $sumL = 0;
        $sumC = 0;
        $sumR = 0;
        $symmetric = 0;
        $centerHeavy = 0;
        $malformed = 0;

        foreach ($tags as $tag) {
            $parsed = self::parseMgTag((string) $tag);
            if ($parsed === null) {
                $malformed++;
                continue;
            }
            $count++;
            $sumL += $parsed['left'];
            $sumC += $parsed['center'];
            $sumR += $parsed['right'];
            if ($parsed['center'] === 0) {
                $symmetric++;
            } elseif ($parsed['center'] >= $parsed['left']) {
                $centerHeavy++;
            }
        }

        $avgSize = $count > 0 ? round(($sumL + $sumC + $sumR) / $count, 2) : 0.0;

        return [
            'count' => $count,
            'sum_left' => $sumL,
            'sum_center' => $sumC,
            'sum_right' => $sumR,
            'avg_cluster_size' => $avgSize,
            'symmetric_count' => $symmetric,
            'center_heavy_count' => $centerHeavy,
            'malformed_count' => $malformed,
        ];
    }
}
