<?php

namespace App\Support;

/**
 * S-LSAT-09 (Vader 2026-05-18) — pure-function rule engine that
 * decides which posts should be auto-translated to the opposite
 * locale.
 *
 * Inputs are stdClass-like rows from `DB::table('posts')->get(…)`
 * or Eloquent models — anything that exposes `original_language`,
 * `editorial_region`, `views`, `translated_to`. The engine returns
 * a `Decision` object:
 *
 *   - `shouldTranslate`     bool — green light for the queue
 *   - `targetLocale`        ?string — 'fr' or 'en' (opposite of post locale)
 *   - `reason`              string — audit log breadcrumb
 *   - `priority`            int — 0..2, written into `translation_priority`
 *
 * Decision matrix (matches the published plan):
 *   1. Post has no detectable language → skip (return null targetLocale).
 *   2. Post already has a translation in the opposite locale → skip
 *      (the work-map will mop up partial coverage separately).
 *   3. Post's editorial region is in `force_both_regions` (default
 *      Africa) AND views >= africaThreshold (default 100) → translate.
 *   4. Post's views >= global popularity threshold (default 500) →
 *      translate. This is the "Le Monde @ 500 views" trigger Vader
 *      asked about.
 *   5. Anything else → skip.
 *
 * Priority levels:
 *   2 = editorial pin (reserved for manual operator action; this
 *       engine never sets it)
 *   1 = rule fired and queued for translation
 *   0 = default / nothing queued
 *
 * The rule engine is pure — it does NO database writes, no I/O, no
 * cache touches. It just returns a Decision. The artisan command
 * (S-LSAT-10) is what writes `translation_priority` and enqueues
 * the actual translation job.
 */
class GrimbaTranslationRules
{
    public static function decide(object|array $post): Decision
    {
        $row = (object) $post;

        $origin = strtolower(substr((string) ($row->original_language ?? ''), 0, 2));
        if ($origin !== 'fr' && $origin !== 'en') {
            return new Decision(false, null, 'no-detectable-language', 0);
        }

        $target = $origin === 'fr' ? 'en' : 'fr';

        // Already has a translation in the target locale.
        $translatedTo = strtolower(substr((string) ($row->translated_to ?? ''), 0, 2));
        if ($translatedTo === $target) {
            return new Decision(false, $target, 'already-translated', 0);
        }

        $views = (int) ($row->views ?? 0);
        $region = strtolower((string) ($row->editorial_region ?? ''));
        $forced = GrimbaLanguageSettings::forceBothRegions();
        $globalThreshold = GrimbaLanguageSettings::popularityThreshold();
        $africaThreshold = GrimbaLanguageSettings::popularityThresholdAfrica();

        // Force-both region rule: lower threshold, e.g. Africa posts
        // translate at 100 views instead of 500.
        if ($region !== '' && in_array($region, $forced, true)) {
            if ($views >= $africaThreshold) {
                return new Decision(
                    true,
                    $target,
                    sprintf('force-both-region:%s views=%d>=%d', $region, $views, $africaThreshold),
                    1,
                );
            }
            // Region forced but not yet hot enough.
            return new Decision(false, $target, sprintf('region-forced-below-threshold views=%d<%d', $views, $africaThreshold), 0);
        }

        // Global popularity threshold (the "Le Monde @ 500" rule).
        if ($views >= $globalThreshold) {
            return new Decision(
                true,
                $target,
                sprintf('popularity-threshold views=%d>=%d', $views, $globalThreshold),
                1,
            );
        }

        return new Decision(
            false,
            $target,
            sprintf('below-threshold views=%d<%d', $views, $globalThreshold),
            0,
        );
    }

    /**
     * Filter a list of post rows down to those that should be
     * translated. Used by the artisan command to build its work
     * queue. Respects the daily cap from settings — once the cap
     * is hit, the remaining rows are silently dropped (the next
     * cron tick picks them up tomorrow).
     *
     * @param iterable<object|array> $posts
     * @param int $callsAlreadyToday Number of translation calls
     *        already burned today; passed in by the caller so the
     *        rule engine stays free of side-effects.
     * @return array<int, array{post: object, decision: Decision}>
     */
    public static function selectTranslatable(iterable $posts, int $callsAlreadyToday = 0): array
    {
        $cap = GrimbaLanguageSettings::ruleEngineDailyCap();
        $remaining = max(0, $cap - $callsAlreadyToday);
        if ($remaining <= 0) {
            return [];
        }
        $out = [];
        foreach ($posts as $p) {
            $decision = self::decide($p);
            if ($decision->shouldTranslate) {
                $out[] = ['post' => (object) $p, 'decision' => $decision];
                if (count($out) >= $remaining) {
                    break;
                }
            }
        }
        return $out;
    }
}

/**
 * @internal Value object — never persisted, just returned to the
 * caller so it can decide whether to write `translation_priority`
 * and/or call the translator.
 */
final class Decision
{
    public function __construct(
        public readonly bool $shouldTranslate,
        public readonly ?string $targetLocale,
        public readonly string $reason,
        public readonly int $priority,
    ) {
    }
}
