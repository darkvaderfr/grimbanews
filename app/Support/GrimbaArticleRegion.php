<?php

namespace App\Support;

/**
 * Vader 2026-05-18 — topical editorial-region detector.
 *
 * The source-country fallback (Regions::regionForCountry()) tags
 * articles by who PUBLISHED them, not by what they're ABOUT. That
 * lands an army of Le Monde / RFI / France 24 articles about Mali,
 * Senegal, Cameroon etc. in the EUROPE bucket, leaving the /africa
 * page light. Vader's directive: "many articles are not showing up
 * for many editorial regions … let's retroactively tag every
 * article."
 *
 * This class scans the article's title + description + nobu summary
 * for region anchors (country names, capitals, demonyms, major
 * cities) and returns the strongest topical region, or null when
 * no strong signal exists.
 *
 * Conservative defaults: at least 3 weighted points in the winning
 * region AND ≥ 2× margin over the runner-up. Otherwise the
 * source-country tag stays.
 *
 * Keywords stay literal — no fuzzy matching, no regex backtracking
 * blowups. The corpus is FR + EN, so the anchors include both.
 */
class GrimbaArticleRegion
{
    /**
     * @var array<string, array<int, string>>
     */
    private const ANCHORS = [
        'africa' => [
            // North Africa
            'Algérie', 'Algerie', 'Algeria', 'Algiers', 'Alger',
            'Maroc', 'Morocco', 'Rabat', 'Casablanca', 'marocain', 'Moroccan',
            'Tunisie', 'Tunisia', 'Tunis', 'tunisien',
            'Libye', 'Libya', 'Tripoli',
            'Egypte', 'Egypt', 'Cairo', 'Caire', 'égyptien', 'Egyptian',
            'Soudan', 'Sudan', 'Khartoum',
            'Mauritanie', 'Mauritania', 'Nouakchott',
            // West Africa
            'Sénégal', 'Senegal', 'Dakar', 'sénégalais', 'Senegalese',
            'Mali', 'Bamako', 'malien', 'Malian',
            'Burkina Faso', 'Ouagadougou', 'burkinab',
            'Côte d\'Ivoire', 'Cote d Ivoire', 'Ivory Coast', 'Abidjan', 'Ivoirian', 'ivoirien',
            'Ghana', 'Accra', 'ghanéen', 'Ghanaian',
            'Nigéria', 'Nigeria', 'Lagos', 'Abuja', 'nigérian', 'Nigerian',
            'Niger', 'Niamey',
            'Bénin', 'Benin', 'Cotonou',
            'Togo', 'Lomé',
            'Guinée', 'Guinea', 'Conakry', 'guinéen', 'Guinean',
            'Sierra Leone', 'Freetown',
            'Libéria', 'Liberia', 'Monrovia',
            'Gambie', 'Gambia', 'Banjul',
            'Cap-Vert', 'Cape Verde',
            // Central Africa
            'Cameroun', 'Cameroon', 'Yaoundé', 'Douala', 'camerounais', 'Cameroonian',
            'Tchad', 'Chad', 'N\'Djamena', 'Ndjamena',
            'République centrafricaine', 'Central African Republic', 'Bangui', 'centrafricain',
            'Gabon', 'Libreville', 'gabonais',
            'Congo', 'Kinshasa', 'Brazzaville', 'congolais', 'Congolese',
            'RDC', 'DRC',
            'Rwanda', 'Kigali', 'rwandais',
            'Burundi', 'Bujumbura',
            'Guinée équatoriale', 'Equatorial Guinea', 'Malabo',
            // East Africa
            'Kenya', 'Nairobi', 'kényan', 'Kenyan',
            'Tanzanie', 'Tanzania', 'Dodoma', 'Dar es Salaam',
            'Ouganda', 'Uganda', 'Kampala',
            'Ethiopie', 'Ethiopia', 'Addis-Abeba', 'éthiopien', 'Ethiopian',
            'Erythrée', 'Eritrea', 'Asmara',
            'Djibouti',
            'Somalie', 'Somalia', 'Mogadiscio', 'Mogadishu',
            'Soudan du Sud', 'South Sudan', 'Juba',
            'Madagascar', 'Antananarivo', 'malgache',
            'Maurice', 'Mauritius', 'Port-Louis',
            'Seychelles',
            'Comores', 'Comoros',
            // Southern Africa
            'Afrique du Sud', 'South Africa', 'Pretoria', 'Johannesburg', 'Cape Town', 'sud-africain', 'South African',
            'Mozambique', 'Maputo',
            'Zimbabwe', 'Harare',
            'Zambie', 'Zambia', 'Lusaka',
            'Botswana', 'Gaborone',
            'Namibie', 'Namibia', 'Windhoek',
            'Angola', 'Luanda',
            'Lesotho', 'Maseru',
            'Eswatini', 'Swaziland',
            'Malawi', 'Lilongwe',
            // Trans-African
            'Sahel', 'sahelien', 'Sahelian',
            'Afrique', 'Africa', 'africain', 'African',
            'Union africaine', 'African Union', 'ECOWAS', 'CEDEAO',
            'Sahara', 'Maghreb', 'maghrébin',
        ],
        'europe' => [
            'France', 'Paris', 'Lyon', 'Marseille', 'Macron', 'français', 'French',
            'Allemagne', 'Germany', 'Berlin', 'Munich', 'Scholz', 'Merkel', 'allemand', 'German',
            'Italie', 'Italy', 'Rome', 'Milan', 'Meloni', 'italien', 'Italian',
            'Espagne', 'Spain', 'Madrid', 'Barcelona', 'Sánchez', 'espagnol', 'Spanish',
            'Portugal', 'Lisbonne', 'Lisbon', 'portugais', 'Portuguese',
            'Royaume-Uni', 'United Kingdom', 'Britain', 'London', 'Londres', 'British', 'britannique',
            'Belgique', 'Belgium', 'Bruxelles', 'Brussels',
            'Pays-Bas', 'Netherlands', 'Amsterdam', 'Dutch', 'néerlandais',
            'Suisse', 'Switzerland', 'Berne', 'Bern', 'Zurich', 'Genève', 'Geneva', 'suisse',
            'Autriche', 'Austria', 'Vienne', 'Vienna',
            'Pologne', 'Poland', 'Varsovie', 'Warsaw', 'polonais', 'Polish',
            'Ukraine', 'Kyiv', 'Kiev', 'ukrainien', 'Ukrainian', 'Zelensky',
            'Russie', 'Russia', 'Moscou', 'Moscow', 'Poutine', 'Putin', 'russe', 'Russian',
            'Hongrie', 'Hungary', 'Budapest', 'Orbán',
            'République tchèque', 'Czech Republic', 'Prague', 'tchèque', 'Czech',
            'Slovaquie', 'Slovakia',
            'Roumanie', 'Romania', 'Bucharest', 'Bucarest',
            'Bulgarie', 'Bulgaria', 'Sofia',
            'Grèce', 'Greece', 'Athens', 'Athènes', 'grec', 'Greek',
            'Turquie', 'Turkey', 'Istanbul', 'Ankara', 'Erdogan', 'turc', 'Turkish',
            'Suède', 'Sweden', 'Stockholm', 'suédois', 'Swedish',
            'Norvège', 'Norway', 'Oslo', 'norvégien', 'Norwegian',
            'Finlande', 'Finland', 'Helsinki',
            'Danemark', 'Denmark', 'Copenhagen', 'Copenhague', 'danois',
            'Irlande', 'Ireland', 'Dublin', 'irlandais', 'Irish',
            'Croatie', 'Croatia',
            'Serbie', 'Serbia', 'Belgrade',
            'Slovénie', 'Slovenia',
            'Estonie', 'Estonia',
            'Lettonie', 'Latvia',
            'Lituanie', 'Lithuania',
            'Union européenne', 'European Union', 'Bruxelles', 'EU summit', 'Parlement européen', 'European Parliament',
            'OTAN', 'NATO',
            'Brexit',
            'Europe', 'européen', 'European',
        ],
        'americas' => [
            'États-Unis', 'Etats-Unis', 'United States', 'USA', 'America',
            'Washington', 'New York', 'Los Angeles', 'Chicago', 'Boston', 'Miami', 'San Francisco',
            'Trump', 'Biden', 'Harris', 'Kamala', 'Obama',
            'Maison Blanche', 'White House', 'Pentagon', 'Capitol',
            'Congrès américain', 'US Congress', 'Senate', 'Sénat américain',
            'Wall Street', 'Silicon Valley',
            'américain', 'American',
            'Canada', 'Ottawa', 'Toronto', 'Montréal', 'Vancouver', 'Trudeau', 'canadien', 'Canadian',
            'Mexique', 'Mexico', 'Mexico City', 'mexicain', 'Mexican',
            'Brésil', 'Brazil', 'Brasília', 'Brasilia', 'São Paulo', 'Rio de Janeiro', 'Lula', 'Bolsonaro', 'brésilien', 'Brazilian',
            'Argentine', 'Argentina', 'Buenos Aires', 'Milei', 'argentin', 'Argentinian',
            'Chili', 'Chile', 'Santiago', 'chilien', 'Chilean',
            'Colombie', 'Colombia', 'Bogotá', 'Bogota', 'colombien', 'Colombian',
            'Venezuela', 'Caracas', 'Maduro', 'vénézuélien', 'Venezuelan',
            'Pérou', 'Peru', 'Lima', 'péruvien', 'Peruvian',
            'Bolivie', 'Bolivia', 'La Paz',
            'Equateur', 'Ecuador', 'Quito',
            'Uruguay', 'Montevideo',
            'Paraguay', 'Asunción',
            'Cuba', 'La Havane', 'Havana', 'cubain', 'Cuban',
            'Haïti', 'Haiti', 'Port-au-Prince', 'haïtien', 'Haitian',
            'République dominicaine', 'Dominican Republic',
            'Jamaïque', 'Jamaica', 'Kingston',
            'Costa Rica', 'San José',
            'Panama', 'Panama City',
            'Guatemala',
            'Honduras', 'Tegucigalpa',
            'Nicaragua', 'Managua', 'Ortega',
            'Salvador', 'San Salvador', 'Bukele',
            'Belize',
            'Amérique latine', 'Latin America', 'latino-américain',
            'Amériques', 'Americas',
            'amérindien',
        ],
    ];

