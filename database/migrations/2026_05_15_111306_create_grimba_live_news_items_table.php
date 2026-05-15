<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grimba_live_news_items')) {
            return;
        }

        Schema::create('grimba_live_news_items', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 40);
            $table->string('provider_item_id', 191)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_name', 191)->nullable();
            $table->string('source_domain', 191)->nullable();
            $table->string('source_country', 8)->nullable();
            $table->string('article_url', 2048);
            $table->string('article_url_hash', 64);
            $table->unsignedBigInteger('post_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique('article_url_hash', 'grimba_live_news_items_url_hash_uniq');
            $table->index(['provider', 'fetched_at'], 'grimba_live_news_items_provider_fetch_idx');
            $table->index('source_id', 'grimba_live_news_items_source_idx');
            $table->index('published_at', 'grimba_live_news_items_published_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grimba_live_news_items');
    }
};
