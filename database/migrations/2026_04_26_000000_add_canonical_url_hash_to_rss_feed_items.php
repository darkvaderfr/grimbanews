<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * S151b — canonical-URL dedup column.
 *
 * BBC and several other RSS feeds publish the same article with
 * trailing GUID fragments (e.g. `cm293m3mr7zo#0` then `#2` on a
 * later poll). The existing (feed_id, guid) unique constraint
 * sees those as different items — we kept ingesting duplicates.
 *
 * `canonical_url_hash` = sha1(strip_fragment(strip_query(link))).
 * Strip both the URL fragment AND any tracking query params
 * (utm_*, fbclid, etc.) so two ingests of the same article via
 * different shares dedup. Indexed but NOT unique — a single
 * canonical URL can legitimately appear in two feeds (mainstream
 * + topic feed both republishing).
 *
 * Backfill: walk rss_feed_items in batches and write the hash.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rss_feed_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('rss_feed_items', 'canonical_url_hash')) {
                $table->string('canonical_url_hash', 64)->nullable()->after('link');
                $table->index('canonical_url_hash', 'rss_feed_items_canonical_idx');
            }
        });

        // Backfill in 1k batches; sha1 is cheap.
        DB::table('rss_feed_items')
            ->whereNull('canonical_url_hash')
            ->whereNotNull('link')
            ->orderBy('id')
            ->chunkById(1000, function ($rows): void {
                foreach ($rows as $row) {
                    $hash = self::canonicalize((string) $row->link);
                    if ($hash) {
                        DB::table('rss_feed_items')
                            ->where('id', $row->id)
                            ->update(['canonical_url_hash' => $hash]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('rss_feed_items', function (Blueprint $table): void {
            if (Schema::hasColumn('rss_feed_items', 'canonical_url_hash')) {
                $table->dropIndex('rss_feed_items_canonical_idx');
                $table->dropColumn('canonical_url_hash');
            }
        });
    }

    private static function canonicalize(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') return null;
        // Strip fragment + tracking params. Order matters: parse_url
        // before we drop, since we need to rebuild the URL.
        $parts = parse_url($url);
        if (! $parts || empty($parts['host'])) return null;

        $clean = ($parts['scheme'] ?? 'https') . '://' . $parts['host'];
        if (! empty($parts['path']))  $clean .= $parts['path'];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $q);
            // Drop common tracking params
            $skip = ['utm_source','utm_medium','utm_campaign','utm_term','utm_content','fbclid','gclid','mc_cid','mc_eid','ref','referrer','source'];
            foreach ($skip as $k) unset($q[$k]);
            if (! empty($q)) $clean .= '?' . http_build_query($q);
        }
        // No fragment.
        return sha1($clean);
    }
};
