<?php

namespace App\Support;

use App\Services\GrimbaUrlCanonicalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class GrimbaArticleDedupe
{
    public static function hasSeen(
        string $url,
        ?string $title = null,
        ?string $sourceName = null,
        ?string $sourceDomain = null
    ): bool {
        $url = trim($url);
        if ($url !== '' && self::hasSeenUrl($url)) {
            return true;
        }

        return self::hasMatchingPostTitle($title, $sourceName, $sourceDomain);
    }

    private static function hasSeenUrl(string $url): bool
    {
        $rawHash = sha1($url);

        foreach (['newsapi_items', 'grimba_live_news_items'] as $table) {
            if (Schema::hasTable($table)
                && Schema::hasColumn($table, 'article_url_hash')
                && DB::table($table)->where('article_url_hash', $rawHash)->exists()) {
                return true;
            }
        }

        $canonicalHash = self::canonicalHash($url);
        if ($canonicalHash === null) {
            return false;
        }

        if (Schema::hasTable('rss_feed_items')
            && Schema::hasColumn('rss_feed_items', 'canonical_url_hash')
            && DB::table('rss_feed_items')->where('canonical_url_hash', $canonicalHash)->exists()) {
            return true;
        }

        foreach (['newsapi_items', 'grimba_live_news_items'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'article_url')) {
                continue;
            }

            $urls = DB::table($table)
                ->whereNotNull('article_url')
                ->when(Schema::hasColumn($table, 'fetched_at'), function ($query): void {
                    $query->where('fetched_at', '>=', now()->subDays(45));
                })
                ->orderByDesc('id')
                ->limit(1500)
                ->pluck('article_url');

            foreach ($urls as $seenUrl) {
                if (self::canonicalHash((string) $seenUrl) === $canonicalHash) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function hasMatchingPostTitle(?string $title, ?string $sourceName, ?string $sourceDomain): bool
    {
        $titleKey = self::normaliseTitle((string) $title);
        if ($titleKey === '') {
            return false;
        }

        $sourceKey = self::normaliseSource((string) $sourceName);
        $domain = self::normaliseHost((string) $sourceDomain);
        if ($sourceKey === '' && $domain === '') {
            return false;
        }

        if (! Schema::hasTable('posts')) {
            return false;
        }

        $rows = DB::table('posts')
            ->leftJoin('news_sources', 'news_sources.id', '=', 'posts.source_id')
            ->where('posts.created_at', '>=', now()->subDays(45))
            ->orderByDesc('posts.id')
            ->limit(2000)
            ->get([
                'posts.name',
                'posts.source_name',
                'news_sources.name as source_record_name',
                'news_sources.website as source_website',
            ]);

        foreach ($rows as $row) {
            if (self::normaliseTitle((string) $row->name) !== $titleKey) {
                continue;
            }

            if (self::sourceMatches($sourceKey, $domain, $row)) {
                return true;
            }
        }

        return false;
    }

    private static function sourceMatches(string $sourceKey, string $domain, object $row): bool
    {
        if ($sourceKey !== '') {
            foreach ([(string) $row->source_name, (string) $row->source_record_name] as $candidate) {
                if (self::normaliseSource($candidate) === $sourceKey) {
                    return true;
                }
            }
        }

        if ($domain === '') {
            return false;
        }

        $sourceHost = self::hostFromUrl((string) $row->source_website);
        if ($sourceHost === '') {
            return false;
        }

        return $sourceHost === $domain
            || str_ends_with($sourceHost, '.' . $domain)
            || str_ends_with($domain, '.' . $sourceHost);
    }

    private static function canonicalHash(string $url): ?string
    {
        try {
            $hash = app(GrimbaUrlCanonicalizer::class)->hash($url);

            return is_string($hash) && $hash !== '' ? $hash : null;
        } catch (Throwable) {
            return null;
        }
    }

    private static function normaliseTitle(string $title): string
    {
        $title = html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = preg_replace('/\s+[–—|-]\s+[^–—|]+$/u', '', $title) ?: $title;
        $title = Str::lower($title);
        $title = preg_replace('/[^\pL\pN]+/u', ' ', $title) ?: $title;

        return trim(preg_replace('/\s+/u', ' ', $title) ?: $title);
    }

    private static function normaliseSource(string $source): string
    {
        $source = Str::lower(html_entity_decode(strip_tags($source), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $source = preg_replace('/\b(news|actualites|actualités|media|média)\b/u', '', $source) ?: $source;
        $source = preg_replace('/[^\pL\pN]+/u', ' ', $source) ?: $source;

        return trim(preg_replace('/\s+/u', ' ', $source) ?: $source);
    }

    private static function hostFromUrl(string $url): string
    {
        $raw = trim($url);
        if ($raw === '') {
            return '';
        }

        $url = preg_match('#^https?://#i', $raw) ? $raw : 'https://' . ltrim($raw, '/');
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) ? self::normaliseHost($host) : '';
    }

    private static function normaliseHost(string $host): string
    {
        $host = Str::lower(trim($host));
        $host = preg_replace('/^(www|m|amp)\./', '', $host) ?: $host;

        return trim($host, '.');
    }
}
