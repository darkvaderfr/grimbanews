<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * S-LANG-08 (Vader 2026-05-17) — tag the locale of every NobuAI
 * summary on the post row so the reader side can decide whether to
 * serve it directly, translate it, or hide it for the wrong locale.
 *
 * Backfill: every existing row with a non-empty `summary_nobuai`
 * gets `'fr'` because the only writer today
 * (`GrimbaGenerateNobuAiSummaries`) hardcodes a French prompt.
 * Future cluster-aware generators can produce EN summaries; the
 * column gives us the room to record that without ambiguity.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'summary_nobuai_locale')) {
                $table->string('summary_nobuai_locale', 5)->nullable()->after('summary_driver');
                $table->index('summary_nobuai_locale');
            }
        });

        // Backfill known-FR rows. Idempotent; cheap on the current
        // ~3.4k-row posts table (single UPDATE, milliseconds).
        //
        // CEILING (Zen audit 2026-05-17): if the posts table grows past
        // ~100k rows, move this backfill to a separate chunked artisan
        // command BEFORE running this migration in prod — the single
        // UPDATE here will hold the table-write lock for too long.
        if (Schema::hasColumn('posts', 'summary_nobuai_locale')) {
            DB::table('posts')
                ->whereNotNull('summary_nobuai')
                ->where('summary_nobuai', '!=', '')
                ->whereNull('summary_nobuai_locale')
                ->update(['summary_nobuai_locale' => 'fr']);
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (Schema::hasColumn('posts', 'summary_nobuai_locale')) {
                $table->dropColumn('summary_nobuai_locale');
            }
        });
    }
};
