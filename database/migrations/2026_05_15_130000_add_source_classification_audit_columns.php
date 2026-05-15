<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            if (! Schema::hasColumn('news_sources', 'classification_confidence')) {
                $table->unsignedTinyInteger('classification_confidence')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('news_sources', 'classification_method')) {
                $table->string('classification_method', 80)->nullable()->after('classification_confidence');
            }

            if (! Schema::hasColumn('news_sources', 'classified_at')) {
                $table->timestamp('classified_at')->nullable()->after('classification_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            foreach (['classified_at', 'classification_method', 'classification_confidence'] as $column) {
                if (Schema::hasColumn('news_sources', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
