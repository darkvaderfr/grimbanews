<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GrimbaSourceBreakdown
{
    public static function fromPosts(Collection $posts): array
    {
        $sources = self::sourcesFromPosts($posts);
        $total = max(1, $sources->count());
        $biasBuckets = self::biasBuckets($sources);
        $knownBiasBuckets = $biasBuckets->filter(fn ($bucket) => in_array($bucket->key, ['left', 'center', 'right'], true));
        $weakestBias = $knownBiasBuckets->sortBy('count')->first();
        $ownershipBuckets = self::ownershipBuckets($sources);
        $topOwner = $ownershipBuckets->first();

        return [
            'sources' => $sources,
            'total' => $total,
            'biasBuckets' => $biasBuckets,
            'knownBiasBuckets' => $knownBiasBuckets,
            'weakestBias' => $weakestBias,
            'weakestPct' => $weakestBias ? (int) round($weakestBias->count * 100 / $total) : 0,
            'factBuckets' => self::factBuckets($sources),
            'ownershipBuckets' => $ownershipBuckets,
            'donutGradient' => self::donutGradient($ownershipBuckets, $total),
            'topOwner' => $topOwner,
            'topOwnerPct' => $topOwner ? (int) round($topOwner->count * 100 / $total) : 0,
        ];
    }

    public static function ownershipLabel(?string $ownership): string
    {
        $normalized = Str::of((string) $ownership)->lower()->replace(['_', '-'], ' ')->squish()->toString();

        return match (true) {
            str_contains($normalized, 'government') || str_contains($normalized, 'state') || str_contains($normalized, 'public') => __('Gouvernement / public'),
            str_contains($normalized, 'independent') => __('Indépendant'),
            str_contains($normalized, 'individual') || str_contains($normalized, 'family') => __('Individuel / familial'),
            str_contains($normalized, 'private') || str_contains($normalized, 'equity') => __('Private equity'),
            str_contains($normalized, 'conglomerate') || str_contains($normalized, 'corporate') || str_contains($normalized, 'company') => __('Conglomérat média'),
            $normalized === '' || $normalized === 'unknown' => __('Non classé'),
            default => Str::headline((string) $ownership),
        };
    }

    private static function sourcesFromPosts(Collection $posts): Collection
    {
        $sourceIds = $posts->pluck('source_id')->filter()->unique()->values();
        $sourceRows = $sourceIds->isEmpty()
            ? collect()
            : DB::table('news_sources')
                ->whereIn('id', $sourceIds)
                ->get(['id', 'name', 'website', 'bias_rating', 'ownership_type', 'credibility_score', 'owner_name']);

        $sourcesById = $sourceRows->keyBy('id');
        $fallbackNames = $posts->pluck('source_name')->filter()->unique()->values();
        $fallbackByName = $fallbackNames->isEmpty()
            ? collect()
            : DB::table('news_sources')
                ->whereIn('name', $fallbackNames)
                ->get(['id', 'name', 'website', 'bias_rating', 'ownership_type', 'credibility_score', 'owner_name'])
                ->keyBy(fn ($row) => Str::lower((string) $row->name));

        return $posts
            ->map(function ($post) use ($sourcesById, $fallbackByName) {
                $meta = $post->source_id ? $sourcesById->get($post->source_id) : null;
                $meta ??= $fallbackByName->get(Str::lower((string) $post->source_name));

                return (object) [
                    'key' => $post->source_id ?: Str::lower((string) ($post->source_name ?: $post->id)),
                    'name' => (string) ($meta->name ?? $post->source_name ?? __('Source inconnue')),
                    'website' => (string) ($meta->website ?? ''),
                    'bias' => (string) ($meta->bias_rating ?? $post->bias_rating ?? 'unknown'),
                    'credibility' => $meta->credibility_score ?? $post->credibility_score ?? null,
                    'ownership' => (string) ($meta->ownership_type ?? $post->ownership_type ?? 'unknown'),
                    'owner' => (string) ($meta->owner_name ?? ''),
                ];
            })
            ->unique('key')
            ->values();
    }

    private static function biasBuckets(Collection $sources): Collection
    {
        return collect([
            'left' => ['label' => __('Gauche'), 'color' => '#3b82f6'],
            'center' => ['label' => __('Centre'), 'color' => '#9ca3af'],
            'right' => ['label' => __('Droite'), 'color' => '#ef4444'],
            'unknown' => ['label' => __('Non classé'), 'color' => '#6b7280'],
        ])->map(function ($meta, $key) use ($sources) {
            $items = $sources->filter(fn ($source) => ($source->bias ?: 'unknown') === $key)->values();

            return (object) [
                'key' => $key,
                'label' => $meta['label'],
                'color' => $meta['color'],
                'items' => $items,
                'count' => $items->count(),
            ];
        })->values();
    }

    private static function factBuckets(Collection $sources): Collection
    {
        $buckets = collect([
            'very-high' => (object) ['label' => __('Très factuel'), 'range' => __('85-100'), 'color' => '#16a34a', 'items' => collect()],
            'high' => (object) ['label' => __('Factuel'), 'range' => __('70-84'), 'color' => '#22c55e', 'items' => collect()],
            'mixed' => (object) ['label' => __('À vérifier'), 'range' => __('50-69'), 'color' => '#d97706', 'items' => collect()],
            'low' => (object) ['label' => __('Faible'), 'range' => __('< 50'), 'color' => '#dc2626', 'items' => collect()],
            'unknown' => (object) ['label' => __('Non coté'), 'range' => __('N/A'), 'color' => '#64748b', 'items' => collect()],
        ]);

        foreach ($sources as $source) {
            $score = is_numeric($source->credibility) ? (int) $source->credibility : null;
            $bucket = match (true) {
                $score === null => 'unknown',
                $score >= 85 => 'very-high',
                $score >= 70 => 'high',
                $score >= 50 => 'mixed',
                default => 'low',
            };
            $buckets[$bucket]->items->push($source);
        }

        return $buckets;
    }

    private static function ownershipBuckets(Collection $sources): Collection
    {
        $colors = ['#111827', '#2085c7', '#6254b2', '#174f47', '#d12854', '#ca9700', '#64748b', '#7c3aed'];

        return $sources
            ->groupBy(fn ($source) => self::ownershipLabel($source->ownership))
            ->map(function ($items, $label) use (&$colors) {
                return (object) [
                    'label' => $label,
                    'color' => array_shift($colors) ?: '#64748b',
                    'items' => $items->values(),
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    private static function donutGradient(Collection $ownershipBuckets, int $total): string
    {
        $stops = [];
        $cursor = 0;

        foreach ($ownershipBuckets as $bucket) {
            $slice = $bucket->count * 100 / max(1, $total);
            $gap = min(1.8, max(0.45, $slice * 0.08));
            $start = $cursor;
            $colorStart = min(100, $cursor + ($slice > 3 ? $gap / 2 : 0));
            $colorEnd = max($colorStart, min(100, $cursor + $slice - ($slice > 3 ? $gap / 2 : 0)));
            $end = min(100, $cursor + $slice);

            if ($colorStart > $start) {
                $stops[] = "transparent {$start}% {$colorStart}%";
            }
            $stops[] = "{$bucket->color} {$colorStart}% {$colorEnd}%";
            if ($end > $colorEnd) {
                $stops[] = "transparent {$colorEnd}% {$end}%";
            }
            $cursor += $slice;
        }

        return $stops ? implode(', ', $stops) : '#e5e7eb 0% 100%';
    }
}
