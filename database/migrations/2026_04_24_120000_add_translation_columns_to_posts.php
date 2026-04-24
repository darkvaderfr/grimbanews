<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            // Translated variants of the two text fields readers see in a
            // grid / hero card. Left nullable so posts authored directly
            // in the target language skip the round-trip entirely.
            $table->text('translated_name')->nullable()->after('name');
            $table->text('translated_description')->nullable()->after('translated_name');
            // Target locale the translation is expressed in (e.g. 'fr').
            // Kept so we can detect when the reader's locale changes and
            // invalidate stale translations instead of serving French when
            // the reader switched to English.
            $table->string('translated_to', 8)->nullable()->after('translated_description');
            $table->timestamp('translated_at')->nullable()->after('translated_to');
            $table->string('translation_driver', 32)->nullable()->after('translated_at');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->index(['translated_to', 'original_language'], 'posts_translated_to_idx');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex('posts_translated_to_idx');
            $table->dropColumn([
                'translated_name',
                'translated_description',
                'translated_to',
                'translated_at',
                'translation_driver',
            ]);
        });
    }
};