    /**
     * Lower-cased + normalized version of each anchor list. Built on
     * first call to keep the const literal readable while letting
     * the scanner stay case- and accent-insensitive.
     *
     * @var array<string, array<int, string>>|null
     */
    private static ?array $normalized = null;

    /**
     * Detect the strongest topical region from the article's text.
     * Returns the region key when one region wins by the required
     * margin; null when the text is too thin or two regions tie.
     *
     * Use `detectAllFromText()` to get the secondary region for
     * cross-region stories (S-LSAT-18 — e.g. "Macron meets
     * Zelensky in Kigali" pings africa + europe).
     */
    public static function detectFromText(string $title, string $description = '', string $summary = ''): ?string
    {
        $all = self::detectAllFromText($title, $description, $summary);
        return $all['primary'] ?? null;
    }

    /**
     * S-LSAT-18 (Vader 2026-05-18) — multi-tag region detection.
     *
     * Returns an array with `primary` (string|null) and
     * `secondary` (string|null) keys:
     *   - `primary` follows the same 3-point + 2× margin contract
     *     as `detectFromText()`. Stays null when the text is thin.
     *   - `secondary` fires when the runner-up has ≥ 3 points AND
     *     is within 1.3× of the winner. Captures stories that
     *     genuinely span two regions (e.g. Macron-meets-Zelensky-in-
     *     Kigali pings europe + africa). Stays null when one
     *     region clearly dominates.
     *
     * @return array{primary: ?string, secondary: ?string}
     */
    public static function detectAllFromText(string $title, string $description = '', string $summary = ''): array
    {
        $normalized = self::normalized();

        $titleNorm = self::normalize($title);
        $bodyNorm = self::normalize($description . ' ' . $summary);

        $scores = ['africa' => 0, 'europe' => 0, 'americas' => 0];

        foreach ($normalized as $region => $anchors) {
            foreach ($anchors as $anchor) {
                if ($anchor === '') {
                    continue;
                }
                if (str_contains($titleNorm, $anchor)) {
                    $scores[$region] += 3;
                }
                if (str_contains($bodyNorm, $anchor)) {
                    $scores[$region] += 1;
                }
            }
        }

        arsort($scores);
        $regions = array_keys($scores);
        $top = $regions[0];
        $topScore = $scores[$top];
        $runnerUp = $regions[1] ?? null;
        $runnerUpScore = $runnerUp !== null ? $scores[$runnerUp] : 0;

        // Floor: title must carry SOME signal.
        if ($topScore < 3) {
            return ['primary' => null, 'secondary' => null];
        }

        // Three branches based on the runner-up's strength:
        //
        // 1. Runner-up < 3 points:
        //    Top clearly dominates. primary = top, no secondary.
        //
        // 2. Runner-up >= 3 AND topScore < runnerUp * 1.3:
        //    Genuinely tied (within 30%). Neither region clearly
        //    leads. primary = null (preserves original "no dominant
        //    region" behavior). No secondary.
        //
        // 3. Runner-up >= 3 AND topScore >= runnerUp * 1.3:
        //    Top leads with a real margin AND the runner-up has
        //    real signal of its own. This is the cross-region
        //    story case Vader cited ("Macron meets Zelensky in
        //    Kigali"). primary = top, secondary = runnerUp.
        //
        // The old 2× margin rule was too conservative: it rejected
        // cases like 8 vs 5 (ratio 1.6) as "no primary", losing the
        // dominant region. The new 1.3× rule + multi-tag handle
        // that case more usefully.

        if ($runnerUpScore < 3) {
            return ['primary' => $top, 'secondary' => null];
        }

        if ($topScore < $runnerUpScore * 1.3) {
            // Genuinely tied — don't pick a primary.
            return ['primary' => null, 'secondary' => null];
        }

        return ['primary' => $top, 'secondary' => $runnerUp];
    }

