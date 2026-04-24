<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // S91 — body-level translation. Kept as longText so feature
        // articles with embedded HTML bodies don't get truncated.
        Schema::table('posts', function (Blueprint $table): void {
            $table->longText('translated_content')->nullable()->after('translated_description');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn('translated_content');
        });
    }
};
