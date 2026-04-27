<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'summary_nobuai')) {
                $table->text('summary_nobuai')->nullable()->after('translated_content');
            }

            if (! Schema::hasColumn('posts', 'summary_generated_at')) {
                $table->timestamp('summary_generated_at')->nullable()->after('summary_nobuai');
            }

            if (! Schema::hasColumn('posts', 'summary_driver')) {
                $table->string('summary_driver', 40)->nullable()->after('summary_generated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (Schema::hasColumn('posts', 'summary_driver')) {
                $table->dropColumn('summary_driver');
            }

            if (Schema::hasColumn('posts', 'summary_generated_at')) {
                $table->dropColumn('summary_generated_at');
            }

            if (Schema::hasColumn('posts', 'summary_nobuai')) {
                $table->dropColumn('summary_nobuai');
            }
        });
    }
};
