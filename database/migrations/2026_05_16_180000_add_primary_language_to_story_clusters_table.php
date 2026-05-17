<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * S-LANG-11 (Vader 2026-05-16) — denormalised dossier-level language
 * metadata. `primary_language` is the modal language of a cluster's
 * published posts; `language_mix_json` carries the raw {fr:N, en:M,
 * unknown:K} counts so the UI can show e.g. "8 sources FR, 3 EN" without
 * a new query. `language_recomputed_at` lets the daily recompute job
 * skip clusters that haven't changed since the last sweep.
 *
 * Decision (architect plan): no static `language` column — the modal
 * can flip as new articles land. These three columns are a recompute-
 * authoritative cache.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('story_clusters', function (Blueprint $table): void {
            if (! Schema::hasColumn('story_clusters', 'primary_language')) {
                $table->string('primary_language', 5)->nullable()->after('description');
                $table->index('primary_language');
            }
            if (! Schema::hasColumn('story_clusters', 'language_mix_json')) {
                $table->json('language_mix_json')->nullable()->after('primary_language');
            }
            if (! Schema::hasColumn('story_clusters', 'language_recomputed_at')) {
                $table->timestamp('language_recomputed_at')->nullable()->after('language_mix_json');
            }
        });
    }

    public function down(): void
    {
        Schema::table('story_clusters', function (Blueprint $table): void {
            foreach (['primary_language', 'language_mix_json', 'language_recomputed_at'] as $col) {
                if (Schema::hasColumn('story_clusters', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
