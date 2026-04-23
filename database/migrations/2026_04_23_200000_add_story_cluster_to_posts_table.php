<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('story_cluster_id')->nullable()->after('ownership_type');
            $table->string('source_name', 120)->nullable()->after('story_cluster_id');
            $table->index('story_cluster_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['story_cluster_id']);
            $table->dropColumn(['story_cluster_id', 'source_name']);
        });
    }
};
