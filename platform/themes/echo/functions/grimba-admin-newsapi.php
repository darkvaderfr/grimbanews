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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('newsapi', function () {
            $key       = (string) setting('grimba_newsapi_key', env('NEWSAPI_KEY', ''));
            $queries   = (string) setting('grimba_newsapi_queries', "macron OR retraites OR énergie OR climat OR ukraine OR israël");
            $language  = (string) setting('grimba_newsapi_language', 'fr');
            $countries = (string) setting('grimba_newsapi_countries', 'fr,us,gb,ca');
            $active    = (bool) setting('grimba_newsapi_active', true);
            $window    = (int) setting('grimba_newsapi_everything_window_hours', 48);
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
                'key', 'queries', 'language', 'countries', 'active', 'window', 'newsApiDrafts', 'guardrailStats'
            ));
        })->name('newsapi.index');

        Route::post('newsapi', function (Request $request) {
            /** @var SettingStore $store */
            $store = app(SettingStore::class);

            $store->set('grimba_newsapi_key',       (string) $request->input('key', ''));
            $store->set('grimba_newsapi_queries',   (string) $request->input('queries', ''));
            $store->set('grimba_newsapi_language',  (string) $request->input('language', 'fr'));
            $store->set('grimba_newsapi_countries', (string) $request->input('countries', 'fr,us,gb,ca'));
            $store->set('grimba_newsapi_active',    (bool)   $request->input('active', false));
            $store->set('grimba_newsapi_everything_window_hours',
                (int) max(24, min(720, (int) $request->input('window', 48))));
            $store->save();

            return redirect()
                ->route('grimba.newsapi.index')
                ->with('success_msg', 'Réglages NewsAPI enregistrés.');
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
            ->name('NewsAPI')
            ->icon('ti ti-bolt')
            ->route('grimba.newsapi.index')
    );
});
