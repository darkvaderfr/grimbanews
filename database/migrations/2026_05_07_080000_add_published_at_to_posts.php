<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $added = false;

        Schema::table('posts', function (Blueprint $table) use (&$added): void {
            if (! Schema::hasColumn('posts', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
                $table->index(['status', 'published_at'], 'posts_status_published_at_index');
                $added = true;
            }
        });

        if ($added || Schema::hasColumn('posts', 'published_at')) {
            DB::table('posts')
                ->where('status', 'published')
                ->whereNull('published_at')
                ->update([
                    'published_at' => DB::raw('created_at'),
                ]);

            DB::table('posts')
                ->where('status', 'published')
                ->whereColumn('updated_at', '>', 'created_at')
                ->where('updated_at', '>=', now()->subDays(2))
                ->update([
                    'published_at' => DB::raw('updated_at'),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('posts', 'published_at')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            try {
                $table->dropIndex('posts_status_published_at_index');
            } catch (\Throwable) {
                //
            }

            $table->dropColumn('published_at');
        });
    }
};
