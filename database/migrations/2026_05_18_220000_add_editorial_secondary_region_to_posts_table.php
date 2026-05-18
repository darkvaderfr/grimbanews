<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * S-LSAT-18b (Vader 2026-05-18) — persist the secondary region
 * detected by `GrimbaArticleRegion::detectAllFromText()`.
 *
 * Stories that genuinely span two regions ("Macron meets
 * Zelensky in Kigali" → primary=europe, secondary=africa) need
 * the second tag to surface on the secondary region's page. Per
 * Wave YYY, the detector returns it; this migration gives it a
 * home in the schema.
 *
 * The column is nullable (most posts have a single region). The
 * regional scope OR-includes it so /africa shows posts where
 * EITHER `editorial_region=africa` OR
 * `editorial_secondary_region=africa`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('editorial_secondary_region', 32)->nullable()->after('editorial_region');
            $table->index('editorial_secondary_region', 'posts_editorial_secondary_region_idx');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_editorial_secondary_region_idx');
            $table->dropColumn('editorial_secondary_region');
        });
    }
};
