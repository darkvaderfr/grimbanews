<?php

namespace App\Support;

/**
 * S-MAP-V4-04 (Vader 2026-05-29) — ISO-2 country code -> [lat, lng] centroid.
 *
 * Pure static lookup that drops a /breaking-map pin at the centroid of each
 * news source's country. Built from Natural Earth label points (public
 * domain): the 1:110m boundaries vendored at public/vendor/natural-earth/
 * supply the centroid for every country drawn on the map, and the 1:50m
 * dataset fills in the small nations 110m omits (Vatican, Monaco, Singapore,
 * island states, ...). LABEL_X / LABEL_Y are Natural Earth's cartographer-
 * placed label anchors, so the pin lands where a human would label the
 * country -- not the raw geometric centroid (which can fall in the ocean for
 * crescent-shaped or multi-island countries).
 *
 * Coordinates are rounded to 3 decimals (~111 m) -- far finer than needed
 * for a source-country pin. Coverage is locked by tests to span every ISO-2
 * code App\Support\Continents recognises, so no continent-mapped source can
 * be silently dropped from the map.
 *
 * v4 pins at the *source's* country, not the story's location (story-level
 * geocoding is deferred to Phase 2). A US-based wire pins in Kansas, not at
 * the event.
 *
 * Trailing comment on each row = country name + the Natural Earth scale the
 * centroid came from.
 */
