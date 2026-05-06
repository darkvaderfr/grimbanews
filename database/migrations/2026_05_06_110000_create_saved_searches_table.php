<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('saved_searches')) {
            return;
        }

        Schema::create('saved_searches', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('member_id')->index();
            $table->string('search_query', 180);
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('bias', 24)->nullable()->index();
            $table->string('owner', 180)->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('search_hash', 64);
            $table->boolean('active')->default(true)->index();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'search_hash'], 'saved_searches_member_hash_unique');
            $table->index(['active', 'last_sent_at'], 'saved_searches_active_sent_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
