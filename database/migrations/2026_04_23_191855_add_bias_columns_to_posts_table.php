<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Bias rating: left, center, right, unknown
            $table->string('bias_rating', 20)->nullable()->default('unknown')->after('status');

            // Blindspot flag: true if story covered by only one political side
            $table->boolean('is_blindspot')->default(false)->after('bias_rating');

            // Source credibility score (0-100)
            $table->integer('credibility_score')->nullable()->after('is_blindspot');

            // Media ownership type: corporate, state, independent, nonprofit
            $table->string('ownership_type', 50)->nullable()->after('credibility_score');

            // Index for bias filtering
            $table->index('bias_rating');
            $table->index('is_blindspot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['bias_rating']);
            $table->dropIndex(['is_blindspot']);
            $table->dropColumn(['bias_rating', 'is_blindspot', 'credibility_score', 'ownership_type']);
        });
    }
};
