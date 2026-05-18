<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grimba_live_news_provider_runs')) {
            return;
        }

        Schema::create('grimba_live_news_provider_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 40);
            $table->string('query_label', 500)->nullable();
            $table->string('status', 24)->default('pending');
            $table->unsignedInteger('returned_articles')->default(0);
            $table->unsignedInteger('ingested_articles')->default(0);
            $table->unsignedInteger('deduped_articles')->default(0);
            $table->unsignedInteger('skipped_articles')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'started_at'], 'grimba_live_runs_provider_started_idx');
            $table->index(['status', 'started_at'], 'grimba_live_runs_status_started_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grimba_live_news_provider_runs');
    }
};
