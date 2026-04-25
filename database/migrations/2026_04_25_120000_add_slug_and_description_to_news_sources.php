<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * S111 — per-source pages need a stable URL handle. We store a slug
 * column + a longer description blob (used as the editorial standfirst
 * on /sources/{slug}). Existing 20 rows get auto-slugged from name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (! Schema::hasColumn('news_sources', 'slug')) {
                $table->string('slug', 191)->nullable()->after('name');
            }
            if (! Schema::hasColumn('news_sources', 'description')) {
                $table->text('description')->nullable()->after('language');
            }
        });

        // Backfill slugs. Cap at 191 chars to fit MySQL utf8mb4 index.
        $rows = DB::table('news_sources')->get(['id', 'name']);
        $taken = [];
        foreach ($rows as $row) {
            $base = Str::slug($row->name) ?: 'source-' . $row->id;
            $base = mb_substr($base, 0, 180);
            $slug = $base;
            $i = 2;
            while (in_array($slug, $taken, true)
                || DB::table('news_sources')->where('slug', $slug)->where('id', '!=', $row->id)->exists()) {
                $slug = $base . '-' . $i;
                $i++;
            }
            $taken[] = $slug;
            DB::table('news_sources')->where('id', $row->id)->update(['slug' => $slug]);
        }

        // Now make slug unique + non-null. Sources can't share a URL.
        Schema::table('news_sources', function (Blueprint $table): void {
            $table->string('slug', 191)->nullable(false)->change();
            $table->unique('slug', 'news_sources_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (Schema::hasColumn('news_sources', 'slug')) {
                $table->dropUnique('news_sources_slug_unique');
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('news_sources', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
