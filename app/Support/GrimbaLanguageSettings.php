<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * S-LSAT-03 (Vader 2026-05-18) — cached reader for the 13 grimba_lang_*
 * settings keys that drive the strict-locale surfacing + rule engine.
 *
 * - One `Cache::remember()` per request bucket (5-minute TTL) so
 *   reader hot paths (`/`, `/breaking`, `/latest`) and the rule-engine
 *   cron both consult a single in-memory map instead of N raw
 *   `setting()` calls.
 * - Sane defaults baked in — operator can leave the keys unset and
 *   the system still works with Vader's intended behavior.
 * - Type coercion lives here, NOT in callers. Booleans are 1/0/'true'/
 *   'false' tolerant; integers are clamped via `clampInt()`.
 * - Strict per-surface toggles allow ramping the filter feature on a
 *   page-by-page basis without one big bang.
 *
 * @see docs/GRIMBANEWS_LANGUAGE_SURFACING_AND_AUTO_TRANSLATE_PLAN.md
 */
class GrimbaLanguageSettings
{
    public const CACHE_KEY = 'grimba_lang_settings';
    public const CACHE_TTL_SECONDS = 300;

    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        self::$cache = Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, fn () => self::load());

        return self::$cache;
    }

    /**
     * Reset both the per-request memoization and the durable cache.
     * Called from the admin save handler so a settings change takes
     * effect immediately rather than waiting 5 minutes.
     */
    public static function flush(): void
    {
        self::$cache = null;
        Cache::forget(self::CACHE_KEY);
    }

    // ---------------------------------------------------------------
    // Master switches
    // ---------------------------------------------------------------

    public static function strictSurface(): bool
    {
        return (bool) (self::all()['strict_surface'] ?? true);
    }

    public static function ruleEngineEnabled(): bool
    {
        return (bool) (self::all()['rule_engine_enabled'] ?? true);
    }

    public static function tailExpanderEnabled(): bool
    {
        return (bool) (self::all()['tail_expander_enabled'] ?? true);
    }

    // ---------------------------------------------------------------
    // Per-surface strict toggles. Each respects the master `strict_surface`
    // switch — if the master is OFF, every per-surface read returns false.
    // ---------------------------------------------------------------

    public static function strictForHome(): bool { return self::strictSurface() && (bool) (self::all()['strict_home'] ?? true); }
    public static function strictForBreaking(): bool { return self::strictSurface() && (bool) (self::all()['strict_breaking'] ?? true); }
    public static function strictForLatest(): bool { return self::strictSurface() && (bool) (self::all()['strict_latest'] ?? true); }
    public static function strictForDossiers(): bool { return self::strictSurface() && (bool) (self::all()['strict_dossiers'] ?? true); }
    public static function strictForCategory(): bool { return self::strictSurface() && (bool) (self::all()['strict_category'] ?? false); }
    public static function strictForSearch(): bool { return self::strictSurface() && (bool) (self::all()['strict_search'] ?? false); }

    /**
     * Generic per-surface lookup so callers can pass a surface key like
     * 'home', 'breaking', 'latest', 'dossiers', 'category', 'search'.
     */
    public static function strictFor(string $surface): bool
    {
        return match ($surface) {
            'home' => self::strictForHome(),
            'breaking' => self::strictForBreaking(),
            'latest' => self::strictForLatest(),
            'dossiers', 'comparatif' => self::strictForDossiers(),
            'category', 'blog' => self::strictForCategory(),
            'search' => self::strictForSearch(),
            default => self::strictSurface(),
        };
    }

    // ---------------------------------------------------------------
    // Rule engine thresholds
    // ---------------------------------------------------------------

    public static function popularityThreshold(): int
    {
        return self::clampInt(self::all()['popularity_threshold'] ?? 500, 10, 100000);
    }

    public static function popularityThresholdAfrica(): int
    {
        return self::clampInt(self::all()['popularity_threshold_africa'] ?? 100, 10, 100000);
    }

    /**
     * Effective threshold for a given post — applies the lower
     * African threshold when the post's editorial region is in the
     * force-both list.
     */
    public static function effectivePopularityThreshold(?string $editorialRegion): int
    {
        $region = strtolower((string) $editorialRegion);
        $forced = self::forceBothRegions();
        if ($region !== '' && in_array($region, $forced, true)) {
            return self::popularityThresholdAfrica();
        }
        return self::popularityThreshold();
    }

    /**
     * @return array<int, string>
     */
    public static function forceBothRegions(): array
    {
        $raw = (string) (self::all()['region_force_both'] ?? 'africa');
        // Zen audit fix 2026-05-18 — operators can disable the
        // forced-region rule by setting the value to the literal
        // string `none` (admin form helper text documents this).
        // An empty string still falls back to default via coerce(),
        // so accidental wipes don't disable the rule silently.
        if (strtolower(trim($raw)) === 'none') {
            return [];
        }
        return array_values(array_filter(array_map('trim', array_map('strtolower', explode(',', $raw)))));
    }

    public static function ruleEngineDailyCap(): int
    {
        return self::clampInt(self::all()['rule_engine_daily_cap'] ?? 500, 1, 100000);
    }

    // ---------------------------------------------------------------
    // Defaults + loader
    // ---------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private static function load(): array
    {
        $defaults = self::defaults();
        $out = [];
        foreach ($defaults as $key => $default) {
            $raw = function_exists('setting') ? setting('grimba_lang_' . $key, $default) : $default;
            $out[$key] = self::coerce($key, $raw, $default);
        }
        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'strict_surface'             => true,
            'strict_home'                => true,
            'strict_breaking'            => true,
            'strict_latest'              => true,
            'strict_dossiers'            => true,
            'strict_category'            => false,
            'strict_search'              => false,
            'popularity_threshold'       => 500,
            'popularity_threshold_africa'=> 100,
            'region_force_both'          => 'africa',
            'rule_engine_daily_cap'      => 500,
            'rule_engine_enabled'        => true,
            'tail_expander_enabled'      => true,
        ];
    }

    private static function coerce(string $key, mixed $raw, mixed $default): mixed
    {
        if (is_int($default)) {
            // Empty string from an unset setting falls back to default;
            // numeric-coercion `(int) ''` is 0 which is hostile to clamps.
            if ($raw === '' || $raw === null) return $default;
            return (int) $raw;
        }
        if (is_bool($default)) {
            if ($raw === '' || $raw === null) return $default;
            if (is_bool($raw)) return $raw;
            $str = strtolower((string) $raw);
            return in_array($str, ['1', 'true', 'yes', 'on'], true);
        }
        // strings (region_force_both) — empty string also falls back to
        // default so an accidental admin-form wipe doesn't disable
        // the auto-translate behavior.
        $value = (string) ($raw ?? '');
        if (trim($value) === '') return $default;
        return $value;
    }

    private static function clampInt(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
