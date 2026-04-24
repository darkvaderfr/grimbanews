<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rss_feeds', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->string('url', 500);
            $table->string('feed_format', 16)->default('rss'); // rss | atom
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedInteger('consecutive_failures')->default(0);
            $table->unsignedInteger('items_ingested')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('source_id');
            $table->index('is_active');
            $table->unique(['source_id', 'url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_feeds');
    }
};
