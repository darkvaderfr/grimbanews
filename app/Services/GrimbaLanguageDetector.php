<?php

namespace App\Services;

/**
 * Origin-language detector — Vader 2026-05-16 S-LANG-02.
 *
 * Pure function: takes a signal bag, returns 'fr', 'en', or null.
 * Used at ingest time (to tag a fresh post) and at backfill time
 * (to classify the NULL backlog).
 *
 * Signal precedence (first-wins). See
 * docs/GRIMBANEWS_LANGUAGE_TAGGING_PLAN.md §"Source-of-truth precedence"
 * for the rationale:
 *
 *   1. caller_hint        — fetcher passed an explicit 'fr'/'en'
 *   2. source_language    — news_sources.language was non-null
 *   3. source_url TLD     — conservative allowlist of country-specific TLDs
 *   4. text n-gram        — FR vs EN markers on name + description
 *   5. NULL               — refuse to guess
 *
 * No DB, no HTTP. Safe to call on the ingest hot path.
 */
class GrimbaLanguageDetector
{
    public const DEFAULT_CONFIDENCE = 0.75;

    /** @var array<string, string> TLD → language. Conservative allowlist. */
    private const TLD_MAP = [
        '.fr'    => 'fr',
        '.qc.ca' => 'fr',
        '.gp'    => 'fr', // Guadeloupe
        '.mq'    => 'fr', // Martinique
        '.re'    => 'fr', // Réunion
        '.nc'    => 'fr', // New Caledonia
        '.pf'    => 'fr', // French Polynesia
        '.sn'    => 'fr', // Senegal
        '.ci'    => 'fr', // Côte d'Ivoire
        '.bf'    => 'fr', // Burkina Faso
        '.ml'    => 'fr', // Mali
        '.ne'    => 'fr', // Niger
        '.cm'    => 'fr', // Cameroon (mostly FR media)
        '.dz'    => 'fr', // Algeria (FR + AR; FR dominant in media)
        '.ma'    => 'fr', // Morocco (same)
        '.tn'    => 'fr', // Tunisia
        '.ga'    => 'fr', // Gabon
        '.cg'    => 'fr', // Congo Brazza
        '.cd'    => 'fr', // DR Congo
        '.tg'    => 'fr', // Togo
        '.bj'    => 'fr', // Benin
        '.mg'    => 'fr', // Madagascar

        // English-confident TLDs
        '.com'   => 'en',
        '.co.uk' => 'en',
        '.org.uk' => 'en',
        '.uk'    => 'en',
        '.us'    => 'en',
        '.au'    => 'en',
        '.com.au' => 'en',
        '.nz'    => 'en',
        '.co.nz' => 'en',
        '.ie'    => 'en',
        '.za'    => 'en',
        '.ng'    => 'en',
        '.ke'    => 'en',
        '.gh'    => 'en',
        '.in'    => 'en',
        '.ph'    => 'en',
        '.sg'    => 'en',
    ];

    /** @var array<int, string> FR-distinguishing markers (lowercase). */
    private const FR_MARKERS = [
        ' le ', ' la ', ' les ', " l'", " d'", " qu'", " c'",
        ' de ', ' du ', ' des ', ' et ', ' est ', ' une ', ' un ',
        ' que ', ' qui ', ' pour ', ' avec ', ' sont ', ' dans ',
        ' par ', ' aux ', ' sur ',
        // diacritics — strong FR signal
        'à', 'â', 'ç', 'é', 'è', 'ê', 'ë', 'î', 'ï', 'ô', 'œ', 'ù', 'û',
    ];

    /** @var array<int, string> EN-distinguishing markers (lowercase). */
    private const EN_MARKERS = [
        ' the ', ' and ', ' of ', ' to ', ' is ', ' in ', ' on ',
        ' for ', ' with ', ' that ', ' it ', ' was ', ' has ',
        ' are ', ' said ', ' from ', ' by ', ' have ', ' will ',
        " won't ", " don't ", " can't ", " it's ", " he's ", " she's ",
        // -ing / -tion endings are EN signals
        'tion ', 'ing ',
    ];

    /**
     * @param array{caller_hint?: ?string, source_language?: ?string, source_url?: ?string, text_sample?: ?string} $signals
     */
    public static function detect(array $signals, ?float $confidence = null): ?string
    {
        // 1. Caller hint — fetcher said so.
        if ($lang = self::normalise($signals['caller_hint'] ?? null)) {
            return $lang;
        }

        // 2. news_sources.language — editor- or upstream-provided.
        if ($lang = self::normalise($signals['source_language'] ?? null)) {
            return $lang;
        }

        // 3. Source URL TLD — conservative allowlist.
        if (! empty($signals['source_url']) && is_string($signals['source_url'])) {
            if ($lang = self::fromTld($signals['source_url'])) {
                return $lang;
            }
        }

        // 4. Body n-gram on first 800 chars.
        $sample = (string) ($signals['text_sample'] ?? '');
        if ($sample !== '') {
            $verdict = self::fromText($sample, $confidence ?? self::DEFAULT_CONFIDENCE);
            if ($verdict !== null) {
                return $verdict;
            }
        }

        return null;
    }

    /**
     * Public for testability. Returns 'fr', 'en', or null based on
     * marker density. Confidence is the minimum ratio of dominant
     * markers to total markers required to commit.
     */
    public static function fromText(string $text, float $confidence = self::DEFAULT_CONFIDENCE): ?string
    {
        $sample = mb_strtolower(mb_substr(trim($text), 0, 800));
        if ($sample === '') {
            return null;
        }

        $frHits = 0;
        $enHits = 0;

        foreach (self::FR_MARKERS as $needle) {
            $frHits += substr_count($sample, $needle);
        }
        foreach (self::EN_MARKERS as $needle) {
            $enHits += substr_count($sample, $needle);
        }

        $total = $frHits + $enHits;
        if ($total < 3) {
            return null; // too little signal
        }

        $frRatio = $frHits / $total;
        if ($frRatio >= $confidence) {
            return 'fr';
        }
        if ((1 - $frRatio) >= $confidence) {
            return 'en';
        }

        return null;
    }

    /**
     * Public for testability. TLD → 'fr' / 'en' / null.
     */
    public static function fromTld(string $url): ?string
    {
        $host = parse_url(trim($url), PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }
        $host = strtolower($host);

        // Match longest suffix first.
        $suffixes = array_keys(self::TLD_MAP);
        usort($suffixes, fn ($a, $b) => strlen($b) - strlen($a));
        foreach ($suffixes as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return self::TLD_MAP[$suffix];
            }
        }

        return null;
    }

    /**
     * Normalise any FR / EN / full-name variant to ISO-2.
     * Returns null on unknown.
     */
    public static function normalise(mixed $raw): ?string
    {
        if (! is_string($raw)) {
            return null;
        }
        $value = strtolower(trim($raw));
        if ($value === '' || $value === 'unknown' || $value === 'null') {
            return null;
        }

        // Already ISO-2 / -5
        if (preg_match('/^(fr|en)([_-][a-z0-9]{2,4})?$/', $value)) {
            return substr($value, 0, 2);
        }

        $map = [
            'french'   => 'fr',
            'français' => 'fr',
            'francais' => 'fr',
            'fra'      => 'fr',
            'fre'      => 'fr',
            'english'  => 'en',
            'eng'      => 'en',
        ];

        return $map[$value] ?? null;
    }
}
