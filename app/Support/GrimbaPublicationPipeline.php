<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrimbaPublicationPipeline
{
    public static function since(mixed $since): object
    {
        $publishedQuery = DB::table('posts')->where('status', 'published');
        GrimbaPostRecency::wherePublishedSince($publishedQuery, $since);

        $published24 = (int) $publishedQuery->count();
        $rssPublished24 = self::rssPublishedSince($since);
        $newsApiPublished24 = self::newsApiPublishedSince($since);
        $ingestedPublished24 = self::ingestedPublishedSince($since);

        $latestPublishedAt = DB::table('posts')
            ->where('status', 'published')
            ->max(DB::raw(GrimbaPostRecency::expression()));

        return (object) [
            'published24' => $published24,
            'rssPublished24' => $rssPublished24,
            'newsApiPublished24' => $newsApiPublished24,
            'ingestedPublished24' => $ingestedPublished24,
            'manualPublished24' => max(0, $published24 - $ingestedPublished24),
            'latestPublishedAt' => $latestPublishedAt,
        ];
    }

    private static function rssPublishedSince(mixed $since): int
    {
        if (! Schema::hasTable('rss_feed_items')) {
            return 0;
        }

        $query = DB::table('posts')
            ->join('rss_feed_items', 'rss_feed_items.post_id', '=', 'posts.id')
            ->where('posts.status', 'published')
            ->whereNotNull('rss_feed_items.post_id')
            ->distinct();

        GrimbaPostRecency::wherePublishedSince($query, $since);

        return (int) $query->count('posts.id');
    }

    private static function newsApiPublishedSince(mixed $since): int
    {
        if (! Schema::hasTable('newsapi_items')) {
            return 0;
        }

        $query = DB::table('posts')
            ->join('newsapi_items', 'newsapi_items.post_id', '=', 'posts.id')
            ->where('posts.status', 'published')
            ->whereNotNull('newsapi_items.post_id')
            ->distinct();

        GrimbaPostRecency::wherePublishedSince($query, $since);

        return (int) $query->count('posts.id');
    }

    private static function ingestedPublishedSince(mixed $since): int
    {
        $queries = [];

        if (Schema::hasTable('rss_feed_items')) {
            $rssQuery = DB::table('posts')
                ->join('rss_feed_items', 'rss_feed_items.post_id', '=', 'posts.id')
                ->where('posts.status', 'published')
                ->whereNotNull('rss_feed_items.post_id')
                ->select('posts.id');

            GrimbaPostRecency::wherePublishedSince($rssQuery, $since);
            $queries[] = $rssQuery;
        }

        if (Schema::hasTable('newsapi_items')) {
            $newsApiQuery = DB::table('posts')
                ->join('newsapi_items', 'newsapi_items.post_id', '=', 'posts.id')
                ->where('posts.status', 'published')
                ->whereNotNull('newsapi_items.post_id')
                ->select('posts.id');

            GrimbaPostRecency::wherePublishedSince($newsApiQuery, $since);
            $queries[] = $newsApiQuery;
        }

        if ($queries === []) {
            return 0;
        }

        $union = array_shift($queries);
        foreach ($queries as $query) {
            $union->union($query);
        }

        return (int) DB::query()
            ->fromSub($union, 'ingested_publications')
            ->distinct()
            ->count('id');
    }
}
