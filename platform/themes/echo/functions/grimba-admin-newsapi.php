<?php

/*
 * S130 — admin settings page for the NewsAPI ingest pipeline.
 *
 * Routes under /admin/grimba/newsapi :
 *   GET   /          → form (key, queries, language, countries, toggle)
 *   POST  /          → save settings (Botble setting() store)
 *   POST  /test      → call NewsAPI /everything once and report
 *                      total + first 5 titles back to the editor
 *   POST  /run       → trigger an immediate fetch (artisan call)
 *
 * Settings persisted (grimba_newsapi_ prefix):
 *   grimba_newsapi_key
 *   grimba_newsapi_queries           — newline- or comma-separated
 *   grimba_newsapi_language
 *   grimba_newsapi_countries         — csv
 *   grimba_newsapi_categories        — csv, NewsAPI top-headlines categories
 *   grimba_newsapi_active            — bool
 *   grimba_newsapi_everything_window_hours — int
 */

use App\Services\GrimbaNewsApiFetcher;
use App\Support\GrimbaIngestGuardrails;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Blog\Models\Post;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

if (! defined('GRIMBA_NEWSAPI_DEFAULT_COUNTRIES')) {
    define('GRIMBA_NEWSAPI_DEFAULT_COUNTRIES', 'fr,us,gb,ca,ng,za,eg,ma,au,in,ae,il,br,mx');
}

