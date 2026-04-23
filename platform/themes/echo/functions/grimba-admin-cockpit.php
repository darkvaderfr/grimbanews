<?php

/*
 * GrimbaNews — admin editorial cockpit.
 *
 * Ships a new route /admin/grimba/cockpit that renders an
 * editorial dashboard (coverage balance today, active dossiers,
 * top sources, newsletter trend, angles morts count).
 *
 * Mythos P2 / S55 in the backend redesign plan.
 */

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('cockpit', function () {
            // Today's coverage balance across all published posts today.
            $today = now()->startOfDay();
            $todayPosts = DB::table('posts')
                ->where('status', 'published')
                ->where('created_at', '>=', $today)
                ->get(['bias_rating', 'source_name', 'story_cluster_id']);

            $coverage = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
            foreach ($todayPosts as $p) {
                $r = $p->bias_rating ?? 'unknown';
                if (isset($coverage[$r])) $coverage[$r]++;
            }
            $coverageTotal = array_sum($coverage);

            // Active dossiers — top 5 clusters by post count, with bias spread.
            $activeClusters = DB::table('story_clusters')
                ->select('story_clusters.id', 'story_clusters.topic')
                ->selectRaw('COUNT(posts.id) as post_count')
                ->leftJoin('posts', function ($j) {
                    $j->on('posts.story_cluster_id', '=', 'story_clusters.id')
                      ->where('posts.status', 'published');
                })
                ->groupBy('story_clusters.id', 'story_clusters.topic')
                ->orderByDesc('post_count')
                ->limit(5)
                ->get()
                ->map(function ($c) {
                    $spread = DB::table('posts')
                        ->where('story_cluster_id', $c->id)
                        ->where('status', 'published')
                        ->selectRaw('bias_rating, COUNT(*) as n')
                        ->groupBy('bias_rating')
                        ->pluck('n', 'bias_rating');
                    $c->spread = [
                        'left'   => (int) ($spread['left']   ?? 0),
                        'center' => (int) ($spread['center'] ?? 0),
                        'right'  => (int) ($spread['right']  ?? 0),
                    ];
                    return $c;
                })
                ->filter(fn ($c) => $c->post_count > 0)
                ->values();

            // Top sources last 7 days.
            $weekAgo = now()->subDays(7);
            $topSources = DB::table('posts')
                ->selectRaw('source_name, COUNT(*) as n, MAX(credibility_score) as score')
                ->where('status', 'published')
                ->where('created_at', '>=', $weekAgo)
                ->whereNotNull('source_name')
                ->groupBy('source_name')
                ->orderByDesc('n')
                ->limit(6)
                ->get();

            // Newsletter signups last 7 days, per day.
            $signups = DB::table('newsletter_subscriptions')
                ->selectRaw("DATE(created_at) as d, COUNT(*) as n")
                ->where('created_at', '>=', $weekAgo)
                ->groupBy('d')
                ->orderBy('d')
                ->pluck('n', 'd');

            $sparkline = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $sparkline[] = [
                    'date' => $date,
                    'n'    => (int) ($signups[$date] ?? 0),
                ];
            }
            $signupsTotal = array_sum(array_column($sparkline, 'n'));

            // Angles morts pending review.
            $blindspotCount = DB::table('posts')
                ->where('status', 'published')
                ->where('is_blindspot', true)
                ->count();

            // Stats headers.
            $publishedToday = $todayPosts->count();
            $draftCount = DB::table('posts')->where('status', '!=', 'published')->count();

            return view('grimba-admin.cockpit', compact(
                'coverage', 'coverageTotal',
                'activeClusters',
                'topSources',
                'sparkline', 'signupsTotal',
                'blindspotCount',
                'publishedToday', 'draftCount'
            ));
        })->name('cockpit');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-cockpit')
                ->priority(1)
                ->parentId('grimba-root')
                ->name('Tableau de bord')
                ->icon('ti ti-layout-dashboard')
                ->route('grimba.cockpit')
        );
    });
});
