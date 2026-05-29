<?php

namespace App\Support;

/**
 * S-MAP-03 (Vader 2026-05-29) — ISO-2 country → continent lookup.
 *
 * Drives the /breaking-map full-viewport view: every news_sources.country
 * code maps to one of 5 continents (africa, americas, asia, europe,
 * oceania) so the per-continent ticker can group breaking posts by
 * geography. Sources with NULL or non-ISO codes fall into the synthetic
 * 'global' bucket where multi-region / UN / climate stories live.
 *
 * Reference: ISO 3166-1 alpha-2. The continent assignment is the
 * conventional 6-continent model collapsed to 5 (Americas = North + South).
 * Antarctica omitted (no news sources).
 */
class Continents
{
    public const AFRICA = 'africa';
    public const AMERICAS = 'americas';
    public const ASIA = 'asia';
    public const EUROPE = 'europe';
    public const OCEANIA = 'oceania';
    public const GLOBAL = 'global';

    /** @var array<string, string> */
    private const ISO2_TO_CONTINENT = [
        // Africa
        'DZ' => self::AFRICA, 'AO' => self::AFRICA, 'BJ' => self::AFRICA, 'BW' => self::AFRICA,
        'BF' => self::AFRICA, 'BI' => self::AFRICA, 'CM' => self::AFRICA, 'CV' => self::AFRICA,
        'CF' => self::AFRICA, 'TD' => self::AFRICA, 'KM' => self::AFRICA, 'CG' => self::AFRICA,
        'CD' => self::AFRICA, 'CI' => self::AFRICA, 'DJ' => self::AFRICA, 'EG' => self::AFRICA,
        'GQ' => self::AFRICA, 'ER' => self::AFRICA, 'SZ' => self::AFRICA, 'ET' => self::AFRICA,
        'GA' => self::AFRICA, 'GM' => self::AFRICA, 'GH' => self::AFRICA, 'GN' => self::AFRICA,
        'GW' => self::AFRICA, 'KE' => self::AFRICA, 'LS' => self::AFRICA, 'LR' => self::AFRICA,
        'LY' => self::AFRICA, 'MG' => self::AFRICA, 'MW' => self::AFRICA, 'ML' => self::AFRICA,
        'MR' => self::AFRICA, 'MU' => self::AFRICA, 'MA' => self::AFRICA, 'MZ' => self::AFRICA,
        'NA' => self::AFRICA, 'NE' => self::AFRICA, 'NG' => self::AFRICA, 'RW' => self::AFRICA,
        'ST' => self::AFRICA, 'SN' => self::AFRICA, 'SC' => self::AFRICA, 'SL' => self::AFRICA,
        'SO' => self::AFRICA, 'ZA' => self::AFRICA, 'SS' => self::AFRICA, 'SD' => self::AFRICA,
        'TZ' => self::AFRICA, 'TG' => self::AFRICA, 'TN' => self::AFRICA, 'UG' => self::AFRICA,
        'ZM' => self::AFRICA, 'ZW' => self::AFRICA,

        // Americas (North + Central + South + Caribbean)
        'AG' => self::AMERICAS, 'AR' => self::AMERICAS, 'BS' => self::AMERICAS, 'BB' => self::AMERICAS,
        'BZ' => self::AMERICAS, 'BO' => self::AMERICAS, 'BR' => self::AMERICAS, 'CA' => self::AMERICAS,
        'CL' => self::AMERICAS, 'CO' => self::AMERICAS, 'CR' => self::AMERICAS, 'CU' => self::AMERICAS,
        'DM' => self::AMERICAS, 'DO' => self::AMERICAS, 'EC' => self::AMERICAS, 'SV' => self::AMERICAS,
        'GD' => self::AMERICAS, 'GT' => self::AMERICAS, 'GY' => self::AMERICAS, 'HT' => self::AMERICAS,
        'HN' => self::AMERICAS, 'JM' => self::AMERICAS, 'MX' => self::AMERICAS, 'NI' => self::AMERICAS,
        'PA' => self::AMERICAS, 'PY' => self::AMERICAS, 'PE' => self::AMERICAS, 'PR' => self::AMERICAS,
        'KN' => self::AMERICAS, 'LC' => self::AMERICAS, 'VC' => self::AMERICAS, 'SR' => self::AMERICAS,
        'TT' => self::AMERICAS, 'US' => self::AMERICAS, 'UY' => self::AMERICAS, 'VE' => self::AMERICAS,

        // Asia
        'AF' => self::ASIA, 'AM' => self::ASIA, 'AZ' => self::ASIA, 'BH' => self::ASIA,
        'BD' => self::ASIA, 'BT' => self::ASIA, 'BN' => self::ASIA, 'KH' => self::ASIA,
        'CN' => self::ASIA, 'CY' => self::ASIA, 'GE' => self::ASIA, 'IN' => self::ASIA,
        'ID' => self::ASIA, 'IR' => self::ASIA, 'IQ' => self::ASIA, 'IL' => self::ASIA,
        'JP' => self::ASIA, 'JO' => self::ASIA, 'KZ' => self::ASIA, 'KW' => self::ASIA,
        'KG' => self::ASIA, 'LA' => self::ASIA, 'LB' => self::ASIA, 'MY' => self::ASIA,
        'MV' => self::ASIA, 'MN' => self::ASIA, 'MM' => self::ASIA, 'NP' => self::ASIA,
        'KP' => self::ASIA, 'OM' => self::ASIA, 'PK' => self::ASIA, 'PS' => self::ASIA,
        'PH' => self::ASIA, 'QA' => self::ASIA, 'SA' => self::ASIA, 'SG' => self::ASIA,
        'KR' => self::ASIA, 'LK' => self::ASIA, 'SY' => self::ASIA, 'TW' => self::ASIA,
        'TJ' => self::ASIA, 'TH' => self::ASIA, 'TL' => self::ASIA, 'TR' => self::ASIA,
        'TM' => self::ASIA, 'AE' => self::ASIA, 'UZ' => self::ASIA, 'VN' => self::ASIA,
        'YE' => self::ASIA, 'HK' => self::ASIA, 'MO' => self::ASIA,

        // Europe
        'AL' => self::EUROPE, 'AD' => self::EUROPE, 'AT' => self::EUROPE, 'BY' => self::EUROPE,
        'BE' => self::EUROPE, 'BA' => self::EUROPE, 'BG' => self::EUROPE, 'HR' => self::EUROPE,
        'CZ' => self::EUROPE, 'DK' => self::EUROPE, 'EE' => self::EUROPE, 'FI' => self::EUROPE,
        'FR' => self::EUROPE, 'DE' => self::EUROPE, 'GR' => self::EUROPE, 'HU' => self::EUROPE,
        'IS' => self::EUROPE, 'IE' => self::EUROPE, 'IT' => self::EUROPE, 'LV' => self::EUROPE,
        'LI' => self::EUROPE, 'LT' => self::EUROPE, 'LU' => self::EUROPE, 'MT' => self::EUROPE,
        'MD' => self::EUROPE, 'MC' => self::EUROPE, 'ME' => self::EUROPE, 'NL' => self::EUROPE,
        'MK' => self::EUROPE, 'NO' => self::EUROPE, 'PL' => self::EUROPE, 'PT' => self::EUROPE,
        'RO' => self::EUROPE, 'RU' => self::EUROPE, 'SM' => self::EUROPE, 'RS' => self::EUROPE,
        'SK' => self::EUROPE, 'SI' => self::EUROPE, 'ES' => self::EUROPE, 'SE' => self::EUROPE,
        'CH' => self::EUROPE, 'UA' => self::EUROPE, 'GB' => self::EUROPE, 'VA' => self::EUROPE,
        'XK' => self::EUROPE,

        // Oceania
        'AU' => self::OCEANIA, 'FJ' => self::OCEANIA, 'KI' => self::OCEANIA, 'MH' => self::OCEANIA,
        'FM' => self::OCEANIA, 'NR' => self::OCEANIA, 'NZ' => self::OCEANIA, 'PW' => self::OCEANIA,
        'PG' => self::OCEANIA, 'WS' => self::OCEANIA, 'SB' => self::OCEANIA, 'TO' => self::OCEANIA,
        'TV' => self::OCEANIA, 'VU' => self::OCEANIA,
    ];

