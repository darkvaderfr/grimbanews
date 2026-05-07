<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'published_at')) {
            return;
        }

        DB::table('posts')
            ->where('status', 'published')
            ->whereColumn('published_at', '>', 'created_at')
            ->where('published_at', '<', '2026-05-07 01:39:30')
            ->update([
                'published_at' => DB::raw('created_at'),
            ]);
    }

    public function down(): void
    {
        //
    }
};
