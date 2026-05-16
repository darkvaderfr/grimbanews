<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editorial region as a first-class tag on every post.
 *
 * Per Vader directive 2026-05-16: "a person in the DRC does not care
 * about what is going on in Germany and vice versa". Region scope used
 * to derive at render time via a join through news_sources.country —
 * which works but is fragile (sources without a country, sources whose
 * country differs from their editorial market). This migration moves
 * the answer to the post itself, written at ingest time, indexed for
 * fast scoping.
 *
 * Values:
 *   africa | europe | americas | international | NULL (unclassified)
 *
 * Population:
 *   - New ingest paths set the column at insert time (GrimbaPostPublisher,
 *     the live-news + newsapi + rss fetchers).
 *   - Existing rows backfilled by `grimba:backfill-editorial-regions`,
 *     which classifies via the news_sources.country join the scope
 *     previously did on every query.
 *
 * NOT run automatically — Vader's standing rule is "migrations should
 * NOT be applied unless explicitly requested" (resume prompt §1).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'editorial_region')) {
                $table->string('editorial_region', 32)->nullable()->after('source_id');
            }
        });

        Schema::table('posts', function (Blueprint $table): void {
            $existing = collect(Schema::getIndexes('posts'))
                ->pluck('name')
                ->all();

            if (! in_array('posts_editorial_region_idx', $existing, true)) {
                $table->index('editorial_region', 'posts_editorial_region_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $existing = collect(Schema::getIndexes('posts'))
                ->pluck('name')
                ->all();

            if (in_array('posts_editorial_region_idx', $existing, true)) {
                $table->dropIndex('posts_editorial_region_idx');
            }

            if (Schema::hasColumn('posts', 'editorial_region')) {
                $table->dropColumn('editorial_region');
            }
        });
    }
};
