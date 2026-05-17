<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * S-LANG-11 helper — dossier (story cluster) language modal computation.
 *
 * The modal language of a dossier = the language with the highest share
 * of its published posts. We commit to that language only when the modal
 * share clears `grimba_dossier_lang_modal_min` (default 0.6 = 60%);
 * below that we leave `primary_language` NULL so the UI doesn't claim
 * a side it shouldn't. Unknown-language posts are excluded from the
 * denominator so two FR + one NULL still resolves to FR.
 */
class GrimbaDossierLanguage
{
    public const DEFAULT_MODAL_THRESHOLD = 0.6;

    /**
     * Recompute the language modal for a single cluster. Writes through
     * to `story_clusters.primary_language` + `language_mix_json` +
     * `language_recomputed_at` when the column set is present.
     *
     * @return array{primary_language: ?string, mix: array<string, int>}
     */
    public static function recompute(int $clusterId, ?float $threshold = null): array
    {
        $threshold = $threshold ?? self::threshold();

        $rows = DB::table('posts')
            ->select(DB::raw("lower(substr(coalesce(original_language, ''), 1, 2)) as lang"), DB::raw('count(*) as n'))
            ->where('story_cluster_id', $clusterId)
            ->where('status', 'published')
            ->groupBy('lang')
            ->pluck('n', 'lang')
            ->toArray();

        $mix = [
            'fr'      => (int) ($rows['fr'] ?? 0),
            'en'      => (int) ($rows['en'] ?? 0),
            'unknown' => (int) ($rows[''] ?? 0),
        ];
        $known = $mix['fr'] + $mix['en'];

        $primary = null;
        if ($known > 0) {
            $frShare = $mix['fr'] / $known;
            if ($frShare >= $threshold) {
                $primary = 'fr';
            } elseif ((1 - $frShare) >= $threshold) {
                $primary = 'en';
            }
        }

        if (Schema::hasColumn('story_clusters', 'primary_language')) {
            DB::table('story_clusters')->where('id', $clusterId)->update([
                'primary_language' => $primary,
                'language_mix_json' => json_encode($mix),
                'language_recomputed_at' => Carbon::now(),
            ]);
        }

        return ['primary_language' => $primary, 'mix' => $mix];
    }

    /**
     * Sweep every cluster touched since `$since` (defaults to all when
     * `$since` is null). Returns counts.
     *
     * @return array{processed: int, fr: int, en: int, unknown: int}
     */
    public static function recomputeStale(?Carbon $since = null, int $batch = 100): array
    {
        $counts = ['processed' => 0, 'fr' => 0, 'en' => 0, 'unknown' => 0];

        $query = DB::table('story_clusters')->select('id');
        if ($since !== null && Schema::hasColumn('story_clusters', 'language_recomputed_at')) {
            $query->where(function ($q) use ($since): void {
                $q->whereNull('language_recomputed_at')
                  ->orWhere('language_recomputed_at', '<', $since);
            });
        }

        $query->orderBy('id')->chunkById($batch, function ($clusters) use (&$counts): void {
            foreach ($clusters as $row) {
                $result = self::recompute((int) $row->id);
                $counts['processed']++;
                $key = $result['primary_language'] ?: 'unknown';
                $counts[$key]++;
            }
        });

        return $counts;
    }

    public static function threshold(): float
    {
        $raw = function_exists('setting')
            ? (float) setting('grimba_dossier_lang_modal_min', self::DEFAULT_MODAL_THRESHOLD)
            : self::DEFAULT_MODAL_THRESHOLD;

        return max(0.5, min(1.0, $raw));
    }
}
