<?php

namespace App\Support;

use Botble\Blog\Models\Post;
use Illuminate\Support\Collection;

class GrimbaVault
{
    public const COOKIE = 'grimba_vault';

    public static function parseIds(?string $raw, int $limit = 50): array
    {
        $ids = collect(explode(',', (string) $raw))
            ->map(static fn (string $value): int => (int) trim($value))
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->take($limit)
            ->all();

        return array_map('intval', $ids);
    }

    public static function serializeIds(array $ids, int $limit = 50): string
    {
        return implode(',', self::parseIds(implode(',', $ids), $limit));
    }

    public static function resolvePosts(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        $byId = Post::query()
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->with('categories')
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(static fn (int $id) => $byId->get($id))
            ->filter()
            ->values();
    }

    public static function staleIds(array $requestedIds, Collection $posts): array
    {
        $liveIds = $posts->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        return array_values(array_diff($requestedIds, $liveIds));
    }
}
