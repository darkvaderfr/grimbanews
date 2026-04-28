<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (! Schema::hasColumn('news_sources', 'logo_url')) {
                $table->string('logo_url', 500)->nullable()->after('website');
            }
            if (! Schema::hasColumn('news_sources', 'logo_status')) {
                $table->string('logo_status', 32)->default('unknown')->after('logo_url');
                $table->index('logo_status', 'news_sources_logo_status_idx');
            }
            if (! Schema::hasColumn('news_sources', 'logo_checked_at')) {
                $table->timestamp('logo_checked_at')->nullable()->after('logo_status');
            }
            if (! Schema::hasColumn('news_sources', 'logo_error')) {
                $table->string('logo_error', 500)->nullable()->after('logo_checked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (Schema::hasColumn('news_sources', 'logo_status')) {
                $table->dropIndex('news_sources_logo_status_idx');
            }
            foreach (['logo_error', 'logo_checked_at', 'logo_status', 'logo_url'] as $column) {
                if (Schema::hasColumn('news_sources', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
