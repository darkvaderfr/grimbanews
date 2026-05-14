<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GrimbaEditorialCategoryFreshness
{
    /**
     * @return array<int, string>
     */
    public static function names(string $scope = 'all'): array
    {
        $scope = trim($scope);
        $normalized = mb_strtolower($scope ?: 'all');

        return match ($normalized) {
            'all' => array_values(array_unique(array_merge(
                GrimbaEditorialCategories::editionNames(),
                GrimbaEditorialCategories::topicNames()
            ))),
            'editions', 'edition', 'locations', 'editorial-locations' => GrimbaEditorialCategories::editionNames(),
            'topics', 'topic' => GrimbaEditorialCategories::topicNames(),
            default => array_values(array_filter(array_map(
                fn (string $name): string => trim($name),
                explode(',', str_replace("\n", ',', $scope))
            ))),
        };
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    public static function categories(string $scope = 'all'): Collection
    {
        $names = self::names($scope);

        if ($names === []) {
            return collect();
        }

        return DB::table('categories')
            ->where('status', 'published')
            ->whereIn('name', $names)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name', 'order']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    public static function counts(mixed $since, string $scope = 'all'): Collection
    {
        return self::categories($scope)
            ->map(function (object $category) use ($since): object {
                $category->recent_count = self::recentCount((int) $category->id, $since);

                return $category;
            });
    }

    public static function recentCount(int $categoryId, mixed $since): int
    {
        return (int) GrimbaPostRecency::wherePublishedSince(
            DB::table('posts')
                ->join('post_categories', 'post_categories.post_id', '=', 'posts.id')
                ->where('posts.status', 'published')
                ->where('post_categories.category_id', $categoryId),
            $since
        )->count();
    }
}