    /**
     * Return the continent key for a country ISO-2 code, or 'global'
     * if the input is null, empty, or unrecognized. Case-insensitive
     * (lowercases the input before lookup).
     */
    public static function forCountry(?string $iso2): string
    {
        if ($iso2 === null || $iso2 === '') {
            return self::GLOBAL;
        }
        $normalized = strtoupper(trim($iso2));
        return self::ISO2_TO_CONTINENT[$normalized] ?? self::GLOBAL;
    }

    /**
     * All 5 continent keys + global, in the canonical render order
     * (north-to-south, east-to-west for visual reading flow).
     *
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::EUROPE,
            self::AMERICAS,
            self::ASIA,
            self::AFRICA,
            self::OCEANIA,
            self::GLOBAL,
        ];
    }

    /**
     * Translated display label for a continent key. Locale-sensitive
     * via __(); call from a request context.
     */
    public static function label(string $continent): string
    {
        return match ($continent) {
            self::AFRICA => __('Afrique'),
            self::AMERICAS => __('Amériques'),
            self::ASIA => __('Asie'),
            self::EUROPE => __('Europe'),
            self::OCEANIA => __('Océanie'),
            self::GLOBAL => __('Global'),
            default => $continent,
        };
    }

    /**
     * Ticker scroll direction per continent. Alternating pattern so
     * the eye is drawn across the whole map, not stuck in one corner.
     * Returns 'ltr' or 'rtl'.
     */
    public static function scrollDirection(string $continent): string
    {
        return match ($continent) {
            self::AFRICA => 'ltr',
            self::AMERICAS => 'rtl',
            self::ASIA => 'ltr',
            self::EUROPE => 'rtl',
            self::OCEANIA => 'ltr',
            self::GLOBAL => 'rtl',
            default => 'ltr',
        };
    }
}
