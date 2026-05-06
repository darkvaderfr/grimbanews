<?php

namespace App\Support;

use Botble\Blog\Models\Post;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GrimbaSavedSearches
{
    public const MAX_PER_MEMBER = 12;
    public const DIGEST_POST_LIMIT = 8;

    private const BIASES = ['left', 'center', 'right', 'unknown'];

    /**
     * @param array<string, mixed>|object $input
     * @return array{search_query: string, source_id: ?int, bias: ?string, owner: ?string, from_date: ?string, to_date: ?string}
     */
    public static function normalize(array|object $input): array
    {
        $query = self::cleanText((string) self::read($input, 'search_query', self::read($input, 'q', '')), 180);
        $sourceId = (int) self::read($input, 'source_id', self::read($input, 'source', 0));
        $bias = (string) self::read($input, 'bias', '');
        $owner = self::cleanText((string) self::read($input, 'owner', ''), 180);
        $fromDate = self::validDate((string) self::read($input, 'from_date', ''));
        $toDate = self::validDate((string) self::read($input, 'to_date', ''));

        return [
            'search_query' => $query,
            'source_id' => $sourceId > 0 ? $sourceId : null,
            'bias' => in_array($bias, self::BIASES, true) ? $bias : null,
            'owner' => $owner !== '' ? $owner : null,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    }

    public static function ready(): bool
    {
        return Schema::hasTable('saved_searches')
            && Schema::hasTable('members')
            && Schema::hasColumn('saved_searches', 'search_query')
            && Schema::hasColumn('saved_searches', 'search_hash');
    }

    public static function hash(array|object $input): string
    {
        return hash('sha256', json_encode(self::normalize($input), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public static function upsertForMember(?object $member, array|object $input): ?object
    {
        if (! self::ready()) {
            return null;
        }

        $memberId = (int) data_get($member, 'id', 0);
        $criteria = self::normalize($input);

        if ($memberId <= 0 || $criteria['search_query'] === '') {
            return null;
        }

        $hash = self::hash($criteria);
        $existing = DB::table('saved_searches')
            ->where('member_id', $memberId)
            ->where('search_hash', $hash)
            ->first(['id']);

        if (! $existing && self::countForMember($member) >= self::MAX_PER_MEMBER) {
            return null;
        }

        $payload = [
            'search_query' => $criteria['search_query'],
            'source_id' => $criteria['source_id'],
            'bias' => $criteria['bias'],
            'owner' => $criteria['owner'],
            'from_date' => $criteria['from_date'],
            'to_date' => $criteria['to_date'],
            'search_hash' => $hash,
            'active' => true,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('saved_searches')
                ->where('id', (int) $existing->id)
                ->update($payload);

            return DB::table('saved_searches')->where('id', (int) $existing->id)->first();
        }

        $id = DB::table('saved_searches')->insertGetId($payload + [
            'member_id' => $memberId,
            'created_at' => now(),
        ]);

        return DB::table('saved_searches')->where('id', (int) $id)->first();
    }

    public static function deleteForMember(?object $member, int $searchId): bool
    {
        if (! self::ready()) {
            return false;
        }

        $memberId = (int) data_get($member, 'id', 0);
        if ($memberId <= 0 || $searchId <= 0) {
            return false;
        }

        return (bool) DB::table('saved_searches')
            ->where('member_id', $memberId)
            ->where('id', $searchId)
            ->delete();
    }

    public static function countForMember(?object $member): int
    {
        if (! self::ready()) {
            return 0;
        }

        $memberId = (int) data_get($member, 'id', 0);
        if ($memberId <= 0) {
            return 0;
        }

        return (int) DB::table('saved_searches')
            ->where('member_id', $memberId)
            ->where('active', true)
            ->count();
    }

    public static function existsForMember(?object $member, array|object $input): bool
    {
        if (! self::ready()) {
            return false;
        }

        $memberId = (int) data_get($member, 'id', 0);
        $criteria = self::normalize($input);

        if ($memberId <= 0 || $criteria['search_query'] === '') {
            return false;
        }

        return DB::table('saved_searches')
            ->where('member_id', $memberId)
            ->where('search_hash', self::hash($criteria))
            ->where('active', true)
            ->exists();
    }

    public static function forMember(?object $member, int $limit = 20): Collection
    {
        if (! self::ready()) {
            return collect();
        }

        $memberId = (int) data_get($member, 'id', 0);
        if ($memberId <= 0) {
            return collect();
        }

        return DB::table('saved_searches')
            ->where('member_id', $memberId)
            ->where('active', true)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    public static function matchingPosts(array|object $search, ?CarbonInterface $since = null, int $limit = self::DIGEST_POST_LIMIT): Collection
    {
        $criteria = self::normalize($search);

        if ($criteria['search_query'] === '') {
            return collect();
        }

        $query = Post::withoutGlobalScope('grimba_region')
            ->where('posts.status', 'published');

        self::applyCriteria($query, $criteria);

        if ($since) {
            $query->where('posts.created_at', '>', $since->toDateTimeString());
        }

        return $query
            ->with('categories')
            ->orderByDesc('posts.created_at')
            ->limit($limit)
            ->get();
    }

    public static function label(array|object $input): string
    {
        $criteria = self::normalize($input);
        $parts = ['"' . $criteria['search_query'] . '"'];

        if ($criteria['source_id']) {
            $parts[] = (string) (DB::table('news_sources')->where('id', $criteria['source_id'])->value('name') ?: ('Source #' . $criteria['source_id']));
        }
        if ($criteria['bias']) {
            $parts[] = self::biasLabel($criteria['bias']);
        }
        if ($criteria['owner']) {
            $parts[] = (string) $criteria['owner'];
        }
        if ($criteria['from_date']) {
            $parts[] = 'depuis ' . $criteria['from_date'];
        }
        if ($criteria['to_date']) {
            $parts[] = "jusqu'au " . $criteria['to_date'];
        }

        return implode(' · ', $parts);
    }

    public static function searchUrl(array|object $input): string
    {
        $params = self::queryParams($input);
        $query = http_build_query($params);

        return url('/search' . ($query ? ('?' . $query) : ''));
    }

    /**
     * @return array<string, string|int>
     */
    public static function queryParams(array|object $input): array
    {
        $criteria = self::normalize($input);
        $params = ['q' => $criteria['search_query']];

        foreach (['source_id' => 'source', 'bias' => 'bias', 'owner' => 'owner', 'from_date' => 'from_date', 'to_date' => 'to_date'] as $from => $to) {
            if ($criteria[$from] !== null && $criteria[$from] !== '') {
                $params[$to] = $criteria[$from];
            }
        }

        return $params;
    }

    /**
     * @param array{search_query: string, source_id: ?int, bias: ?string, owner: ?string, from_date: ?string, to_date: ?string} $criteria
     */
    public static function applyCriteria(Builder $query, array $criteria): void
    {
        $terms = collect(preg_split('/\s+/u', $criteria['search_query']))
            ->filter()
            ->take(8)
            ->values();

        foreach ($terms as $term) {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $term) . '%';

            $query->where(function (Builder $where) use ($like): void {
                $where->where('posts.name', 'like', $like)
                    ->orWhere('posts.description', 'like', $like)
                    ->orWhere('posts.content', 'like', $like)
                    ->orWhere('posts.source_name', 'like', $like);
            });
        }

        if ($criteria['source_id']) {
            $query->where('posts.source_id', $criteria['source_id']);
        }
        if ($criteria['bias']) {
            $query->where('posts.bias_rating', $criteria['bias']);
        }
        if ($criteria['owner']) {
            $owner = $criteria['owner'];
            $query->whereIn('posts.source_id', function ($sub) use ($owner): void {
                $sub->select('id')
                    ->from('news_sources')
                    ->where('owner_name', $owner);
            });
        }
        if ($criteria['from_date']) {
            $query->whereDate('posts.created_at', '>=', $criteria['from_date']);
        }
        if ($criteria['to_date']) {
            $query->whereDate('posts.created_at', '<=', $criteria['to_date']);
        }
    }

    private static function biasLabel(string $bias): string
    {
        return match ($bias) {
            'left' => 'Gauche',
            'center' => 'Centre',
            'right' => 'Droite',
            default => 'Non classe',
        };
    }

    private static function cleanText(string $value, int $limit): string
    {
        return Str::of($value)
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->limit($limit, '')
            ->toString();
    }

    private static function validDate(string $value): ?string
    {
        $value = trim($value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    private static function read(array|object $source, string $key, mixed $default = null): mixed
    {
        if (is_array($source)) {
            return $source[$key] ?? $default;
        }

        return data_get($source, $key, $default);
    }
}
