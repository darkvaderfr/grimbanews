<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('story_clusters', function (Blueprint $table): void {
            if (! Schema::hasColumn('story_clusters', 'review_action')) {
                $table->string('review_action', 20)->nullable()->index();
            }

            if (! Schema::hasColumn('story_clusters', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('story_clusters', function (Blueprint $table): void {
            if (Schema::hasColumn('story_clusters', 'review_action')) {
                $table->dropIndex(['review_action']);
                $table->dropColumn('review_action');
            }

            if (Schema::hasColumn('story_clusters', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
        });
    }
};
