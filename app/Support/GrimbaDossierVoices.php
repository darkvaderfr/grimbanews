<?php

namespace App\Support;

use Botble\Blog\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * GrimbaDossierVoices
 *
 * Single representative pick per bias side (Gauche / Centre / Droite) for
 * the dossier page. Replaces the "render every article as a card twice"
 * pattern (source-drilldown + article-list) with three elevated voices
 * that distil each side's framing.
 *
 * Picking rules per side:
 *   1. Never the post the reader landed on (no self-quote).
 *   2. Prefer the post with the longest distinct excerpt — that's the
 *      one most likely to carry the framing language we want to show.
 *   3. Break ties on higher view count, then most recent.
 *   4. If a side has zero posts, the slot is null — the UI renders an
 *      "absent voice" placeholder so readers see the coverage gap.
 */
class GrimbaDossierVoices
{
    /**
     * @param  Collection<int, Post>  $clusterPosts
     * @return array{
     *     left:    ?array{post: Post, excerpt: string, country: ?string, source_id: ?int},
     *     center:  ?array{post: Post, excerpt: string, country: ?string, source_id: ?int},
     *     right:   ?array{post: Post, excerpt: string, country: ?string, source_id: ?int},
     *     others:  Collection<int, Post>,
     *     totals:  array{left:int, center:int, right:int, unknown:int}
     * }
     */
    public static function build(Collection $clusterPosts, Post $currentPost, ?Collection $sourceMeta = null): array
    {
        $byBias = ['left' => [], 'center' => [], 'right' => [], 'unknown' => []];

        foreach ($clusterPosts as $post) {
            $bucket = self::bucketFor($post);
            $byBias[$bucket][] = $post;
        }

        $picks = [
            'left' => self::pickRepresentative($byBias['left'], $currentPost, $sourceMeta),
            'center' => self::pickRepresentative($byBias['center'], $currentPost, $sourceMeta),
            'right' => self::pickRepresentative($byBias['right'], $currentPost, $sourceMeta),
        ];

        $pickedIds = collect($picks)
            ->filter()
            ->pluck('post.id')
            ->push((int) $currentPost->id)
            ->unique()
            ->all();

        $others = $clusterPosts
            ->reject(fn ($p) => in_array((int) $p->id, $pickedIds, true))
            ->values();

        return [
            'left' => $picks['left'],
            'center' => $picks['center'],
            'right' => $picks['right'],
            'others' => $others,
            'totals' => [
                'left' => count($byBias['left']),
                'center' => count($byBias['center']),
                'right' => count($byBias['right']),
                'unknown' => count($byBias['unknown']),
            ],
        ];
    }

    private static function bucketFor(Post $post): string
    {
        $b = $post->bias_rating ?? 'unknown';

        return in_array($b, ['left', 'center', 'right'], true) ? $b : 'unknown';
    }

    /**
     * @param  array<int, Post>  $candidates
     * @return ?array{post: Post, excerpt: string, country: ?string, source_id: ?int}
     */
    private static function pickRepresentative(array $candidates, Post $currentPost, ?Collection $sourceMeta): ?array
    {
        if (empty($candidates)) {
            return null;
        }

        $scored = [];
        foreach ($candidates as $post) {
            if ((int) $post->id === (int) $currentPost->id) {
                continue;
            }

            $excerpt = self::extractExcerpt($post);
            $score = strlen($excerpt) * 100
                + (int) ($post->views ?? 0)
                + (optional($post->created_at)->timestamp ?? 0) / 1_000_000;

            $scored[] = ['post' => $post, 'excerpt' => $excerpt, 'score' => $score];
        }

        if (empty($scored)) {
            // Cluster has only one post on this side and it's the current
            // one. Fall back to the current post's excerpt so the side
            // still reads instead of going blank.
            $excerpt = self::extractExcerpt($candidates[0]);

            return [
                'post' => $candidates[0],
                'excerpt' => $excerpt,
                'country' => self::countryFor($candidates[0], $sourceMeta),
                'source_id' => $candidates[0]->source_id ?? null,
            ];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
        $top = $scored[0];

        return [
            'post' => $top['post'],
            'excerpt' => $top['excerpt'],
            'country' => self::countryFor($top['post'], $sourceMeta),
            'source_id' => $top['post']->source_id ?? null,
        ];
    }

    private static function extractExcerpt(Post $post): string
    {
        $translated = GrimbaTranslationPresenter::description($post);
        $body = GrimbaTranslationPresenter::body($post);

        $candidates = [
            (string) $translated,
            (string) $body,
            (string) ($post->description ?? ''),
            (string) ($post->content ?? ''),
        ];

        foreach ($candidates as $raw) {
            $clean = trim(preg_replace('/\s+/u', ' ', strip_tags($raw)));
            if (Str::length($clean) >= 80) {
                return Str::limit($clean, 360);
            }
        }

        // Last resort — use whichever was longest, even if short.
        $best = '';
        foreach ($candidates as $raw) {
            $clean = trim(preg_replace('/\s+/u', ' ', strip_tags($raw)));
            if (Str::length($clean) > Str::length($best)) {
                $best = $clean;
            }
        }

        return Str::limit($best, 360);
    }

    private static function countryFor(Post $post, ?Collection $sourceMeta): ?string
    {
        if ($sourceMeta && $post->source_id && isset($sourceMeta[$post->source_id])) {
            $meta = $sourceMeta[$post->source_id];
            $country = $meta->country ?? null;
            if (! empty($country)) {
                return (string) $country;
            }
        }

        return $post->country ?? null;
    }
}
