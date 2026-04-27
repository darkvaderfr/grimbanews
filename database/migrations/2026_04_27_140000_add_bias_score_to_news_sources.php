<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (! Schema::hasColumn('news_sources', 'bias_score')) {
                $table->decimal('bias_score', 3, 1)->nullable()->after('bias_rating');
                $table->index('bias_score', 'news_sources_bias_score_idx');
            }
        });

        if (Schema::hasColumn('news_sources', 'bias_score')) {
            DB::table('news_sources')
                ->whereNull('bias_score')
                ->update([
                    'bias_score' => DB::raw("CASE bias_rating WHEN 'left' THEN -1.0 WHEN 'center' THEN 0.0 WHEN 'right' THEN 1.0 ELSE NULL END"),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (Schema::hasColumn('news_sources', 'bias_score')) {
                $table->dropIndex('news_sources_bias_score_idx');
                $table->dropColumn('bias_score');
            }
        });
    }
};
