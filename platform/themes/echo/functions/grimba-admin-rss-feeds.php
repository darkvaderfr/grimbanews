<?php

/*
 * GrimbaNews — admin CRUD + health dashboard for rss_feeds.
 *
 * Routes under /admin/grimba/rss-feeds :
 *   GET     /                         → list with search + health badges
 *   GET     /create                   → form (new)
 *   POST    /                         → store
 *   GET     /{id}/edit                → form (edit)
 *   PUT     /{id}                     → update
 *   DELETE  /{id}                     → destroy
 *   POST    /{id}/toggle              → flip is_active
 *   POST    /{id}/poll-now            → poll a single feed immediately
 *   POST    /poll-all                 → poll every active feed now
 *
 * All writes go through Validator + DB::table — consistent with the
 * rest of the grimba-admin pages (news-sources, story-clusters).
 */

use App\Services\GrimbaRssPoller;
use App\Support\GrimbaRssFeedHealth;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('rss-feeds', function (Request $request) {
            $q = trim((string) $request->input('q', ''));

            $query = DB::table('rss_feeds')
                ->leftJoin('news_sources', 'news_sources.id', '=', 'rss_feeds.source_id')
                ->select([
                    'rss_feeds.*',
                    'news_sources.name as source_name',
                    'news_sources.bias_rating as source_bias',
                ]);

            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('news_sources.name', 'like', "%{$q}%")
                      ->orWhere('rss_feeds.url', 'like', "%{$q}%");
                });
            }

            $feeds = $query->orderBy('news_sources.name')->paginate(30)->withQueryString();
            $feeds->getCollection()->transform(function ($feed) {
                $feed->health_score = GrimbaRssFeedHealth::score($feed);
                $feed->health_label = GrimbaRssFeedHealth::label($feed);
                $feed->health_color = GrimbaRssFeedHealth::color($feed);
                $feed->is_stale = GrimbaRssFeedHealth::isStale($feed);
                $feed->stale_reason = GrimbaRssFeedHealth::staleReason($feed);

                return $feed;
            });

            $activeFeeds = DB::table('rss_feeds')->where('is_active', true)->get();
            $staleCount = $activeFeeds
                ->filter(fn ($feed) => GrimbaRssFeedHealth::isStale($feed))
                ->count();
            $averageHealth = $activeFeeds->isEmpty()
                ? 0
                : (int) round($activeFeeds->avg(fn ($feed) => GrimbaRssFeedHealth::score($feed)));

            $stats = [
                'total'          => DB::table('rss_feeds')->count(),
                'active'         => $activeFeeds->count(),
                'sick'           => $activeFeeds->filter(fn ($feed) => GrimbaRssFeedHealth::isSick($feed))->count(),
                'stale'          => $staleCount,
                'average_health' => $averageHealth,
                'last_success'   => DB::table('rss_feeds')->where('is_active', true)->max('last_success_at'),
                'ingested'       => (int) DB::table('rss_feeds')->sum('items_ingested'),
            ];

            return view('grimba-admin.rss-feeds.index', compact('feeds', 'q', 'stats'));
        })->name('rss-feeds.index');

        Route::get('rss-feeds/create', function () {
            $sources = DB::table('news_sources')->orderBy('name')->get(['id', 'name']);
            return view('grimba-admin.rss-feeds.form', ['feed' => null, 'sources' => $sources]);
        })->name('rss-feeds.create');

        Route::get('rss-feeds/{id}/edit', function (int $id) {
            $feed = DB::table('rss_feeds')->where('id', $id)->first();
            abort_if(! $feed, 404);
            $sources = DB::table('news_sources')->orderBy('name')->get(['id', 'name']);
            return view('grimba-admin.rss-feeds.form', compact('feed', 'sources'));
        })->name('rss-feeds.edit');

        Route::post('rss-feeds', function (Request $request) {
            $data = Validator::make($request->all(), grimba_rss_feed_rules())->validate();
            $data['created_at'] = now();
            $data['updated_at'] = now();
            $data['is_active']  = (bool) ($data['is_active'] ?? true);

            $id = DB::table('rss_feeds')->insertGetId($data);

            return redirect()
                ->route('grimba.rss-feeds.edit', $id)
                ->with('success_msg', 'Flux ajouté.');
        })->name('rss-feeds.store');

        Route::put('rss-feeds/{id}', function (Request $request, int $id) {
            $exists = DB::table('rss_feeds')->where('id', $id)->exists();
            abort_if(! $exists, 404);

            $data = Validator::make($request->all(), grimba_rss_feed_rules($id))->validate();
            $data['updated_at'] = now();
            $data['is_active']  = (bool) ($data['is_active'] ?? false);

            DB::table('rss_feeds')->where('id', $id)->update($data);

            return redirect()
                ->route('grimba.rss-feeds.edit', $id)
                ->with('success_msg', 'Flux mis à jour.');
        })->name('rss-feeds.update');

        Route::delete('rss-feeds/{id}', function (int $id) {
            DB::table('rss_feed_items')->where('feed_id', $id)->delete();
            DB::table('rss_feeds')->where('id', $id)->delete();

            return redirect()
                ->route('grimba.rss-feeds.index')
                ->with('success_msg', 'Flux supprimé.');
        })->name('rss-feeds.destroy');

        Route::post('rss-feeds/{id}/toggle', function (int $id) {
            $feed = DB::table('rss_feeds')->where('id', $id)->first();
            abort_if(! $feed, 404);

            DB::table('rss_feeds')->where('id', $id)->update([
                'is_active'  => ! $feed->is_active,
                'updated_at' => now(),
            ]);

            $msg = $feed->is_active ? 'Flux désactivé.' : 'Flux réactivé.';
            return back()->with('success_msg', $msg);
        })->name('rss-feeds.toggle');

        Route::post('rss-feeds/{id}/poll-now', function (int $id, GrimbaRssPoller $poller) {
            $feed = DB::table('rss_feeds')
                ->leftJoin('news_sources', 'news_sources.id', '=', 'rss_feeds.source_id')
                ->where('rss_feeds.id', $id)
                ->first([
                    'rss_feeds.*',
                    'news_sources.name as source_name',
                    'news_sources.bias_rating as source_bias',
                ]);
            abort_if(! $feed, 404);

            $result = $poller->pollOne($feed);

            if ($result['status'] === 'ok') {
                $msg = sprintf('%s : %d nouveau(x) brouillon(s).', $result['source_name'], $result['ingested']);
            } else {
                $msg = sprintf('Échec sur %s : %s',
                    $result['source_name'],
                    Str::limit((string) $result['error'], 120)
                );
            }

            return back()->with('success_msg', $msg);
        })->name('rss-feeds.poll-now');

        Route::post('rss-feeds/poll-all', function (GrimbaRssPoller $poller) {
            $summary  = $poller->pollAll();
            $ok       = count(array_filter($summary, fn ($s) => $s['status'] === 'ok'));
            $ingested = array_sum(array_column($summary, 'ingested'));
            $failed   = count($summary) - $ok;

            $msg = sprintf(
                '%d flux polled — %d OK, %d échec(s), %d nouveau(x) brouillon(s).',
                count($summary),
                $ok,
                $failed,
                $ingested
            );
            return back()->with('success_msg', $msg);
        })->name('rss-feeds.poll-all');
    });

if (! function_exists('grimba_rss_feed_rules')) {
    function grimba_rss_feed_rules(?int $ignoreId = null): array
    {
        // Allow duplicates across different source_ids but not within one.
        // Combined unique is enforced at DB level via migration too.
        $unique = 'unique:rss_feeds,url,NULL,id,source_id,' . request()->input('source_id', 'NULL');
        if ($ignoreId) {
            $unique = 'unique:rss_feeds,url,' . $ignoreId . ',id,source_id,' . request()->input('source_id', 'NULL');
        }

        return [
            'source_id'   => ['required', 'integer', 'exists:news_sources,id'],
            'url'         => ['required', 'url:http,https', 'max:500', $unique],
            'feed_format' => ['required', 'in:rss,atom'],
            'is_active'   => ['nullable', 'boolean'],
            'notes'       => ['nullable', 'string'],
        ];
    }
}

// Dashboard menu hook — adds "Flux RSS" under GrimbaNews.
app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()
            ->registerItem(
                DashboardMenuItem::make()
                    ->id('grimba-rss-feeds')
                    ->priority(16)
                    ->parentId('grimba-root')
                    ->name('Flux RSS')
                    ->icon('ti ti-rss')
                    ->route('grimba.rss-feeds.index')
            );
    });
});
