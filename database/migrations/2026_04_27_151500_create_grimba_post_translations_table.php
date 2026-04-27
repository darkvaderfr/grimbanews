<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grimba_post_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('locale', 8);
            $table->text('translated_name');
            $table->text('translated_description')->nullable();
            $table->longText('translated_content')->nullable();
            $table->string('translation_driver', 64)->nullable();
            $table->timestamp('translated_at')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'locale'], 'grimba_post_translations_post_locale_unique');
            $table->index(['locale', 'translated_at'], 'grimba_post_translations_locale_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grimba_post_translations');
    }
};