class CountryCentroids
{
    /** @var array<string, array{0: float, 1: float}> ISO-2 (uppercase) => [lat, lng] */
    private const CENTROIDS = [
        'AD' => [42.548, 1.539], // Andorra (50m)
        'AE' => [23.466, 54.547], // United Arab Emirates (110m)
        'AF' => [34.164, 66.497], // Afghanistan (110m)
        'AG' => [17.352, -61.791], // Antigua and Barbuda (50m)
        'AI' => [18.243, -63.026], // Anguilla (50m)
        'AL' => [40.655, 20.114], // Albania (110m)
        'AM' => [40.459, 44.801], // Armenia (110m)
        'AO' => [-12.183, 17.984], // Angola (110m)
        'AQ' => [-79.843, 35.885], // Antarctica (110m)
        'AR' => [-33.501, -64.173], // Argentina (110m)
        'AS' => [-14.327, -170.747], // American Samoa (50m)
        'AT' => [47.519, 14.131], // Austria (110m)
        'AU' => [-24.13, 134.05], // Australia (110m)
        'AW' => [12.517, -69.973], // Aruba (50m)
        'AX' => [60.156, 19.87], // Åland (50m)
        'AZ' => [40.402, 47.211], // Azerbaijan (110m)
        'BA' => [44.091, 18.068], // Bosnia and Herzegovina (110m)
        'BB' => [13.164, -59.569], // Barbados (50m)
        'BD' => [24.215, 89.685], // Bangladesh (110m)
        'BE' => [50.785, 4.8], // Belgium (110m)
        'BF' => [12.673, -1.364], // Burkina Faso (110m)
        'BG' => [42.509, 25.157], // Bulgaria (110m)
        'BH' => [26.056, 50.555], // Bahrain (50m)
        'BI' => [-3.333, 29.917], // Burundi (110m)
        'BJ' => [10.325, 2.352], // Benin (110m)
        'BL' => [17.902, -62.833], // Saint Barthélemy (50m)
        'BM' => [32.297, -64.764], // Bermuda (50m)
        'BN' => [4.448, 114.552], // Brunei (110m)
        'BO' => [-16.666, -64.593], // Bolivia (110m)
        'BR' => [-12.099, -49.559], // Brazil (110m)
        'BS' => [26.402, -77.147], // The Bahamas (110m)
        'BT' => [27.537, 90.04], // Bhutan (110m)
        'BW' => [-22.103, 24.179], // Botswana (110m)
        'BY' => [53.822, 28.418], // Belarus (110m)
        'BZ' => [17.202, -88.713], // Belize (110m)
        'CA' => [60.324, -101.911], // Canada (110m)
        'CD' => [-1.858, 23.459], // Democratic Republic of the Congo (110m)
        'CF' => [6.99, 20.907], // Central African Republic (110m)
        'CG' => [0.142, 15.901], // Republic of the Congo (110m)
        'CH' => [46.719, 7.464], // Switzerland (110m)
        'CI' => [7.491, -5.569], // Ivory Coast (110m)
        'CK' => [-21.216, -159.786], // Cook Islands (50m)
        'CL' => [-38.152, -72.319], // Chile (110m)
        'CM' => [4.585, 12.473], // Cameroon (110m)
        'CN' => [32.498, 106.337], // People's Republic of China (110m)
        'CO' => [3.373, -73.174], // Colombia (110m)
        'CR' => [10.065, -84.078], // Costa Rica (110m)
        'CU' => [21.334, -77.976], // Cuba (110m)
        'CV' => [15.075, -23.639], // Cape Verde (50m)
        'CW' => [12.145, -68.921], // Curaçao (50m)
        'CY' => [34.913, 33.084], // Cyprus (110m)
        'CZ' => [49.882, 15.378], // Czech Republic (110m)
        'DE' => [50.962, 9.678], // Germany (110m)
        'DJ' => [11.976, 42.499], // Djibouti (110m)
        'DK' => [55.967, 9.018], // Denmark (110m)
        'DM' => [15.459, -61.345], // Dominica (50m)
        'DO' => [19.104, -70.654], // Dominican Republic (110m)
        'DZ' => [27.397, 2.808], // Algeria (110m)
        'EC' => [-1.259, -78.188], // Ecuador (110m)
        'EE' => [58.725, 25.867], // Estonia (110m)
        'EG' => [26.186, 29.446], // Egypt (110m)
        'EH' => [23.968, -12.63], // Western Sahara (110m)
        'ER' => [15.787, 38.286], // Eritrea (110m)
        'ES' => [40.091, -3.465], // Spain (110m)
        'ET' => [8.033, 39.089], // Ethiopia (110m)
        'FI' => [63.252, 27.276], // Finland (110m)
        'FJ' => [-17.826, 177.975], // Fiji (110m)
        'FK' => [-51.609, -58.739], // Falkland Islands (110m)
        'FM' => [6.888, 158.234], // Federated States of Micronesia (50m)
        'FO' => [62.186, -7.058], // Faroe Islands (50m)
        'FR' => [46.696, 2.552], // France (110m)
        'GA' => [-0.438, 11.836], // Gabon (110m)
        'GB' => [54.403, -2.116], // United Kingdom (110m)
        'GD' => [12.113, -61.68], // Grenada (50m)
        'GE' => [41.87, 43.736], // Georgia (110m)
        'GG' => [49.464, -2.562], // Guernsey (50m)
        'GH' => [7.718, -1.037], // Ghana (110m)
        'GL' => [74.319, -39.335], // Greenland (110m)
        'GM' => [13.642, -14.998], // The Gambia (110m)
        'GN' => [10.619, -10.016], // Guinea (110m)
        'GQ' => [2.333, 8.99], // Equatorial Guinea (110m)
        'GR' => [39.493, 21.726], // Greece (110m)
        'GS' => [-55.683, -31.063], // South Georgia and the South Sandwich Islands (50m)
        'GT' => [14.982, -90.497], // Guatemala (110m)
        'GU' => [13.354, 144.704], // Guam (50m)
        'GW' => [12.164, -14.524], // Guinea-Bissau (110m)
        'GY' => [5.124, -58.943], // Guyana (110m)
        'HK' => [22.449, 114.098], // Hong Kong (50m)
        'HM' => [-53.103, 73.505], // Heard Island and McDonald Islands (50m)
        'HN' => [14.795, -86.888], // Honduras (110m)
        'HR' => [45.806, 16.372], // Croatia (110m)
        'HT' => [19.264, -72.224], // Haiti (110m)
        'HU' => [47.087, 19.448], // Hungary (110m)
        'ID' => [-0.954, 101.893], // Indonesia (110m)
        'IE' => [53.079, -7.799], // Ireland (110m)
        'IL' => [30.911, 34.848], // Israel (110m)
        'IM' => [54.221, -4.53], // Isle of Man (50m)
        'IN' => [22.687, 79.358], // India (110m)
        'IO' => [-6.191, 71.348], // British Indian Ocean Territory (50m)
        'IQ' => [33.094, 43.262], // Iraq (110m)
        'IR' => [32.166, 54.931], // Iran (110m)
        'IS' => [64.779, -18.674], // Iceland (110m)
        'IT' => [44.732, 11.077], // Italy (110m)
        'JE' => [49.221, -2.09], // Jersey (50m)
        'JM' => [18.137, -77.319], // Jamaica (110m)
        'JO' => [30.805, 36.376], // Jordan (110m)
        'JP' => [36.143, 138.442], // Japan (110m)
        'KE' => [0.549, 37.908], // Kenya (110m)
        'KG' => [41.669, 74.533], // Kyrgyzstan (110m)
        'KH' => [12.648, 104.505], // Cambodia (110m)
        'KI' => [1.82, -157.385], // Kiribati (50m)
        'KM' => [-11.728, 43.318], // Comoros (50m)
        'KN' => [17.337, -62.758], // Saint Kitts and Nevis (50m)
        'KP' => [39.885, 126.445], // North Korea (110m)
        'KR' => [36.385, 128.13], // South Korea (110m)
        'KW' => [29.414, 47.314], // Kuwait (110m)
        'KY' => [19.32, -81.241], // Cayman Islands (50m)
        'KZ' => [49.054, 68.686], // Kazakhstan (110m)
        'LA' => [19.432, 102.534], // Laos (110m)
        'LB' => [34.133, 35.993], // Lebanon (110m)
        'LC' => [13.892, -60.98], // Saint Lucia (50m)
        'LI' => [47.111, 9.559], // Liechtenstein (50m)
        'LK' => [7.581, 80.705], // Sri Lanka (110m)
        'LR' => [6.447, -9.46], // Liberia (110m)
        'LS' => [-29.48, 28.247], // Lesotho (110m)
        'LT' => [55.104, 24.09], // Lithuania (110m)
        'LU' => [49.734, 6.078], // Luxembourg (110m)
        'LV' => [57.067, 25.459], // Latvia (110m)
        'LY' => [26.639, 18.011], // Libya (110m)
        'MA' => [31.651, -7.187], // Morocco (110m)
        'MC' => [43.74, 7.398], // Monaco (50m)
        'MD' => [47.435, 28.488], // Moldova (110m)
        'ME' => [42.803, 19.144], // Montenegro (110m)
        'MF' => [18.081, -63.049], // Saint Martin (50m)
        'MG' => [-18.628, 46.704], // Madagascar (110m)
        'MH' => [7.083, 171.194], // Marshall Islands (50m)
        'MK' => [41.558, 21.556], // North Macedonia (110m)
        'ML' => [18.693, -2.038], // Mali (110m)
        'MM' => [21.574, 95.804], // Myanmar (110m)
        'MN' => [45.997, 104.15], // Mongolia (110m)
        'MO' => [22.13, 113.556], // Macau (50m)
        'MP' => [15.188, 145.734], // Northern Mariana Islands (50m)
        'MR' => [19.587, -9.74], // Mauritania (110m)
        'MS' => [16.737, -62.188], // Montserrat (50m)
        'MT' => [35.893, 14.433], // Malta (50m)
        'MU' => [-20.3, 57.566], // Mauritius (50m)
        'MV' => [4.174, 73.508], // Maldives (50m)
        'MW' => [-13.387, 33.608], // Malawi (110m)
        'MX' => [23.92, -102.289], // Mexico (110m)
        'MY' => [2.529, 113.837], // Malaysia (110m)
        'MZ' => [-13.943, 37.838], // Mozambique (110m)
        'NA' => [-20.575, 17.108], // Namibia (110m)
        'NC' => [-21.065, 165.084], // New Caledonia (110m)
        'NE' => [17.446, 9.504], // Niger (110m)
        'NF' => [-29.033, 167.955], // Norfolk Island (50m)
        'NG' => [9.44, 7.503], // Nigeria (110m)
        'NI' => [12.671, -85.069], // Nicaragua (110m)
        'NL' => [52.422, 5.611], // Netherlands (110m)
        'NO' => [61.357, 9.68], // Norway (110m)
        'NP' => [28.298, 83.64], // Nepal (110m)
        'NR' => [-0.52, 166.933], // Nauru (50m)
        'NU' => [-19.046, -169.863], // Niue (50m)
        'NZ' => [-39.759, 172.787], // New Zealand (110m)
        'OM' => [22.12, 57.337], // Oman (110m)
        'PA' => [8.722, -80.352], // Panama (110m)
        'PE' => [-12.977, -72.9], // Peru (110m)
        'PF' => [-17.628, -149.462], // French Polynesia (50m)
        'PG' => [-5.695, 143.91], // Papua New Guinea (110m)
        'PH' => [11.198, 122.465], // Philippines (110m)
        'PK' => [29.328, 68.546], // Pakistan (110m)
        'PL' => [51.99, 19.49], // Poland (110m)
        'PM' => [47.04, -56.332], // Saint Pierre and Miquelon (50m)
        'PN' => [-24.365, -128.318], // Pitcairn Islands (50m)
        'PR' => [18.235, -66.481], // Puerto Rico (110m)
        'PS' => [32.047, 35.291], // Palestine (110m)
        'PT' => [39.607, -8.272], // Portugal (110m)
        'PW' => [7.518, 134.58], // Palau (50m)
        'PY' => [-21.675, -60.146], // Paraguay (110m)
        'QA' => [25.237, 51.144], // Qatar (110m)
        'RO' => [45.733, 24.973], // Romania (110m)
        'RS' => [44.19, 20.788], // Serbia (110m)
        'RU' => [58.249, 44.686], // Russia (110m)
        'RW' => [-1.897, 30.104], // Rwanda (110m)
        'SA' => [23.807, 44.7], // Saudi Arabia (110m)
        'SB' => [-8.03, 159.17], // Solomon Islands (110m)
        'SC' => [-4.677, 55.48], // Seychelles (50m)
        'SD' => [16.331, 29.261], // Sudan (110m)
        'SE' => [65.859, 19.017], // Sweden (110m)
        'SG' => [1.367, 103.817], // Singapore (50m)
        'SH' => [-15.95, -5.713], // Saint Helena (50m)
        'SI' => [46.061, 14.915], // Slovenia (110m)
        'SK' => [48.734, 19.05], // Slovakia (110m)
        'SL' => [8.617, -11.764], // Sierra Leone (110m)
        'SM' => [43.934, 12.441], // San Marino (50m)
        'SN' => [15.138, -14.779], // Senegal (110m)
        'SO' => [3.569, 45.192], // Somalia (110m)
        'SR' => [4.144, -55.911], // Suriname (110m)
        'SS' => [7.23, 30.39], // South Sudan (110m)
        'ST' => [0.971, 7.021], // São Tomé and Príncipe (50m)
        'SV' => [13.685, -88.89], // El Salvador (110m)
        'SX' => [18.041, -63.07], // Sint Maarten (50m)
        'SY' => [35.007, 38.278], // Syria (110m)
        'SZ' => [-26.534, 31.467], // Eswatini (110m)
        'TC' => [21.817, -71.753], // Turks and Caicos Islands (50m)
        'TD' => [15.143, 18.645], // Chad (110m)
        'TF' => [-49.304, 69.122], // French Southern and Antarctic Lands (110m)
        'TG' => [8.807, 1.058], // Togo (110m)
        'TH' => [15.46, 101.073], // Thailand (110m)
        'TJ' => [38.2, 72.587], // Tajikistan (110m)
        'TL' => [-8.804, 125.855], // East Timor (110m)
        'TM' => [39.855, 58.677], // Turkmenistan (110m)
        'TN' => [33.687, 9.008], // Tunisia (110m)
        'TO' => [-21.21, -175.163], // Tonga (50m)
        'TR' => [39.345, 34.508], // Turkey (110m)
        'TT' => [10.999, -60.918], // Trinidad and Tobago (110m)
        'TV' => [-8.514, 179.21], // Tuvalu (50m)
        'TW' => [23.652, 120.868], // Taiwan (110m)
        'TZ' => [-6.052, 34.959], // Tanzania (110m)
        'UA' => [49.725, 32.141], // Ukraine (110m)
        'UG' => [1.973, 32.949], // Uganda (110m)
        'US' => [39.538, -97.483], // United States of America (110m)
        'UY' => [-32.961, -55.967], // Uruguay (110m)
        'UZ' => [41.694, 64.005], // Uzbekistan (110m)
        'VA' => [41.903, 12.453], // Vatican City (50m)
        'VC' => [13.088, -61.336], // Saint Vincent and the Grenadines (50m)
        'VE' => [7.182, -64.599], // Venezuela (110m)
        'VG' => [18.427, -64.637], // British Virgin Islands (50m)
        'VI' => [17.747, -64.779], // United States Virgin Islands (50m)
        'VN' => [21.715, 105.387], // Vietnam (110m)
        'VU' => [-15.372, 166.909], // Vanuatu (110m)
        'WF' => [-14.286, -178.137], // Wallis and Futuna (50m)
        'WS' => [-13.639, -172.438], // Samoa (50m)
        'XK' => [42.594, 20.861], // Kosovo (110m)
        'YE' => [15.328, 45.874], // Yemen (110m)
        'ZA' => [-29.709, 23.666], // South Africa (110m)
        'ZM' => [-14.661, 26.395], // Zambia (110m)
        'ZW' => [-18.912, 29.925], // Zimbabwe (110m)
    ];

    /**
     * Centroid [lat, lng] for an ISO-2 country code, or null when the code
     * is null/empty/unrecognised. Case-insensitive (the table is uppercase).
     *
     * @return array{0: float, 1: float}|null
     */
    public static function for(?string $iso2): ?array
    {
        if ($iso2 === null || $iso2 === '') {
            return null;
        }

        return self::CENTROIDS[strtoupper(trim($iso2))] ?? null;
    }

    /**
     * True when a centroid exists for the given ISO-2 code. Case-insensitive.
     */
    public static function has(?string $iso2): bool
    {
        return self::for($iso2) !== null;
    }

    /**
     * The full ISO-2 => [lat, lng] table (uppercase keys).
     *
     * @return array<string, array{0: float, 1: float}>
     */
    public static function all(): array
    {
        return self::CENTROIDS;
    }

    /**
     * Number of countries with a centroid. Pinned by a test so an
     * accidental truncation of the table fails loud.
     */
    public static function count(): int
    {
        return count(self::CENTROIDS);
    }
}
