<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GrimbaStoryInsights
{
    public static function buildHighlights(iterable $clusterPosts, int $limit = 3): array
    {
        $counts = [];

        foreach (Collection::make($clusterPosts) as $post) {
            $text = trim(strip_tags((string) (($post->name ?? '') . '. ' . ($post->description ?? ''))));

            if ($text === '') {
                continue;
            }

            preg_match_all('/\b([A-ZÀ-ÖØ-Þ][\p{L}\p{M}\-\'’]+(?:\s+[A-ZÀ-ÖØ-Þ][\p{L}\p{M}\-\'’]+)+)\b/u', $text, $matches);

            foreach ($matches[1] ?? [] as $entity) {
                $entity = trim(preg_replace('/\s+/u', ' ', $entity));
                $normalized = mb_strtolower($entity);

                if (self::shouldSkipEntity($entity, $normalized)) {
                    continue;
                }

                if (! isset($counts[$normalized])) {
                    $counts[$normalized] = [
                        'label' => $entity,
                        'count' => 0,
                    ];
                }

                $counts[$normalized]['count']++;
            }
        }

        return collect($counts)
            ->sortByDesc('count')
            ->take($limit)
            ->values()
            ->all();
    }

    public static function buildVoices(iterable $clusterPosts, int $limit = 5): array
    {
        $quotes = [];
        $seen = [];

        foreach (Collection::make($clusterPosts) as $post) {
            $text = trim(strip_tags((string) ($post->description ?? '')));

            if ($text === '') {
                continue;
            }

            preg_match_all('/(?:«([^»]{12,})»|"([^"]{12,})")/u', $text, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $quote = trim($match[1] ?: $match[2] ?: '');
                $signature = mb_strtolower($quote);

                if ($quote === '' || isset($seen[$signature])) {
                    continue;
                }

                $seen[$signature] = true;
                $quotes[] = [
                    'quote'  => Str::limit($quote, 220),
                    'source' => $post->source_name ?? null,
                    'bias'   => $post->bias_rating ?? 'unknown',
                ];

                if (count($quotes) >= $limit) {
                    break 2;
                }
            }
        }

        return $quotes;
    }

    public static function buildJumpList(iterable $clusterPosts, int $currentPostId): array
    {
        return Collection::make($clusterPosts)
            ->filter(static fn ($post) => (int) ($post->id ?? 0) !== $currentPostId)
            ->filter(static fn ($post) => filled($post->source_name ?? null))
            ->map(static function ($post): array {
                return [
                    'id'     => (int) $post->id,
                    'label'  => (string) $post->source_name,
                    'bias'   => $post->bias_rating ?? 'unknown',
                ];
            })
            ->unique('label')
            ->values()
            ->all();
    }

    protected static function shouldSkipEntity(string $entity, string $normalized): bool
    {
        if (mb_strlen($entity) < 5) {
            return true;
        }

        $blocked = [
            'grimbanews',
            'pour vous',
            'angle mort',
            'angles morts',
            'nobuai',
        ];

        if (in_array($normalized, $blocked, true)) {
            return true;
        }

        return preg_match('/^(L|La|Le|Les|Un|Une|Des|Du|De|Dans|Sur|Avec|Pour|Après|Avant|Selon|Cette|Ce|Ces)\s/u', $entity) === 1;
    }
}
