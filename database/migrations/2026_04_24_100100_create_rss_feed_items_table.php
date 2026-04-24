<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ingest ledger — every RSS/Atom item the poller has ever seen,
        // with a pointer to the Post row it produced (if any). Keeps
        // dedup independent of the posts table and lets us re-ingest
        // without back-filling guid columns onto Botble's Post model.
        Schema::create('rss_feed_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('feed_id');
            $table->string('guid', 500);
            $table->string('link', 1000)->nullable();
            $table->string('title_snapshot', 500)->nullable();
            $table->unsignedBigInteger('post_id')->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['feed_id', 'guid']);
            $table->index('post_id');
            $table->index('seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_feed_items');
    }
};
