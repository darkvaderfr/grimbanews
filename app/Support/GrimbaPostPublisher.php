<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrimbaPostPublisher
{
    /**
     * @param array<int, int> $ids
     */
    public static function publishDrafts(array $ids, ?CarbonInterface $publishedAt = null): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return 0;
        }

        $now = $publishedAt ?: now();
        $payload = [
            'status' => 'published',
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('posts', 'published_at')) {
            $payload['published_at'] = $now;
        }

        return DB::table('posts')
            ->whereIn('id', $ids)
            ->where('status', 'draft')
            ->update($payload);
    }
}
