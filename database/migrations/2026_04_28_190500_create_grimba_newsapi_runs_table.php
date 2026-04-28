<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grimba_newsapi_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('endpoint', 32);
            $table->string('country', 8)->nullable();
            $table->string('category', 40)->nullable();
            $table->string('language', 8)->nullable();
            $table->string('query_label', 500);
            $table->json('request_params')->nullable();
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('total_results')->default(0);
            $table->unsignedInteger('returned_articles')->default(0);
            $table->unsignedInteger('ingested_articles')->default(0);
            $table->unsignedInteger('deduped_articles')->default(0);
            $table->unsignedInteger('skipped_articles')->default(0);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['endpoint', 'country', 'category'], 'grimba_newsapi_runs_scope_idx');
            $table->index(['status', 'started_at'], 'grimba_newsapi_runs_status_started_idx');
            $table->index('started_at', 'grimba_newsapi_runs_started_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grimba_newsapi_runs');
    }
};
