<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class GrimbaRssFeedHealth
{
    public const STALE_HOURS = 24;
    public const VERY_STALE_HOURS = 72;
    public const SICK_FAILURES = 5;

    public static function score(object|array $feed): int
    {
        if (! self::bool($feed, 'is_active')) {
            return 0;
        }

        $score = 100;
        $failures = max(0, self::int($feed, 'consecutive_failures'));
        $lastPoll = self::date($feed, 'last_polled_at');
        $lastSuccess = self::date($feed, 'last_success_at');
        $itemsIngested = self::int($feed, 'items_ingested');

        $score -= min(70, $failures * 14);

        if ($lastSuccess === null) {
            $score -= 35;
        } else {
            $ageHours = $lastSuccess->diffInHours(Carbon::now());
            if ($ageHours >= self::VERY_STALE_HOURS) {
                $score -= 30;
            } elseif ($ageHours >= self::STALE_HOURS) {
                $score -= 18;
            }
        }

        if ($lastPoll === null) {
            $score -= 12;
        } elseif ($lastPoll->diffInHours(Carbon::now()) >= 12) {
            $score -= 8;
        }

        if ($itemsIngested === 0 && $lastSuccess !== null) {
            $score -= 8;
        }

        return max(0, min(100, $score));
    }

    public static function label(object|array $feed): string
    {
        if (! self::bool($feed, 'is_active')) {
            return 'Inactif';
        }

        $score = self::score($feed);

        return match (true) {
            $score >= 85 => 'Sain',
            $score >= 65 => 'A surveiller',
            $score >= 40 => 'Sans succes',
            default => 'Malade',
        };
    }

    public static function color(object|array $feed): string
    {
        if (! self::bool($feed, 'is_active')) {
            return '#9ca3af';
        }

        $score = self::score($feed);

        return match (true) {
            $score >= 85 => '#10b981',
            $score >= 65 => '#eab308',
            $score >= 40 => '#f97316',
            default => '#e84c3d',
        };
    }

    public static function isStale(object|array $feed, int $hours = self::STALE_HOURS): bool
    {
        if (! self::bool($feed, 'is_active')) {
            return false;
        }

        $lastSuccess = self::date($feed, 'last_success_at');

        return $lastSuccess === null || $lastSuccess->lte(Carbon::now()->subHours($hours));
    }

    public static function isSick(object|array $feed): bool
    {
        return self::bool($feed, 'is_active')
            && self::int($feed, 'consecutive_failures') >= self::SICK_FAILURES;
    }

    public static function staleReason(object|array $feed): string
    {
        $lastSuccess = self::date($feed, 'last_success_at');

        if ($lastSuccess === null) {
            return 'aucun succes';
        }

        return 'dernier succes ' . $lastSuccess->diffForHumans();
    }

    private static function value(object|array $feed, string $key): mixed
    {
        if (is_array($feed)) {
            return $feed[$key] ?? null;
        }

        return $feed->{$key} ?? null;
    }

    private static function bool(object|array $feed, string $key): bool
    {
        return (bool) self::value($feed, $key);
    }

    private static function int(object|array $feed, string $key): int
    {
        return (int) (self::value($feed, $key) ?? 0);
    }

    private static function date(object|array $feed, string $key): ?CarbonInterface
    {
        $value = self::value($feed, $key);

        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
