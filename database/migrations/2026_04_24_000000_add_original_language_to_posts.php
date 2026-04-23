<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('original_language', 5)->nullable()->after('source_id');
            $table->index('original_language');
        });

        // Backfill from news_sources.language where source_id is set.
        $sources = DB::table('news_sources')->pluck('language', 'id');
        foreach ($sources as $id => $lang) {
            DB::table('posts')
                ->where('source_id', $id)
                ->whereNull('original_language')
                ->update(['original_language' => $lang]);
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['original_language']);
            $table->dropColumn('original_language');
        });
    }
};