    private static function normalize(string $s): string
    {
        // Strip accents → lowercase. Keeps the literal-match scanner
        // permissive enough that "Senegal" and "Sénégal" both fire.
        // iconv TRANSLIT depends on locale + can produce noisy quotes
        // ("É" → "'E"). Use an explicit character map for stable,
        // locale-independent results.
        static $map = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ã' => 'a', 'Ä' => 'a', 'Å' => 'a',
            'ç' => 'c', 'Ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i',
            'ñ' => 'n', 'Ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o', 'Ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y', 'Ý' => 'y',
            'ß' => 'ss', 'œ' => 'oe', 'Œ' => 'oe', 'æ' => 'ae', 'Æ' => 'ae',
        ];
        $s = strtr($s, $map);
        $s = strtolower($s);
        // Replace whitespace runs with single spaces for word-boundary stability.
        return preg_replace('/\s+/u', ' ', $s) ?? $s;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function normalized(): array
    {
        if (self::$normalized !== null) {
            return self::$normalized;
        }
        $out = [];
        foreach (self::ANCHORS as $region => $list) {
            $norm = [];
            foreach ($list as $anchor) {
                $n = self::normalize($anchor);
                if ($n !== '') {
                    $norm[] = $n;
                }
            }
            $out[$region] = array_values(array_unique($norm));
        }
        self::$normalized = $out;
        return self::$normalized;
    }
}
