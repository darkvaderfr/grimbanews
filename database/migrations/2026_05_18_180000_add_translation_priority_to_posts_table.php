<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * S-LSAT-08 (Vader 2026-05-18) — rule-engine priority column.
 *
 * `posts.translation_priority` is a tinyint pre-computed by the
 * S-LSAT rule engine. Higher values jump the translate-pending
 * queue ahead of normal posts.
 *
 *   0 = default / no rule has elevated this post
 *   1 = rule fired (popularity threshold OR forced region)
 *   2 = reserved for future "editorial pin" (operator-flagged)
 *
 * Indexed so `GrimbaTranslatePending` can `ORDER BY translation_priority DESC,
 * views DESC` cheaply on a corpus of 4k+ rows.
 *
 * Additive + nullable-equivalent (default 0) → safe rollback. The
 * rule engine reads/writes through `Schema::hasColumn` guards so
 * the column missing is a no-op until this migration runs.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'translation_priority')) {
                $table->unsignedTinyInteger('translation_priority')->default(0)->after('translated_at');
                $table->index('translation_priority', 'posts_translation_priority_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (Schema::hasColumn('posts', 'translation_priority')) {
                try {
                    $table->dropIndex('posts_translation_priority_idx');
                } catch (\Throwable) {
                    // Index may already be gone on a partial-rollback DB.
                }
                $table->dropColumn('translation_priority');
            }
        });
    }
};
