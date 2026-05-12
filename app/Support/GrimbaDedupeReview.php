<?php

namespace App\Support;

use App\Services\GrimbaUrlCanonicalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrimbaDedupeReview
{
    public static function titleGroups(?int $sourceId = null, int $limit = 2000): Collection
    {
        return DB::table('posts')
            ->when($sourceId, fn ($query) => $query->where('source_id', $sourceId))
            ->select(
                'name',
                'source_id',
                DB::raw('GROUP_CONCAT(id ORDER BY id ASC) as post_ids'),
                DB::raw('MIN(id) as first_post_id'),
                DB::raw('MAX(id) as latest_post_id'),
                DB::raw('COUNT(*) as c')
            )
            ->groupBy('name', 'source_id')
            ->having('c', '>', 1)
            ->orderByDesc('c')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{same_url: Collection<int, object>, ignored: Collection<int, object>, unresolved: Collection<int, object>}
     */
    public static function partitionTitleGroups(Collection $groups, GrimbaUrlCanonicalizer $canon): array
    {
        [$sameUrlGroups, $remaining] = $groups->partition(
            fn (object $group): bool => self::titleGroupHasSingleCanonicalUrl($group, $canon)
        );
        [$ignored, $unresolved] = $remaining->partition(
            fn (object $group): bool => self::isKnownRecurringMediaGroup($group)
        );

        return [
            'same_url' => $sameUrlGroups->values(),
            'ignored' => $ignored->values(),
            'unresolved' => $unresolved->values(),
        ];
    }

    /**
     * @return array<int, int>
     */
    public static function idsForTitleGroup(object $group): array
    {
        $ids = DB::table('posts')
            ->where('name', $group->name)
            ->when(
                $group->source_id,
                fn ($query) => $query->where('source_id', $group->source_id),
                fn ($query) => $query->whereNull('source_id')
            )
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        foreach ([(int) ($group->first_post_id ?? 0), (int) ($group->latest_post_id ?? 0)] as $fallbackId) {
            if ($fallbackId > 0 && ! in_array($fallbackId, $ids, true)) {
                $ids[] = $fallbackId;
            }
        }

        sort($ids);

        return $ids;
    }

    public static function titleGroupHasSingleCanonicalUrl(object $group, GrimbaUrlCanonicalizer $canon): bool
    {
        $ids = self::idsForTitleGroup($group);
        if (count($ids) < 2) {
            return false;
        }

        $hashesByPost = self::canonicalHashesByPost($ids, $canon);
        $allHashes = [];

        foreach ($ids as $id) {
            if (empty($hashesByPost[$id])) {
                return false;
            }

            foreach (array_keys($hashesByPost[$id]) as $hash) {
                $allHashes[$hash] = true;
            }
        }

        return count($allHashes) === 1;
    }

    public static function isKnownRecurringMediaGroup(object $group): bool
    {
        $sourceName = $group->source_id
            ? (string) (DB::table('news_sources')->where('id', $group->source_id)->value('name') ?: '')
            : '';

        if (strtolower($sourceName) !== 'bbc') {
            return false;
        }

        $urls = self::urlsForPosts(self::idsForTitleGroup($group));
        if ($urls->count() < 2) {
            return false;
        }

        $allBbcSounds = $urls->every(function (string $url): bool {
            $parts = parse_url($url);
            $host = strtolower((string) ($parts['host'] ?? ''));
            $path = (string) ($parts['path'] ?? '');

            return in_array($host, ['bbc.co.uk', 'www.bbc.co.uk'], true)
                && str_starts_with($path, '/sounds/play/');
        });

        if (! $allBbcSounds) {
            return false;
        }

        return $urls->contains(function (string $url): bool {
            $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');

            return str_contains($path, '/live:');
        });
    }

    /**
     * @param array<int, int> $postIds
     * @return Collection<int, string>
     */
    public static function sampleUrlsForPosts(array $postIds): Collection
    {
        return self::urlsForPosts($postIds)->take(3)->values();
    }

    /**
     * @param array<int, int> $postIds
     * @return Collection<int, string>
     */
    public static function urlsForPosts(array $postIds): Collection
    {
        $urls = collect();

        if (Schema::hasTable('rss_feed_items')) {
            $urls = $urls->merge(
                DB::table('rss_feed_items')
                    ->whereIn('post_id', $postIds)
                    ->whereNotNull('link')
                    ->pluck('link')
            );
        }

        if (Schema::hasTable('newsapi_items')) {
            $urls = $urls->merge(
                DB::table('newsapi_items')
                    ->whereIn('post_id', $postIds)
                    ->whereNotNull('article_url')
                    ->pluck('article_url')
            );
        }

        $urls = $urls->merge(
            DB::table('posts')
                ->whereIn('id', $postIds)
                ->whereNotNull('content')
                ->pluck('content')
                ->map(fn ($content): ?string => GrimbaArticleText::firstHttpUrlFromHtml((string) $content))
                ->filter()
        );

        return $urls
            ->map(fn ($url): string => (string) $url)
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * @param array<int, int> $postIds
     * @return array<int, array<string, bool>>
     */
    private static function canonicalHashesByPost(array $postIds, GrimbaUrlCanonicalizer $canon): array
    {
        $hashes = [];

        foreach ($postIds as $postId) {
            $hashes[(int) $postId] = [];
        }

        if (Schema::hasTable('rss_feed_items')) {
            DB::table('rss_feed_items')
                ->whereIn('post_id', $postIds)
                ->whereNotNull('link')
                ->get(['post_id', 'link'])
                ->each(function (object $row) use (&$hashes, $canon): void {
                    $hash = $canon->hash((string) $row->link);
                    if ($hash) {
                        $hashes[(int) $row->post_id][$hash] = true;
                    }
                });
        }

        if (Schema::hasTable('newsapi_items')) {
            DB::table('newsapi_items')
                ->whereIn('post_id', $postIds)
                ->whereNotNull('article_url')
                ->get(['post_id', 'article_url'])
                ->each(function (object $row) use (&$hashes, $canon): void {
                    $hash = $canon->hash((string) $row->article_url);
                    if ($hash) {
                        $hashes[(int) $row->post_id][$hash] = true;
                    }
                });
        }

        DB::table('posts')
            ->whereIn('id', $postIds)
            ->whereNotNull('content')
            ->get(['id', 'content'])
            ->each(function (object $row) use (&$hashes, $canon): void {
                $url = GrimbaArticleText::firstHttpUrlFromHtml((string) $row->content);
                if (! $url) {
                    return;
                }

                $hash = $canon->hash($url);
                if ($hash) {
                    $hashes[(int) $row->id][$hash] = true;
                }
            });

        return $hashes;
    }
}
