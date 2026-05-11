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
use App\Services\GrimbaNobuAi;
use App\Services\GrimbaTranslator;
use App\Support\GrimbaAutomationMonitor;
use App\Support\GrimbaIngestGuardrails;
use App\Support\GrimbaPostRecency;
use Botble\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('cockpit', function () {
            // Today's coverage balance across all published posts today.
            $today = now()->startOfDay();
            $todayPosts = DB::table('posts')
                ->where('status', 'published')
                ->tap(fn ($q) => GrimbaPostRecency::wherePublishedSince($q, $today))
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
                ->tap(fn ($q) => GrimbaPostRecency::wherePublishedSince($q, $weekAgo))
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

            // Stats headers + operational pressure.
            $publishedToday = $todayPosts->count();
            $draftCount = DB::table('posts')->where('status', '!=', 'published')->count();
            $publishedTotal = DB::table('posts')->where('status', 'published')->count();
            $clusterCount = DB::table('story_clusters')->count();
            $activeClusterCount = DB::table('posts')
                ->where('status', 'published')
                ->whereNotNull('story_cluster_id')
                ->distinct('story_cluster_id')
                ->count('story_cluster_id');
            $translationReady = DB::table('posts')
                ->where('status', 'published')
                ->where('original_language', '!=', 'fr')
                ->where('translated_to', 'fr')
                ->whereNotNull('translated_name')
                ->count();
            $translationPending = DB::table('posts')
                ->where('status', 'published')
                ->where('original_language', '!=', 'fr')
                ->where(function ($q): void {
                    $q->whereNull('translated_to')
                        ->orWhere('translated_to', '!=', 'fr')
                        ->orWhereNull('translated_name');
                })
                ->count();

            $rssActive = Schema::hasTable('rss_feeds')
                ? DB::table('rss_feeds')->where('is_active', true)->count()
                : 0;
            $rssSick = Schema::hasTable('rss_feeds')
                ? DB::table('rss_feeds')->where('is_active', true)->where('consecutive_failures', '>=', 5)->count()
                : 0;
            $rssLastPoll = Schema::hasTable('rss_feeds')
                ? DB::table('rss_feeds')->whereNotNull('last_polled_at')->max('last_polled_at')
                : null;
            $rssItems24 = Schema::hasTable('rss_feed_items')
                ? DB::table('rss_feed_items')->where('seen_at', '>=', now()->subDay())->count()
                : 0;

            $newsApiActive = (bool) setting('grimba_newsapi_active', true);
            $newsApiConfigured = trim((string) setting('grimba_newsapi_key', env('NEWSAPI_KEY', ''))) !== '';
            $newsApiItems24 = Schema::hasTable('newsapi_items')
                ? DB::table('newsapi_items')->where('fetched_at', '>=', now()->subDay())->count()
                : 0;
            $newsApiLastFetch = Schema::hasTable('newsapi_items')
                ? DB::table('newsapi_items')->whereNotNull('fetched_at')->max('fetched_at')
                : null;

            $duplicateGroups = DB::table('posts')
                ->whereNotNull('name')
                ->select('name', 'source_id', DB::raw('COUNT(*) as c'))
                ->groupBy('name', 'source_id')
                ->having('c', '>', 1)
                ->count();

            $englishTranslationPending = 0;
            if (Schema::hasTable('grimba_post_translations')) {
                $englishTranslationPending = DB::table('posts')
                    ->where('status', 'published')
                    ->whereNotNull('original_language')
                    ->where('original_language', '!=', 'en')
                    ->whereNotExists(function ($q): void {
                        $q->selectRaw('1')
                            ->from('grimba_post_translations')
                            ->whereColumn('grimba_post_translations.post_id', 'posts.id')
                            ->where('grimba_post_translations.locale', 'en')
                            ->whereNotNull('grimba_post_translations.translated_name');
                    })
                    ->count();
            }

            $latestDrafts = DB::table('posts')
                ->where('status', '!=', 'published')
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(['id', 'name', 'source_name', 'updated_at', 'bias_rating']);

            $ingestGuardrailPosts = Post::query()
                ->where('status', 'draft')
                ->where(function ($query): void {
                    $query
                        ->whereIn('id', function ($sub): void {
                            $sub->select('post_id')->from('rss_feed_items')->whereNotNull('post_id');
                        })
                        ->orWhereIn('id', function ($sub): void {
                            $sub->select('post_id')->from('newsapi_items')->whereNotNull('post_id');
                        });
                })
                ->get();
            $ingestGuardrailStats = GrimbaIngestGuardrails::tally($ingestGuardrailPosts);

            $nobuAi = app(GrimbaNobuAi::class);
            $translator = app(GrimbaTranslator::class);
            $nobuDrivers = $nobuAi->configuredDrivers();
            $translationDrivers = $translator->configuredDrivers();
            $nobuFailureDiagnostics = $nobuAi->failureDiagnostics($nobuDrivers);
            $nobuInsightReady = 0;
            $nobuInsightPending = 0;
            $nobuInsightStale = 0;
            $nobuInsightLatest = null;
            $automationStatus = GrimbaAutomationMonitor::status();

            if (Schema::hasColumn('posts', 'summary_nobuai')) {
                $insightClusters = DB::table('posts')
                    ->where('status', 'published')
                    ->whereNotNull('story_cluster_id')
                    ->selectRaw("
                        story_cluster_id,
                        COUNT(*) as post_count,
                        MAX(CASE WHEN summary_nobuai IS NOT NULL AND summary_nobuai != '' THEN 1 ELSE 0 END) as has_summary,
                        MAX(summary_generated_at) as summary_generated_at,
                        MAX(updated_at) as latest_post_at
                    ")
                    ->groupBy('story_cluster_id')
                    ->havingRaw('COUNT(*) >= 2')
                    ->get();

                $nobuInsightReady = $insightClusters->where('has_summary', 1)->count();
                $nobuInsightPending = $insightClusters->count() - $nobuInsightReady;
                $nobuInsightStale = $insightClusters
                    ->filter(fn ($cluster) => (int) $cluster->has_summary === 1
                        && $cluster->summary_generated_at
                        && $cluster->latest_post_at
                        && \Carbon\Carbon::parse($cluster->latest_post_at)->gt(\Carbon\Carbon::parse($cluster->summary_generated_at)))
                    ->count();
                $nobuInsightLatest = DB::table('posts')
                    ->whereNotNull('summary_generated_at')
                    ->max('summary_generated_at');
            }

            return view('grimba-admin.cockpit', compact(
                'coverage', 'coverageTotal',
                'activeClusters',
                'topSources',
                'sparkline', 'signupsTotal',
                'blindspotCount',
                'publishedToday', 'draftCount',
                'publishedTotal', 'clusterCount', 'activeClusterCount',
                'translationReady', 'translationPending',
                'rssActive', 'rssSick', 'rssLastPoll', 'rssItems24',
                'newsApiActive', 'newsApiConfigured', 'newsApiItems24', 'newsApiLastFetch',
                'duplicateGroups', 'englishTranslationPending',
                'ingestGuardrailStats',
                'latestDrafts', 'nobuDrivers', 'translationDrivers',
                'nobuFailureDiagnostics',
                'nobuInsightReady', 'nobuInsightPending', 'nobuInsightStale', 'nobuInsightLatest',
                'automationStatus'
            ));
        })->name('cockpit');

        Route::post('cockpit/nobuai-summaries', function (Request $request) {
            $limit = min(5, max(1, (int) $request->input('limit', 3)));
            $staleOnly = $request->boolean('stale_only');

            $args = [
                '--limit' => $limit,
            ];

            if ($staleOnly) {
                $args['--stale'] = true;
            }

            $exitCode = Artisan::call('grimba:nobuai-summaries', $args);

            $output = trim(Artisan::output());
            $summary = collect(preg_split("/\r\n|\n|\r/", $output) ?: [])
                ->map(fn (string $line): string => trim(strip_tags($line)))
                ->filter()
                ->take(-4)
                ->implode(' ');

            $prefix = $staleOnly ? 'NobuAI stale refresh' : 'NobuAI';
            $message = $summary !== ''
                ? $prefix . ': ' . $summary
                : $prefix . ': génération terminée.';

            return redirect()
                ->route('grimba.cockpit')
                ->with($exitCode === 0 ? 'success_msg' : 'error_msg', $message);
        })->name('cockpit.nobuai-summaries');

        Route::post('cockpit/runbook', function (Request $request) {
            $action = (string) $request->input('action', 'health');
            $limit = min(5, max(1, (int) $request->input('limit', 3)));

            $actions = [
                'health' => [
                    'label' => 'Health',
                    'command' => 'grimba:health',
                    'args' => [],
                ],
                'rss_poll_one' => [
                    'label' => 'RSS poll',
                    'command' => 'grimba:poll-feeds',
                    'args' => [],
                ],
                'nobuai_health' => [
                    'label' => 'NobuAI health',
                    'command' => 'grimba:nobuai-health',
                    'args' => [],
                ],
                'translate_fr' => [
                    'label' => 'Translate to FR',
                    'command' => 'grimba:translate-pending',
                    'args' => ['--to' => 'fr', '--limit' => $limit],
                ],
                'translate_en' => [
                    'label' => 'Translate to EN',
                    'command' => 'grimba:translate-pending',
                    'args' => ['--to' => 'en', '--limit' => $limit],
                ],
                'newsapi_fetch' => [
                    'label' => 'NewsAPI fetch',
                    'command' => 'grimba:fetch-newsapi',
                    'args' => [],
                ],
                'category_reclassify' => [
                    'label' => 'Category reclassify',
                    'command' => 'grimba:classify-categories',
                    'args' => [],
                ],
            ];

            if (! isset($actions[$action])) {
                return redirect()
                    ->route('grimba.cockpit')
                    ->with('error_msg', 'Runbook: action inconnue.');
            }

            $selected = $actions[$action];
            if ($action === 'rss_poll_one') {
                $feedId = Schema::hasTable('rss_feeds')
                    ? DB::table('rss_feeds')
                        ->where('is_active', true)
                        ->orderByRaw('last_polled_at IS NULL DESC')
                        ->orderBy('last_polled_at')
                        ->value('id')
                    : null;

                if (! $feedId) {
                    return redirect()
                        ->route('grimba.cockpit')
                        ->with('error_msg', 'RSS poll: aucun flux actif.');
                }

                $selected['args'] = ['--feed' => (int) $feedId];
            }

            if ($action === 'category_reclassify') {
                $categoryId = (int) $request->input('category_id');
                if ($categoryId < 1) {
                    return redirect()
                        ->route('grimba.cockpit')
                        ->with('error_msg', 'Category reclassify: category_id manquant.');
                }

                $selected['args'] = ['--category' => $categoryId];
            }

            $exitCode = Artisan::call($selected['command'], $selected['args']);
            $output = trim(Artisan::output());
            $summary = collect(preg_split("/\r\n|\n|\r/", $output) ?: [])
                ->map(fn (string $line): string => trim(preg_replace('/\e\[[0-9;]*m/u', '', strip_tags($line)) ?? ''))
                ->filter()
                ->take(-5)
                ->implode(' ');

            $message = $selected['label'] . ': ' . ($summary !== '' ? $summary : 'commande terminée.');

            return redirect()
                ->route('grimba.cockpit')
                ->with($exitCode === 0 ? 'success_msg' : 'error_msg', $message);
        })->name('cockpit.runbook');
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
