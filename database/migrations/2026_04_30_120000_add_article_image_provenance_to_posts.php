<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Article image provenance for GrimbaNews.
 *
 * `posts.image` already stores the renderable hero image. These
 * columns preserve how that image was discovered so editors can audit
 * weak extraction, stale publisher metadata, and sources that never
 * expose usable article media.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $after = Schema::hasColumn('posts', 'image') ? 'image' : 'content';

            if (! Schema::hasColumn('posts', 'image_source_url')) {
                $table->string('image_source_url', 2048)->nullable()->after($after);
                $after = 'image_source_url';
            }
            if (! Schema::hasColumn('posts', 'image_extraction_method')) {
                $table->string('image_extraction_method', 32)->nullable()->after($after);
                $after = 'image_extraction_method';
            }
            if (! Schema::hasColumn('posts', 'image_extracted_at')) {
                $table->timestamp('image_extracted_at')->nullable()->after($after);
                $after = 'image_extracted_at';
            }
            if (! Schema::hasColumn('posts', 'image_extract_error')) {
                $table->string('image_extract_error', 191)->nullable()->after($after);
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            foreach (['image_extract_error', 'image_extracted_at', 'image_extraction_method', 'image_source_url'] as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
