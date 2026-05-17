<?php

namespace App\Console\Commands;

use App\Services\GrimbaLanguageDetector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Retroactive language-tagging — Vader 2026-05-16 S-LANG-04.
 *
 * Classifies every post.original_language that's currently NULL (or
 * every post when --reclassify is set) using GrimbaLanguageDetector.
 * Signals fed into the detector (in precedence order, first-wins):
 *   - news_sources.language (joined via source_id)
 *   - the publisher canonical URL (from rss_feed_items / newsapi_items
 *     / grimba_live_news_items — whichever has a row for this post)
 *   - the post's name + description, first 800 chars combined
 *
 * Bubbles up the verdict to news_sources.language WHEN that row is
 * currently NULL — so subsequent posts from the same source benefit
 * without re-running the detector.
 *
 * Pattern mirrors GrimbaBackfillEditorialRegions.php.
 */
class GrimbaBackfillLanguage extends Command
{
    protected $signature = 'grimba:backfill-language
        {--batch=500 : posts per batch}
        {--limit= : optional cap on total posts processed}
        {--reclassify : update rows that already have a language set}
        {--confidence=0.75 : minimum n-gram confidence (0..1)}
        {--dry : count only, no writes}';

    protected $description = 'Backfill posts.original_language from source language + URL + text signals.';

    public function handle(): int
    {
        if (! Schema::hasColumn('posts', 'original_language')) {
            $this->error('posts.original_language column not present. Run migration 2026_04_24_000000_add_original_language_to_posts.php first.');
            return self::FAILURE;
        }

        $batch         = max(50, (int) $this->option('batch'));
        $limitOption   = $this->option('limit');
        $limit         = $limitOption !== null ? max(1, (int) $limitOption) : null;
        $reclassify    = (bool) $this->option('reclassify');
        $confidence    = (float) $this->option('confidence');
        $dry           = (bool) $this->option('dry');

        $touched      = 0;
        $perLang      = ['fr' => 0, 'en' => 0, 'unknown' => 0];
        $sourcesPatched = 0;

        $lastId = 0;
        while (true) {
            if ($limit !== null && $touched >= $limit) {
                break;
            }

            $rows = DB::table('posts')
                ->leftJoin('news_sources', 'news_sources.id', '=', 'posts.source_id')
                ->select([
                    'posts.id',
                    'posts.source_id',
                    'posts.name',
                    'posts.description',
                    'posts.original_language',
                    'news_sources.language as src_lang',
                    'news_sources.website as src_url',
                ])
                ->where('posts.id', '>', $lastId)
                ->when(! $reclassify, fn ($q) => $q->whereNull('posts.original_language'))
                ->orderBy('posts.id')
                ->limit($batch)
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            // Zen audit 2026-05-16 — wrap each batch in a transaction
            // so a Ctrl-C / fatal mid-batch can't leave a partial state.
            // Per-batch (not per-row) keeps the lock window tight.
            $batchTouched = 0;
            $batchFr = 0;
            $batchEn = 0;
            $batchUnknown = 0;
            $batchSourcesPatched = 0;
            $batchLastId = $lastId;

            DB::transaction(function () use (
                $rows, $confidence, $dry,
                &$batchTouched, &$batchFr, &$batchEn, &$batchUnknown,
                &$batchSourcesPatched, &$batchLastId, &$touched, $limit
            ): void {
                foreach ($rows as $row) {
                    $batchLastId = (int) $row->id;
                    if ($limit !== null && ($touched + $batchTouched) >= $limit) {
                        break;
                    }

                    $sample = trim(((string) $row->name) . "\n" . strip_tags((string) $row->description));
                    if ($sample === '' && ! $row->src_lang && ! $row->src_url) {
                        $batchUnknown++;
                        $batchTouched++;
                        continue;
                    }

                    $verdict = GrimbaLanguageDetector::detect([
                        'source_language' => $row->src_lang,
                        'source_url'      => $row->src_url,
                        'text_sample'     => $sample,
                    ], $confidence);

                    if ($verdict === null) {
                        $batchUnknown++;
                        $batchTouched++;
                        continue;
                    }

                    if (! $dry) {
                        DB::table('posts')->where('id', $row->id)->update([
                            'original_language' => $verdict,
                        ]);
                        if ($row->source_id && empty($row->src_lang)) {
                            DB::table('news_sources')->where('id', $row->source_id)
                                ->whereNull('language')
                                ->update(['language' => $verdict]);
                            $batchSourcesPatched++;
                        }
                    }

                    $verdict === 'fr' ? $batchFr++ : $batchEn++;
                    $batchTouched++;
                }
            });

            $touched += $batchTouched;
            $perLang['fr'] += $batchFr;
            $perLang['en'] += $batchEn;
            $perLang['unknown'] += $batchUnknown;
            $sourcesPatched += $batchSourcesPatched;
            $lastId = $batchLastId;

            $this->line("  · {$touched} touched (lastId={$lastId}) — fr={$perLang['fr']} en={$perLang['en']} unknown={$perLang['unknown']}");
            if ($limit !== null && $touched >= $limit) {
                break;
            }
        }

        $this->newLine();
        $total = max(1, $touched);
        $unknownPct = round($perLang['unknown'] * 100 / $total, 1);

        $this->table(
            ['Bucket', 'Count', 'Share'],
            [
                ['fr',      $perLang['fr'],      round($perLang['fr'] * 100 / $total, 1) . '%'],
                ['en',      $perLang['en'],      round($perLang['en'] * 100 / $total, 1) . '%'],
                ['unknown', $perLang['unknown'], $unknownPct . '%'],
                ['TOTAL touched', $touched, '100%'],
                ['news_sources patched', $sourcesPatched, ''],
            ]
        );

        $this->info($dry ? 'DRY RUN — no writes.' : 'Backfill complete.');

        return self::SUCCESS;
    }
}
