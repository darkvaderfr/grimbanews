<?php

namespace App\Ground;

/**
 * S400 (Fleet K1) — single source of truth for the editorial region split.
 *
 * Vader directive 2026-05-05: simplify the cuts to four regions.
 *   Africa     — 54 ISO-2 country codes
 *   Europe     — ~48 ISO-2 country codes
 *   Americas   — ~35 ISO-2 country codes (North + Central + South + Caribbean)
 *   International — NEGATIVE filter: posts whose source is NOT in any of
 *                   the three named regions (Asia, Oceania, Middle East,
 *                   Pacific, Antarctica, plus sources with no country).
 *
 * Helpers:
 *   - Regions::countries(string $key): ?array — returns the ISO-2 list,
 *     null for `international` (which uses a different filter shape).
 *   - Regions::valid(string $key): bool — quick membership test.
 *   - Regions::label(string $key): string — French label for UI.
 *   - Regions::all(): array — keyed list for picker / dropdown rendering.
 *   - Regions::otherNamedCodes(): array — union of Africa+Europe+Americas
 *     codes, used by the GrimbaRegionScope to build the International
 *     negative filter.
 *
 * The lists are intentionally hard-coded: they don't change weekly and
 * we want them readable in the diff. UN M.49 + ISO 3166-1 used as base.
 */
class Regions
{
    public const AFRICA = [
        'DZ', 'AO', 'BJ', 'BW', 'BF', 'BI', 'CV', 'CM', 'CF', 'TD',
        'KM', 'CG', 'CD', 'DJ', 'EG', 'GQ', 'ER', 'SZ', 'ET', 'GA',
        'GM', 'GH', 'GN', 'GW', 'CI', 'KE', 'LS', 'LR', 'LY', 'MG',
        'MW', 'ML', 'MR', 'MU', 'MA', 'MZ', 'NA', 'NE', 'NG', 'RW',
        'ST', 'SN', 'SC', 'SL', 'SO', 'ZA', 'SS', 'SD', 'TZ', 'TG',
        'TN', 'UG', 'ZM', 'ZW',
    ];

    public const EUROPE = [
        'AL', 'AD', 'AT', 'BY', 'BE', 'BA', 'BG', 'HR', 'CY', 'CZ',
        'DK', 'EE', 'FO', 'FI', 'FR', 'DE', 'GI', 'GR', 'HU', 'IS',
        'IE', 'IM', 'IT', 'XK', 'LV', 'LI', 'LT', 'LU', 'MT', 'MD',
        'MC', 'ME', 'NL', 'MK', 'NO', 'PL', 'PT', 'RO', 'RU', 'SM',
        'RS', 'SK', 'SI', 'ES', 'SE', 'CH', 'UA', 'GB', 'VA',
    ];

    public const AMERICAS = [
        // North America
        'CA', 'US', 'MX',
        // Central America
        'BZ', 'CR', 'SV', 'GT', 'HN', 'NI', 'PA',
        // Caribbean
        'AG', 'BS', 'BB', 'CU', 'DM', 'DO', 'GD', 'HT', 'JM',
        'KN', 'LC', 'VC', 'TT',
        // South America
        'AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'GY', 'PY',
        'PE', 'SR', 'UY', 'VE',
    ];

    private const VALID = ['africa', 'europe', 'americas', 'international'];

    /**
     * Returns the ISO-2 list for a named region, or null when the region
     * is "international" (which uses a negative filter via
     * otherNamedCodes()).
     *
     * @return array<int, string>|null
     */
    public static function countries(string $key): ?array
    {
        return match ($key) {
            'africa'   => self::AFRICA,
            'europe'   => self::EUROPE,
            'americas' => self::AMERICAS,
            'international' => null,
            default    => null,
        };
    }

    public static function valid(string $key): bool
    {
        return in_array($key, self::VALID, true);
    }

    public static function label(string $key): string
    {
        // Wave TTTTTTTTTT (Vader 2026-05-23) — wrap in __() so EN
        // readers see "Africa / Americas" instead of FR labels.
        // Existing translations: lang/en.json has Amériques→Americas,
        // Afrique→Africa.
        return match ($key) {
            'africa'        => __('Afrique'),
            'europe'        => __('Europe'),
            'americas'      => __('Amériques'),
            'international' => __('International'),
            default         => '—',
        };
    }

    /**
     * Keyed list for picker / dropdown rendering. Values are the
     * canonical labels.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'africa'        => self::label('africa'),
            'europe'        => self::label('europe'),
            'americas'      => self::label('americas'),
            'international' => self::label('international'),
        ];
    }

    /**
     * Union of every ISO-2 code that belongs to a named region. Used by
     * the GrimbaRegionScope to build the International negative filter:
     *   posts.source_id IN (sources where country NOT IN <this union>
     *                       OR country IS NULL)
     *
     * @return array<int, string>
     */
    public static function otherNamedCodes(): array
    {
        return array_values(array_unique(array_merge(self::AFRICA, self::EUROPE, self::AMERICAS)));
    }

    /**
     * Classify a source country code (ISO-2) into one of the four
     * canonical regions. Used at ingest time to tag posts with their
     * editorial region instead of deriving it at every render.
     *
     *   null / unknown  → 'international'
     *   AF country      → 'africa'
     *   EU country      → 'europe'
     *   AM country      → 'americas'
     *   anything else   → 'international' (Asia, Oceania, ME, etc.)
     */
    public static function regionForCountry(?string $code): string
    {
        if ($code === null) {
            return 'international';
        }

        $up = strtoupper(trim($code));
        if ($up === '') {
            return 'international';
        }

        if (in_array($up, self::AFRICA, true)) {
            return 'africa';
        }
        if (in_array($up, self::EUROPE, true)) {
            return 'europe';
        }
        if (in_array($up, self::AMERICAS, true)) {
            return 'americas';
        }

        return 'international';
    }

    /**
     * Map legacy / synonymous cookie values to the canonical 4 regions.
     * Returns the canonical key, or 'international' as the safe fallback.
     */
    public static function migrate(string $raw): string
    {
        $r = strtolower(trim($raw));
        $map = [
            // legacy 6-region picker
            'monde'    => 'international',
            'afrique'  => 'africa',
            'europe'   => 'europe',
            'amerique' => 'americas',
            'amériques' => 'americas',
            'ameriques' => 'americas',
            'france'   => 'europe',
            'uk'       => 'europe',
            'gb'       => 'europe',
            'us'       => 'americas',
            'usa'      => 'americas',
            'canada'   => 'americas',
            // current values pass through
            'africa'        => 'africa',
            'americas'      => 'americas',
            'international' => 'international',
        ];

        return $map[$r] ?? 'international';
    }
}
