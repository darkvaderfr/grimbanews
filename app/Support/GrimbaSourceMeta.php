<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrimbaSourceMeta
{
    /**
     * @param array<int, int|string|null> $sourceIds
     * @param array<int, string> $columns
     */
    public static function forIds(array $sourceIds, array $columns = []): Collection
    {
        $ids = collect($sourceIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === [] || ! Schema::hasTable('news_sources')) {
            return collect();
        }

        $columns = $columns ?: [
            'id',
            'name',
            'website',
            'bias_rating',
            'bias_score',
            'ownership_type',
            'credibility_score',
            'owner_name',
            'country',
            'logo_url',
            'logo_status',
            'logo_checked_at',
        ];

        $select = collect($columns)
            ->prepend('id')
            ->unique()
            ->filter(fn (string $column) => $column === 'id' || Schema::hasColumn('news_sources', $column))
            ->values()
            ->all();

        return DB::table('news_sources')
            ->whereIn('id', $ids)
            ->get($select)
            ->keyBy('id');
    }
}