if (! defined('GRIMBA_WEBZ_DEFAULT_QUERIES')) {
    define('GRIMBA_WEBZ_DEFAULT_QUERIES', implode("\n", [
        '(breaking OR "breaking news" OR urgent OR alerte)',
        '(africa OR afrique OR sahel OR mali OR senegal OR nigeria OR sudan OR kenya OR congo OR cameroon)',
        'topic:"financial and economic news" (africa OR afrique OR economy OR économie)',
    ]));
}

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('newsapi', function (GrimbaNewsApiFetcher $fetcher) {
            $key       = (string) setting('grimba_newsapi_key', env('NEWSAPI_KEY', ''));
            $queries   = (string) setting('grimba_newsapi_queries', "macron OR retraites OR énergie OR climat OR ukraine OR israël");
            $language  = (string) setting('grimba_newsapi_language', 'fr');
            $countries = (string) setting('grimba_newsapi_countries', GRIMBA_NEWSAPI_DEFAULT_COUNTRIES);
            $categories = (string) setting('grimba_newsapi_categories', 'business,entertainment,general,health,science,sports,technology');
            $active    = (bool) setting('grimba_newsapi_active', $fetcher->isConfigured());
            $window    = (int) setting('grimba_newsapi_everything_window_hours', 48);
            $dailyBudget = $fetcher->dailyRequestBudget();
            $maxCallsPerRun = $fetcher->maxCallsPerRun();
            $plannedCalls = $fetcher->plannedCallCount();
            $callsToday = $fetcher->callsToday();
            $webzKey = (string) setting('grimba_webz_key', env('WEBZ_NEWS_API_LITE_TOKEN', env('WEBZ_API_KEY', '')));
            $webzQueries = (string) setting('grimba_webz_queries', GRIMBA_WEBZ_DEFAULT_QUERIES);
            $webzActive = (bool) setting('grimba_webz_active', trim($webzKey) !== '');
            $webzDailyBudget = max(1, min(1000, (int) setting('grimba_webz_daily_request_budget', 30)));
            $webzMonthlyBudget = max(1, min(1000, (int) setting('grimba_webz_monthly_request_budget', 900)));
            $webzMaxCallsPerRun = max(1, min(10, (int) setting('grimba_webz_max_calls_per_run', 1)));
            $recentRuns = collect();
            $recentLiveRuns = collect();
            $newsApiStats = [
                'calls_today' => $callsToday,
                'daily_budget' => $dailyBudget,
                'planned_calls' => $plannedCalls,
                'max_calls_per_run' => $maxCallsPerRun,
                'budget_pct' => min(100, (int) round($callsToday * 100 / max(1, $dailyBudget))),
                'ingested_24h' => 0,
                'deduped_24h' => 0,
                'returned_24h' => 0,
                'failed_24h' => 0,
            ];
            $liveProviderStats = [
                'webz_calls_today' => 0,
                'webz_calls_month' => 0,
                'webz_daily_budget' => $webzDailyBudget,
                'webz_monthly_budget' => $webzMonthlyBudget,
                'webz_ingested_24h' => 0,
                'webz_deduped_24h' => 0,
                'webz_failed_24h' => 0,
            ];
            if (Schema::hasTable('grimba_newsapi_runs')) {
                $recentRuns = DB::table('grimba_newsapi_runs')
                    ->orderByDesc('started_at')
                    ->limit(12)
                    ->get();
                $since = now()->subDay();
                $newsApiStats['ingested_24h'] = (int) DB::table('grimba_newsapi_runs')->where('started_at', '>=', $since)->sum('ingested_articles');
                $newsApiStats['deduped_24h'] = (int) DB::table('grimba_newsapi_runs')->where('started_at', '>=', $since)->sum('deduped_articles');
                $newsApiStats['returned_24h'] = (int) DB::table('grimba_newsapi_runs')->where('started_at', '>=', $since)->sum('returned_articles');
                $newsApiStats['failed_24h'] = (int) DB::table('grimba_newsapi_runs')->where('started_at', '>=', $since)->where('status', 'failed')->count();
            }
            if (Schema::hasTable('grimba_live_news_provider_runs')) {
                $recentLiveRuns = DB::table('grimba_live_news_provider_runs')
                    ->orderByDesc('started_at')
                    ->limit(12)
                    ->get();
                $since = now()->subDay();
                $liveProviderStats['webz_calls_today'] = (int) DB::table('grimba_live_news_provider_runs')
                    ->where('provider', 'webz')
                    ->where('status', '!=', 'skipped')
                    ->where('started_at', '>=', now()->startOfDay())
                    ->count();
                $liveProviderStats['webz_calls_month'] = (int) DB::table('grimba_live_news_provider_runs')
                    ->where('provider', 'webz')
                    ->where('status', '!=', 'skipped')
                    ->where('started_at', '>=', now()->startOfMonth())
                    ->count();
                $liveProviderStats['webz_ingested_24h'] = (int) DB::table('grimba_live_news_provider_runs')->where('provider', 'webz')->where('started_at', '>=', $since)->sum('ingested_articles');
                $liveProviderStats['webz_deduped_24h'] = (int) DB::table('grimba_live_news_provider_runs')->where('provider', 'webz')->where('started_at', '>=', $since)->sum('deduped_articles');
                $liveProviderStats['webz_failed_24h'] = (int) DB::table('grimba_live_news_provider_runs')->where('provider', 'webz')->where('started_at', '>=', $since)->where('status', 'failed')->count();
            }
            $newsApiDrafts = Post::query()
                ->whereIn('id', function ($sub): void {
                    $sub->select('post_id')
                        ->from('newsapi_items')
                        ->whereNotNull('post_id');
                })
                ->where('status', 'draft')
                ->orderByDesc('id')
                ->limit(10)
                ->get();
            $guardrailStats = GrimbaIngestGuardrails::tally(Post::query()
                ->whereIn('id', function ($sub): void {
                    $sub->select('post_id')
                        ->from('newsapi_items')
                        ->whereNotNull('post_id');
                })
                ->where('status', 'draft')
                ->get());

            return view('grimba-admin.newsapi.index', compact(
                'key', 'queries', 'language', 'countries', 'categories', 'active', 'window',
                'dailyBudget', 'maxCallsPerRun', 'newsApiStats', 'recentRuns', 'recentLiveRuns',
                'webzKey', 'webzQueries', 'webzActive', 'webzDailyBudget', 'webzMonthlyBudget',
                'webzMaxCallsPerRun', 'liveProviderStats',
                'newsApiDrafts', 'guardrailStats'
            ));
        })->name('newsapi.index');

        Route::post('newsapi', function (Request $request) {
            /** @var SettingStore $store */
            $store = app(SettingStore::class);

            $store->set('grimba_newsapi_key',       (string) $request->input('key', ''));
            $store->set('grimba_newsapi_queries',   (string) $request->input('queries', ''));
            $store->set('grimba_newsapi_language',  (string) $request->input('language', 'fr'));
            $store->set('grimba_newsapi_countries', (string) $request->input('countries', GRIMBA_NEWSAPI_DEFAULT_COUNTRIES));
            $store->set('grimba_newsapi_categories', (string) $request->input('categories', 'business,entertainment,general,health,science,sports,technology'));
            $store->set('grimba_newsapi_active',    (bool)   $request->input('active', false));
            $store->set('grimba_newsapi_everything_window_hours',
                (int) max(24, min(720, (int) $request->input('window', 48))));
            $store->set('grimba_newsapi_daily_request_budget',
                (int) max(1, min(100000, (int) $request->input('daily_budget', 900))));
            $store->set('grimba_newsapi_max_calls_per_run',
                (int) max(1, min(200, (int) $request->input('max_calls_per_run', 120))));
            $store->set('grimba_webz_key', (string) $request->input('webz_key', ''));
            $store->set('grimba_webz_active', (bool) $request->input('webz_active', false));
            $store->set('grimba_webz_queries', (string) $request->input('webz_queries', GRIMBA_WEBZ_DEFAULT_QUERIES));
            $store->set('grimba_webz_daily_request_budget',
                (int) max(1, min(1000, (int) $request->input('webz_daily_budget', 30))));
            $store->set('grimba_webz_monthly_request_budget',
                (int) max(1, min(1000, (int) $request->input('webz_monthly_budget', 900))));
            $store->set('grimba_webz_max_calls_per_run',
                (int) max(1, min(10, (int) $request->input('webz_max_calls_per_run', 1))));
            $store->save();

            return redirect()
                ->route('grimba.newsapi.index')
                ->with('success_msg', 'Réglages fournisseurs enregistrés.');
        })->name('newsapi.save');

        Route::post('newsapi/test', function (Request $request) {
            $fetcher = app(GrimbaNewsApiFetcher::class);
            if (! $fetcher->isConfigured()) {
                return response()->json(['ok' => false, 'error' => 'Clé NewsAPI absente.'], 422);
            }

            // Single low-cost test call: top-headlines US, pageSize 1.
            $res = Http::withUserAgent('GrimbaNewsBot/1.0 (+https://grimbanews.com/bot)')
                ->withHeaders(['X-Api-Key' => $fetcher->key()])
                ->timeout(10)
                ->get('https://newsapi.org/v2/top-headlines', [
                    'country'  => 'us',
                    'pageSize' => 5,
                ]);

            if (! $res->successful()) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'HTTP ' . $res->status() . ' — ' . ($res->json('message') ?? ''),
                ], 422);
            }

            $body = $res->json();
            $samples = array_map(
                fn ($a) => [
                    'source' => $a['source']['name'] ?? '?',
                    'title'  => \Illuminate\Support\Str::limit((string) ($a['title'] ?? ''), 100, '…'),
                ],
                array_slice((array) ($body['articles'] ?? []), 0, 5)
            );

            return response()->json([
                'ok'           => true,
                'totalResults' => (int) ($body['totalResults'] ?? 0),
                'samples'      => $samples,
            ]);
        })->name('newsapi.test');

        Route::post('newsapi/run', function () {
            // Synchronous artisan call. Slow on a tight admin page —
            // bounded by the 4 standard queries × ~5s each = ~20s
            // worst case. For a longer fleet, switch to dispatch().
            try {
                $exitCode = Artisan::call('grimba:fetch-newsapi');
                $out = Artisan::output();
            } catch (\Throwable $e) {
                return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
            }

            return response()->json([
                'ok'       => $exitCode === 0,
                'exitCode' => $exitCode,
                'output'   => $out,
            ]);
        })->name('newsapi.run');

        Route::post('newsapi/run-live', function () {
            try {
                $exitCode = Artisan::call('grimba:fetch-breaking', [
                    '--provider' => ['webz'],
                ]);
                $out = Artisan::output();
            } catch (\Throwable $e) {
                return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
            }

            return response()->json([
                'ok' => $exitCode === 0,
                'exitCode' => $exitCode,
                'output' => $out,
            ]);
        })->name('newsapi.run-live');

        Route::post('newsapi/publish-drafts', function (Request $request) {
            $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
            if ($ids === []) {
                return back()->with('success_msg', 'Aucun brouillon NewsAPI sélectionné.');
            }

            $result = grimba_newsapi_publish_posts($ids);
            $message = "{$result['published']} brouillon(s) NewsAPI publié(s).";
            if ($result['blocked'] > 0) {
                $message .= " {$result['blocked']} bloqué(s) par les garde-fous: " . implode(', ', $result['reasons']) . '.';
            }

            return back()->with('success_msg', $message);
        })->name('newsapi.publish-drafts');
    });

if (! function_exists('grimba_newsapi_draft_guardrails')) {
    /**
     * @return array<int, string>
     */
    function grimba_newsapi_draft_guardrails(Post $post): array
    {
        return GrimbaIngestGuardrails::flags($post);
    }
}

if (! function_exists('grimba_newsapi_publish_posts')) {
    /**
     * @return array{published:int, blocked:int, reasons:array<int, string>}
     */
    function grimba_newsapi_publish_posts(array $ids): array
    {
        return GrimbaIngestGuardrails::publishDrafts($ids, function ($query) {
            return $query->whereIn('id', function ($sub): void {
                $sub->select('post_id')->from('newsapi_items')->whereNotNull('post_id');
            });
        });
    }
}

DashboardMenu::default()->beforeRetrieving(function (): void {
    DashboardMenu::make()->registerItem(
        DashboardMenuItem::make()
            ->id('grimba-newsapi')
            ->priority(60)
            ->parentId('grimba-root')
            ->name('News providers')
            ->icon('ti ti-bolt')
            ->route('grimba.newsapi.index')
    );
});
