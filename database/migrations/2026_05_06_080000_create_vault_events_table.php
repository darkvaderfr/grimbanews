<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event', 16);
            $table->unsignedBigInteger('post_id');
            $table->timestamp('ts');
            $table->string('ip_hash', 64);

            $table->index(['ts', 'event'], 'vault_events_ts_event_idx');
            $table->index(['post_id', 'ts'], 'vault_events_post_ts_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_events');
    }
};
