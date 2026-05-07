<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GrimbaPostRecency
{
    public static function expression(string $table = 'posts'): string
    {
        $createdAt = $table . '.created_at';

        if (! Schema::hasColumn('posts', 'published_at')) {
            return $createdAt;
        }

        return 'COALESCE(' . $table . '.published_at, ' . $createdAt . ')';
    }

    public static function orderByPublished(mixed $query, string $table = 'posts', bool $stable = true): mixed
    {
        $query->orderByRaw(self::expression($table) . ' DESC');

        if ($stable) {
            $query->orderByDesc($table . '.id');
        }

        return $query;
    }

    public static function wherePublishedSince(mixed $query, mixed $since, string $table = 'posts', bool $inclusive = true): mixed
    {
        $operator = $inclusive ? '>=' : '>';

        return $query->whereRaw(
            self::expression($table) . ' ' . $operator . ' ?',
            [self::dateTimeString($since)]
        );
    }

    public static function wherePublishedDateFrom(mixed $query, string $date, string $table = 'posts'): mixed
    {
        return $query->whereRaw('DATE(' . self::expression($table) . ') >= ?', [$date]);
    }

    public static function wherePublishedDateTo(mixed $query, string $date, string $table = 'posts'): mixed
    {
        return $query->whereRaw('DATE(' . self::expression($table) . ') <= ?', [$date]);
    }

    public static function value(object $post): ?CarbonImmutable
    {
        $raw = $post->published_at ?? null;
        if ($raw === null || $raw === '') {
            $raw = $post->created_at ?? null;
        }

        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            if ($raw instanceof CarbonInterface) {
                return CarbonImmutable::instance($raw->toDateTime());
            }

            return CarbonImmutable::parse($raw);
        } catch (Throwable) {
            return null;
        }
    }

    private static function dateTimeString(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toDateTimeString();
        }

        return (string) $value;
    }
}
