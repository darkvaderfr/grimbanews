<?php

/*
 * S-LANG-10 (Vader 2026-05-16) — translation work-map admin page.
 *
 * Reader-friendly view of what needs translating per locale. Driven by
 * the existing `grimba_post_translations` table — no new storage. Read-
 * only: this page shows operators where the gaps are; actual translation
 * happens via the scheduled `grimba:translate-pending` job.
 *
 * Route: /admin/grimba/translation-map  →  grimba.translation-map.index
 */

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('translation-map', function () {
            $hasTranslationsTable = Schema::hasTable('grimba_post_translations');
            $hasOriginalLang      = Schema::hasColumn('posts', 'original_language');
            $hasInRowTrans        = Schema::hasColumn('posts', 'translated_to')
                && Schema::hasColumn('posts', 'translated_name');

            // Per-locale work map. Counts posts whose origin is the
            // OPPOSITE locale AND which do NOT have a translated row
            // yet (either in posts.translated_* or in grimba_post_translations).
            $work = [];
            foreach (['fr' => 'en', 'en' => 'fr'] as $target => $source) {
                if (! $hasOriginalLang) {
                    $work[$target] = ['source' => $source, 'pending' => 0, 'done' => 0];
                    continue;
                }

                $base = DB::table('posts')
                    ->where('posts.status', 'published')
                    ->whereRaw("lower(substr(coalesce(posts.original_language, ''), 1, 2)) = ?", [$source]);

                $pending = (clone $base);
                if ($hasInRowTrans) {
                    $pending->where(function ($q) use ($target): void {
                        $q->whereNull('posts.translated_to')
                          ->orWhereRaw("lower(substr(coalesce(posts.translated_to, ''), 1, 2)) != ?", [$target])
                          ->orWhereNull('posts.translated_name')
                          ->orWhere('posts.translated_name', '');
                    });
                }
                if ($hasTranslationsTable) {
                    $pending->whereNotExists(function ($q) use ($target): void {
                        $q->select(DB::raw(1))
                          ->from('grimba_post_translations')
                          ->whereColumn('grimba_post_translations.post_id', 'posts.id')
                          ->whereRaw("lower(grimba_post_translations.locale) = ?", [$target])
                          ->whereNotNull('grimba_post_translations.translated_name')
                          ->whereRaw("trim(grimba_post_translations.translated_name) != ''");
                    });
                }

                $pendingCount = (int) $pending->count();
                $totalCount   = (int) (clone $base)->count();

                $work[$target] = [
                    'source'  => $source,
                    'pending' => $pendingCount,
                    'done'    => max(0, $totalCount - $pendingCount),
                    'total'   => $totalCount,
                ];
            }

            // Per-source breakdown — the top 15 publishers ranked by
            // pending count. Helps the operator spot which feeds drive
            // the backlog.
            $perSourceFr = collect();
            $perSourceEn = collect();
            if ($hasOriginalLang) {
                foreach (['fr', 'en'] as $target) {
                    $source = $target === 'fr' ? 'en' : 'fr';
                    $rows = DB::table('posts')
                        ->leftJoin('news_sources', 'news_sources.id', '=', 'posts.source_id')
                        ->where('posts.status', 'published')
                        ->whereRaw("lower(substr(coalesce(posts.original_language, ''), 1, 2)) = ?", [$source])
                        ->when($hasInRowTrans, fn ($q) => $q->where(function ($qq) use ($target): void {
                            $qq->whereNull('posts.translated_to')
                               ->orWhereRaw("lower(substr(coalesce(posts.translated_to, ''), 1, 2)) != ?", [$target])
                               ->orWhereNull('posts.translated_name')
                               ->orWhere('posts.translated_name', '');
                        }))
                        ->when($hasTranslationsTable, fn ($q) => $q->whereNotExists(function ($qq) use ($target): void {
                            $qq->select(DB::raw(1))
                               ->from('grimba_post_translations')
                               ->whereColumn('grimba_post_translations.post_id', 'posts.id')
                               ->whereRaw("lower(grimba_post_translations.locale) = ?", [$target])
                               ->whereNotNull('grimba_post_translations.translated_name')
                               ->whereRaw("trim(grimba_post_translations.translated_name) != ''");
                        }))
                        ->select('news_sources.name as source_name', DB::raw('count(*) as pending'))
                        ->groupBy('news_sources.name')
                        ->orderByDesc('pending')
                        ->limit(15)
                        ->get();

                    if ($target === 'fr') {
                        $perSourceFr = $rows;
                    } else {
                        $perSourceEn = $rows;
                    }
                }
            }

            // NULL-language posts — the detector backlog. These can't be
            // translated yet because we don't know which way to go.
            $unclassifiedCount = $hasOriginalLang
                ? (int) DB::table('posts')
                    ->where('status', 'published')
                    ->where(function ($q): void {
                        $q->whereNull('original_language')->orWhere('original_language', '');
                    })
                    ->count()
                : 0;

            // Total work-map cap so the UI can show progress.
            $totalPending = $work['fr']['pending'] + $work['en']['pending'];
            $totalDone    = $work['fr']['done']    + $work['en']['done'];

            // S-LANG-13 (Vader 2026-05-17) — per-source coverage table.
            // For every source with at least one published post, show
            // total / FR / EN / unclassified / translated counts so the
            // operator can see which publishers have the worst gaps.
            $perSourceCoverage = collect();
            if ($hasOriginalLang) {
                $perSourceCoverage = DB::table('news_sources')
                    ->leftJoin('posts', function ($join): void {
                        $join->on('posts.source_id', '=', 'news_sources.id')
                             ->where('posts.status', 'published');
                    })
                    ->select(
                        'news_sources.id',
                        'news_sources.name',
                        'news_sources.language as source_lang',
                        DB::raw('count(posts.id) as total'),
                        DB::raw("sum(case when lower(substr(coalesce(posts.original_language, ''), 1, 2)) = 'fr' then 1 else 0 end) as fr_count"),
                        DB::raw("sum(case when lower(substr(coalesce(posts.original_language, ''), 1, 2)) = 'en' then 1 else 0 end) as en_count"),
                        DB::raw("sum(case when coalesce(posts.original_language, '') = '' then 1 else 0 end) as unknown_count"),
                        DB::raw("sum(case when posts.translated_name is not null and trim(posts.translated_name) != '' then 1 else 0 end) as in_row_translated")
                    )
                    ->groupBy('news_sources.id', 'news_sources.name', 'news_sources.language')
                    ->having('total', '>', 0)
                    ->orderByDesc('total')
                    ->limit(40)
                    ->get();
            }

            return view('grimba-admin.translation-map.index', [
                'pageTitle' => 'Translation work-map',
                'work' => $work,
                'perSourceCoverage' => $perSourceCoverage,
                'perSourceFr' => $perSourceFr,
                'perSourceEn' => $perSourceEn,
                'unclassifiedCount' => $unclassifiedCount,
                'totalPending' => $totalPending,
                'totalDone' => $totalDone,
                'hasTranslationsTable' => $hasTranslationsTable,
                'hasOriginalLang' => $hasOriginalLang,
            ]);
        })->name('translation-map.index');
    });

DashboardMenu::default()->beforeRetrieving(function (): void {
    DashboardMenu::make()->registerItem(
        DashboardMenuItem::make()
            ->id('grimba-translation-map')
            ->priority(64)
            ->parentId('grimba-root')
            ->name('Translation map')
            ->icon('ti ti-language')
            ->route('grimba.translation-map.index')
    );
});
