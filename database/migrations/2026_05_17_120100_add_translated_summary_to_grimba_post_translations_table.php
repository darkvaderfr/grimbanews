<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * S-LANG-09 (Vader 2026-05-17) — NobuAI summary translations live on
 * the existing per-locale join table alongside name/description/content
 * translations. Avoids a separate `grimba_post_summary_translations`
 * table (architect's "one source of truth" decision).
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('grimba_post_translations')) {
            return;
        }
        Schema::table('grimba_post_translations', function (Blueprint $table): void {
            if (! Schema::hasColumn('grimba_post_translations', 'translated_summary')) {
                $table->longText('translated_summary')->nullable()->after('translated_content');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('grimba_post_translations')) {
            return;
        }
        Schema::table('grimba_post_translations', function (Blueprint $table): void {
            if (Schema::hasColumn('grimba_post_translations', 'translated_summary')) {
                $table->dropColumn('translated_summary');
            }
        });
    }
};
