<?php

use App\Services\GrimbaUrlCanonicalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rss_feed_items') || ! Schema::hasColumn('rss_feed_items', 'canonical_url_hash')) {
            return;
        }

        $canonicalizer = app(GrimbaUrlCanonicalizer::class);

        DB::table('rss_feed_items')
            ->whereNotNull('link')
            ->orderBy('id')
            ->chunkById(1000, function ($rows) use ($canonicalizer): void {
                foreach ($rows as $row) {
                    $hash = $canonicalizer->hash((string) $row->link);
                    if (! $hash || $hash === $row->canonical_url_hash) {
                        continue;
                    }

                    DB::table('rss_feed_items')
                        ->where('id', $row->id)
                        ->update([
                            'canonical_url_hash' => $hash,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // The previous hashes were derived code-side and cannot be
        // reconstructed after deploy without the old canonicalizer.
    }
};
