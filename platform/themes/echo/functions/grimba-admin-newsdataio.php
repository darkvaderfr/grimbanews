<?php

/*
 * Vader 2026-05-16 — S-NDI-11/12/13/14 admin settings page for the
 * newsdata.io ingest pipeline.
 *
 * Routes under /admin/grimba/newsdataio :
 *   GET   /          → form (key, queries, languages, countries, categories, toggles)
 *   POST  /          → save settings (Botble setting() store)
 *   POST  /test      → call newsdata.io /latest once and report first 5
 *                       titles + remaining credits back to the editor
 *   POST  /run       → trigger an immediate fetch (artisan call)
 *
 * Settings persisted (grimba_newsdata_io_ prefix):
 *   grimba_newsdata_io_key, grimba_newsdata_io_active,
 *   grimba_newsdata_io_daily_credit_budget, grimba_newsdata_io_max_calls_per_run,
 *   grimba_newsdata_io_queries, grimba_newsdata_io_languages,
 *   grimba_newsdata_io_countries, grimba_newsdata_io_categories,
 *   grimba_newsdata_io_timeout, grimba_newsdata_io_connect_timeout,
 *   grimba_newsdata_io_dedicated_cron, grimba_newsdata_io_page_size.
 */

use App\Services\GrimbaNewsdataIoFetcher;
use App\Support\GrimbaProviderCredits;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Setting\Supports\SettingStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('newsdataio', function (GrimbaNewsdataIoFetcher $fetcher) {
            $stats = [
                'used'   => $fetcher->creditsUsedToday(),
                'budget' => $fetcher->dailyCreditBudget(),
                'remaining' => $fetcher->creditsRemainingToday(),
            ];
            $stats['pct'] = (int) round($stats['used'] * 100 / max(1, $stats['budget']));

            $recentRuns = collect();
            if (Schema::hasTable('grimba_live_news_provider_runs')) {
                $recentRuns = DB::table('grimba_live_news_provider_runs')
                    ->where('provider', GrimbaNewsdataIoFetcher::PROVIDER)
                    ->orderByDesc('started_at')
                    ->limit(12)
                    ->get();
            }

            return view('grimba-admin.newsdataio.index', [
                'pageTitle'   => 'newsdata.io',
                'active'      => $fetcher->isActive(),
                'configured'  => $fetcher->isConfigured(),
                'apiKey'      => $fetcher->apiKey(),
                'budget'      => $fetcher->dailyCreditBudget(),
                'maxCalls'    => $fetcher->maxCallsPerRun(),
                'queries'     => (string) setting('grimba_newsdata_io_queries', ''),
                'languages'   => implode(',', $fetcher->languages()),
                'countries'   => implode(',', $fetcher->countries()),
                'categories'  => implode(',', $fetcher->categories()),
                'timeout'     => $fetcher->timeoutSeconds(),
                'connectTimeout' => $fetcher->connectTimeoutSeconds(),
                'pageSize'    => $fetcher->pageSize(),
                'dedicatedCron' => (bool) setting('grimba_newsdata_io_dedicated_cron', false),
                'stats'       => $stats,
                'recentRuns'  => $recentRuns,
            ]);
        })->name('newsdataio.index');

        Route::post('newsdataio', function (Request $request, SettingStore $settings) {
            // Validate + clamp inline. Architect plan §4.3.
            $key       = trim((string) $request->input('key', ''));
            $active    = $request->boolean('active');
            $budget    = max(1, min(200, (int) $request->input('daily_credit_budget', 190)));
            $maxCalls  = max(1, min(6, (int) $request->input('max_calls_per_run', 2)));
            $pageSize  = max(1, min(10, (int) $request->input('page_size', 10)));
            $queries   = trim((string) $request->input('queries', ''));
            $languages = $__csvClamp((string) $request->input('languages', 'fr,en'), 5);
            $countries = $__csvClamp((string) $request->input('countries', 'fr,sn,ci,ml,cm'), 5);
            $categories = $__csvClamp((string) $request->input('categories', 'top,politics,world'), 8);
            $timeout   = max(2, min(60, (int) $request->input('timeout', 12)));
            $connectTimeout = max(1, min(30, (int) $request->input('connect_timeout', 5)));
            $dedicatedCron = $request->boolean('dedicated_cron');

            // Balanced-parens sanity on q strings (architect §4.3).
            foreach (preg_split('/\r\n|\r|\n/', $queries) as $line) {
                $line = trim($line);
                if ($line === '') continue;
                if (substr_count($line, '(') !== substr_count($line, ')')) {
                    return redirect()->back()->withErrors([
                        'queries' => 'A query line has unbalanced parentheses: ' . $line,
                    ])->withInput();
                }
            }

            $settings
                ->set('grimba_newsdata_io_key', $key)
                ->set('grimba_newsdata_io_active', $active ? '1' : '')
                ->set('grimba_newsdata_io_daily_credit_budget', (string) $budget)
                ->set('grimba_newsdata_io_max_calls_per_run', (string) $maxCalls)
                ->set('grimba_newsdata_io_page_size', (string) $pageSize)
                ->set('grimba_newsdata_io_queries', $queries)
                ->set('grimba_newsdata_io_languages', $languages)
                ->set('grimba_newsdata_io_countries', $countries)
                ->set('grimba_newsdata_io_categories', $categories)
                ->set('grimba_newsdata_io_timeout', (string) $timeout)
                ->set('grimba_newsdata_io_connect_timeout', (string) $connectTimeout)
                ->set('grimba_newsdata_io_dedicated_cron', $dedicatedCron ? '1' : '')
                ->save();

            return redirect()->route('grimba.newsdataio.index')->with('status', 'newsdata.io settings saved.');
        })->name('newsdataio.save');

        Route::post('newsdataio/test', function (GrimbaNewsdataIoFetcher $fetcher) {
            if (! $fetcher->isConfigured()) {
                return redirect()->route('grimba.newsdataio.index')->with('test_result', [
                    'ok' => false,
                    'message' => 'API key is not configured.',
                ]);
            }

            $queries = $fetcher->queries();
            $query = $queries[0] ?? 'breaking news';

            $started = microtime(true);
            $result = $fetcher->fetchQuery($query);
            $elapsed = round((microtime(true) - $started) * 1000);

            return redirect()->route('grimba.newsdataio.index')->with('test_result', [
                'ok'        => $result['status'] !== 'failed',
                'status'    => $result['status'],
                'query'     => $query,
                'returned'  => $result['returned'],
                'ingested'  => $result['ingested'],
                'deduped'   => $result['deduped'],
                'error'     => $result['error'],
                'elapsed_ms' => $elapsed,
                'remaining' => $fetcher->creditsRemainingToday(),
            ]);
        })->name('newsdataio.test');

        Route::post('newsdataio/run', function () {
            try {
                Artisan::call('grimba:fetch-breaking', ['--provider' => 'newsdata-io']);
                $output = Artisan::output();
            } catch (\Throwable $e) {
                $output = 'Run failed: ' . $e->getMessage();
            }

            return redirect()->route('grimba.newsdataio.index')->with('run_output', $output);
        })->name('newsdataio.run');
    });

DashboardMenu::default()->beforeRetrieving(function (): void {
    DashboardMenu::make()->registerItem(
        DashboardMenuItem::make()
            ->id('grimba-newsdataio')
            ->priority(62)
            ->parentId('grimba-root')
            ->name('newsdata.io')
            ->icon('ti ti-wifi-2')
            ->route('grimba.newsdataio.index')
    );
});

if (! function_exists('__csvClamp')) {
    function __csvClamp(string $raw, int $max): string {
        $list = collect(explode(',', $raw))
            ->map(fn (string $v): string => strtolower(trim($v)))
            ->filter()
            ->unique()
            ->take($max)
            ->values()
            ->all();

        return implode(',', $list);
    }
}
