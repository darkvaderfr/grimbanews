<?php

/*
 * GrimbaNews — privacy-preserving vault analytics dashboard.
 *
 * Aggregates vault_events only by event, post_id, timestamp, and salted
 * ip_hash. No account id, raw IP, or user-agent is stored or displayed.
 */

use App\Support\GrimbaVaultEvents;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {
        Route::get('vault-analytics', function (Request $request) {
            $weekInput = trim((string) $request->query('week', ''));
            try {
                $weekStart = $weekInput !== ''
                    ? Carbon::parse($weekInput)->startOfWeek()
                    : now()->startOfWeek();
            } catch (Throwable) {
                $weekStart = now()->startOfWeek();
            }
            $weekEnd = $weekStart->copy()->endOfWeek();

            $stats = [
                'saves' => 0,
                'unsaves' => 0,
                'return_visits' => 0,
                'unique_savers' => 0,
                'converted_returners' => 0,
                'conversion_rate' => 0,
            ];
            $topPosts = collect();
            $dailyRows = collect(range(0, 6))->map(function (int $offset) use ($weekStart) {
                $date = $weekStart->copy()->addDays($offset);

                return (object) [
                    'date' => $date->toDateString(),
                    'label' => $date->isoFormat('ddd D'),
                    'saves' => 0,
                    'return_visits' => 0,
                ];
            });

            if (Schema::hasTable(GrimbaVaultEvents::TABLE)) {
                $range = function ($query) use ($weekStart, $weekEnd): void {
                    $query->whereBetween('ts', [$weekStart, $weekEnd]);
                };

                $stats['saves'] = (int) DB::table(GrimbaVaultEvents::TABLE)
                    ->where('event', GrimbaVaultEvents::EVENT_SAVE)
                    ->where($range)
                    ->count();
                $stats['unsaves'] = (int) DB::table(GrimbaVaultEvents::TABLE)
                    ->where('event', GrimbaVaultEvents::EVENT_UNSAVE)
                    ->where($range)
                    ->count();
                $stats['return_visits'] = (int) DB::table(GrimbaVaultEvents::TABLE)
                    ->where('event', GrimbaVaultEvents::EVENT_RETURN_VISIT)
                    ->where($range)
                    ->count();

                $firstSaves = DB::table(GrimbaVaultEvents::TABLE)
                    ->select('ip_hash', DB::raw('MIN(ts) as first_save_at'))
                    ->where('event', GrimbaVaultEvents::EVENT_SAVE)
                    ->where($range)
                    ->groupBy('ip_hash')
                    ->pluck('first_save_at', 'ip_hash');
                $firstReturns = DB::table(GrimbaVaultEvents::TABLE)
                    ->select('ip_hash', DB::raw('MIN(ts) as first_return_at'))
                    ->where('event', GrimbaVaultEvents::EVENT_RETURN_VISIT)
                    ->where($range)
                    ->groupBy('ip_hash')
                    ->pluck('first_return_at', 'ip_hash');

                $stats['unique_savers'] = $firstSaves->count();
                $stats['converted_returners'] = $firstSaves
                    ->filter(fn ($firstSaveAt, string $hash): bool => $firstReturns->has($hash)
                        && Carbon::parse($firstReturns[$hash])->gte(Carbon::parse($firstSaveAt)))
                    ->count();
                $stats['conversion_rate'] = $stats['unique_savers'] > 0
                    ? (int) round($stats['converted_returners'] * 100 / $stats['unique_savers'])
                    : 0;

                $topPosts = DB::table(GrimbaVaultEvents::TABLE . ' as events')
                    ->leftJoin('posts', 'posts.id', '=', 'events.post_id')
                    ->where('events.event', GrimbaVaultEvents::EVENT_SAVE)
                    ->where('events.post_id', '>', 0)
                    ->whereBetween('events.ts', [$weekStart, $weekEnd])
                    ->select([
                        'events.post_id',
                        'posts.name',
                        'posts.source_name',
                    ])
                    ->selectRaw('COUNT(*) as saves')
                    ->selectRaw('COUNT(DISTINCT events.ip_hash) as unique_savers')
                    ->selectRaw('MAX(events.ts) as latest_save_at')
                    ->groupBy('events.post_id', 'posts.name', 'posts.source_name')
                    ->orderByDesc('saves')
                    ->orderByDesc('unique_savers')
                    ->limit(20)
                    ->get();

                $savesByDay = DB::table(GrimbaVaultEvents::TABLE)
                    ->selectRaw('DATE(ts) as day, COUNT(*) as total')
                    ->where('event', GrimbaVaultEvents::EVENT_SAVE)
                    ->where($range)
                    ->groupBy('day')
                    ->pluck('total', 'day');
                $returnsByDay = DB::table(GrimbaVaultEvents::TABLE)
                    ->selectRaw('DATE(ts) as day, COUNT(*) as total')
                    ->where('event', GrimbaVaultEvents::EVENT_RETURN_VISIT)
                    ->where($range)
                    ->groupBy('day')
                    ->pluck('total', 'day');

                $dailyRows = $dailyRows->map(function ($row) use ($savesByDay, $returnsByDay) {
                    $row->saves = (int) ($savesByDay[$row->date] ?? 0);
                    $row->return_visits = (int) ($returnsByDay[$row->date] ?? 0);

                    return $row;
                });
            }

            $maxDaily = max(1, (int) $dailyRows->max(fn ($row) => max($row->saves, $row->return_visits)));
            $previousWeek = $weekStart->copy()->subWeek()->toDateString();
            $nextWeek = $weekStart->copy()->addWeek()->toDateString();

            return view('grimba-admin.vault-analytics.index', compact(
                'stats',
                'topPosts',
                'dailyRows',
                'maxDaily',
                'weekStart',
                'weekEnd',
                'previousWeek',
                'nextWeek'
            ));
        })->name('vault-analytics.index');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-vault-analytics')
                ->priority(35)
                ->parentId('grimba-root')
                ->name('Analytics coffre')
                ->icon('ti ti-chart-bar')
                ->route('grimba.vault-analytics.index')
        );
    });
});
