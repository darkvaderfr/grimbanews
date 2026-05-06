<?php

namespace App\Support;

use Botble\Blog\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrimbaVault
{
    public const COOKIE = 'grimba_vault';

    public static function parseIds(?string $raw, int $limit = 50): array
    {
        $ids = collect(explode(',', (string) $raw))
            ->map(static fn (string $value): int => (int) trim($value))
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->take($limit)
            ->all();

        return array_map('intval', $ids);
    }

    public static function serializeIds(array $ids, int $limit = 50): string
    {
        return implode(',', self::parseIds(implode(',', $ids), $limit));
    }

    public static function resolvePosts(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        $byId = Post::withoutGlobalScope('grimba_region')
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->with('categories')
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(static fn (int $id) => $byId->get($id))
            ->filter()
            ->values();
    }

    public static function staleIds(array $requestedIds, Collection $posts): array
    {
        $liveIds = $posts->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        return array_values(array_diff($requestedIds, $liveIds));
    }

    public static function memberDigestIds(?object $member): array
    {
        return self::parseIds((string) data_get($member, 'vault_digest_post_ids', ''));
    }

    public static function memberDigestEnabled(?object $member): bool
    {
        if (! $member || ! self::memberDigestColumnsReady()) {
            return false;
        }

        $memberId = (int) data_get($member, 'id', 0);
        if ($memberId <= 0) {
            return false;
        }

        return (bool) DB::table('members')
            ->where('id', $memberId)
            ->value('weekly_vault_digest');
    }

    public static function syncMemberDigestSnapshot(?object $member, array $ids, ?bool $enabled = null): void
    {
        if (! $member || ! self::memberDigestColumnsReady()) {
            return;
        }

        $memberId = (int) data_get($member, 'id', 0);
        if ($memberId <= 0) {
            return;
        }

        if ($enabled === null && ! self::memberDigestEnabled($member)) {
            return;
        }

        $updates = [
            'updated_at' => now(),
        ];

        if ($enabled !== null) {
            $updates['weekly_vault_digest'] = $enabled;
        }

        if ($enabled === false) {
            $updates['vault_digest_post_ids'] = null;
            $updates['vault_digest_synced_at'] = null;
        } else {
            $updates['vault_digest_post_ids'] = self::serializeIds($ids);
            $updates['vault_digest_synced_at'] = now();
        }

        DB::table('members')->where('id', $memberId)->update($updates);
    }

    private static function memberDigestColumnsReady(): bool
    {
        return Schema::hasTable('members')
            && Schema::hasColumn('members', 'weekly_vault_digest')
            && Schema::hasColumn('members', 'vault_digest_post_ids')
            && Schema::hasColumn('members', 'vault_digest_synced_at');
    }
}
