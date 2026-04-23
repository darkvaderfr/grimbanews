<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('website', 255)->nullable();
            $table->string('bias_rating', 20)->default('unknown');
            $table->string('ownership_type', 50)->nullable();
            $table->unsignedTinyInteger('credibility_score')->nullable();
            $table->string('country', 3)->nullable();
            $table->string('language', 5)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('bias_rating');
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
