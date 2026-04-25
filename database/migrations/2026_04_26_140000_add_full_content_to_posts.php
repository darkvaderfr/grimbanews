<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * S163 — full-article body for in-app reading (paid-tier feature).
 *
 * NewsAPI's free tier truncates the `content` field at ~200 chars, so
 * we can't render the upstream article body in-app from that pipe
 * alone. This migration adds:
 *   - posts.full_content       longText: extracted main article body
 *                              (cleaned HTML, hopefully reader-grade)
 *   - posts.full_fetched_at    timestamp: when the extractor last ran
 *                              against this post; lets the cron skip
 *                              already-extracted rows
 *   - posts.full_extract_error string:    last error reason if a
 *                              fetch failed, so the editor cockpit
 *                              can surface dead-extraction sources
 *
 * Gated behind the `grimba_full_article_active` setting at render
 * time. The column is populated for ALL posts even when off — flip
 * a switch and full reading is available without re-running ingest.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'full_content')) {
                $table->longText('full_content')->nullable()->after('content');
            }
            if (! Schema::hasColumn('posts', 'full_fetched_at')) {
                $table->timestamp('full_fetched_at')->nullable()->after('full_content');
            }
            if (! Schema::hasColumn('posts', 'full_extract_error')) {
                $table->string('full_extract_error', 191)->nullable()->after('full_fetched_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (Schema::hasColumn('posts', 'full_extract_error')) {
                $table->dropColumn('full_extract_error');
            }
            if (Schema::hasColumn('posts', 'full_fetched_at')) {
                $table->dropColumn('full_fetched_at');
            }
            if (Schema::hasColumn('posts', 'full_content')) {
                $table->dropColumn('full_content');
            }
        });
    }
};
