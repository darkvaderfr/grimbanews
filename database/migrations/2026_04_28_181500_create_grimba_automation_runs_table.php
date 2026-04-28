<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grimba_automation_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('job_key', 64);
            $table->string('command', 255);
            $table->string('status', 24)->default('running');
            $table->integer('exit_code')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['job_key', 'finished_at'], 'grimba_automation_runs_job_finished_idx');
            $table->index(['status', 'finished_at'], 'grimba_automation_runs_status_finished_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grimba_automation_runs');
    }
};
