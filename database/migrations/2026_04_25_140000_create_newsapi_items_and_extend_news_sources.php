<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * S128 — NewsAPI.org ingest. We map an outlet on NewsAPI (api_id like
 * "le-monde", "bbc-news", "fox-news") to our internal news_sources row
 * so the existing bias / ownership / credibility data flows through to
 * the ingested article exactly the way RSS-ingested articles do.
 *
 * Dedup ledger lives in newsapi_items (parallel to rss_feed_items but
 * keyed on the article URL, which NewsAPI guarantees as unique).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (! Schema::hasColumn('news_sources', 'api_id')) {
                // NewsAPI source identifier (kebab-case, ≤ 50 chars in
                // their catalog). Nullable: not every news_sources row
                // is in NewsAPI's catalog.
                $table->string('api_id', 80)->nullable()->after('slug');
                $table->index('api_id', 'news_sources_api_id_idx');
            }
        });

        if (! Schema::hasTable('newsapi_items')) {
            Schema::create('newsapi_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('api_source_id', 80)->nullable();
                $table->string('article_url', 2048);
                $table->string('article_url_hash', 64); // sha1; index-friendly
                $table->unsignedBigInteger('post_id')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamp('fetched_at')->nullable();
                $table->timestamps();

                // Hash, not URL — MySQL key-prefix limits make 2KB urls
                // unindexable directly. sha1 collision rate at our
                // scale (millions of articles) is effectively zero.
                $table->unique('article_url_hash', 'newsapi_items_url_hash_uniq');
                $table->index('source_id', 'newsapi_items_source_idx');
                $table->index('api_source_id', 'newsapi_items_api_source_idx');
                $table->index('published_at', 'newsapi_items_published_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('newsapi_items');
        Schema::table('news_sources', function (Blueprint $table): void {
            if (Schema::hasColumn('news_sources', 'api_id')) {
                $table->dropIndex('news_sources_api_id_idx');
                $table->dropColumn('api_id');
            }
        });
    }
};
