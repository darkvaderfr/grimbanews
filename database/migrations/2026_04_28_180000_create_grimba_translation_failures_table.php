<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grimba_translation_failures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('source_language', 8)->nullable();
            $table->string('driver_chain', 255)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'locale'], 'grimba_translation_failures_post_locale_unique');
            $table->index(['locale', 'failed_at'], 'grimba_translation_failures_locale_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grimba_translation_failures');
    }
};
