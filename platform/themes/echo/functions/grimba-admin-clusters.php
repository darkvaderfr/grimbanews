<?php

/*
 * GrimbaNews — admin CRUD for story_clusters.
 *
 * Pragmatic page at /admin/grimba/story-clusters that lets editors
 * create clusters, label them, attach/detach posts, and see the
 * L/C/R bias spread of each dossier before the public /comparatif
 * page loads.
 */

use App\Services\GrimbaNobuAi;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('story-clusters', function () {
            $clusters = DB::table('story_clusters')
                ->orderByDesc('id')
                ->get()
                ->map(function ($c) {
                    $spread = DB::table('posts')
                        ->selectRaw('bias_rating, COUNT(*) as n')
                        ->where('story_cluster_id', $c->id)
                        ->where('status', 'published')
                        ->groupBy('bias_rating')
                        ->pluck('n', 'bias_rating');
                    $c->spread = [
                        'left'    => (int) ($spread['left']    ?? 0),
                        'center'  => (int) ($spread['center']  ?? 0),
                        'right'   => (int) ($spread['right']   ?? 0),
                        'unknown' => (int) ($spread['unknown'] ?? 0),
                    ];
                    $c->total = array_sum($c->spread);
                    return $c;
                });

            $coverageStats = [
                'balanced' => 0,
                'partial' => 0,
                'one_sided' => 0,
                'empty' => 0,
            ];

            foreach ($clusters as $cluster) {
                $activeSides = count(array_filter([
                    $cluster->spread['left'] ?? 0,
                    $cluster->spread['center'] ?? 0,
                    $cluster->spread['right'] ?? 0,
                ]));

                match ($activeSides) {
                    3 => $coverageStats['balanced']++,
                    2 => $coverageStats['partial']++,
                    1 => $coverageStats['one_sided']++,
                    default => $coverageStats['empty']++,
                };
            }

            return view('grimba-admin.story-clusters.index', compact('clusters', 'coverageStats'));
        })->name('story-clusters.index');

        Route::get('cluster-review', function () {
            $hasReviewFields = Schema::hasColumn('story_clusters', 'review_action')
                && Schema::hasColumn('story_clusters', 'reviewed_at');

            $select = [
                'clusters.id',
                'clusters.topic',
                'clusters.description',
                'clusters.updated_at',
            ];

            if ($hasReviewFields) {
                $select[] = 'clusters.review_action';
                $select[] = 'clusters.reviewed_at';
            }

            $rows = DB::table('story_clusters as clusters')
                ->leftJoin('posts', function ($join): void {
                    $join->on('posts.story_cluster_id', '=', 'clusters.id')
                        ->where('posts.status', '=', 'published');
                })
                ->select($select)
                ->selectRaw('COUNT(posts.id) as total')
                ->selectRaw("SUM(CASE WHEN posts.bias_rating = 'left' THEN 1 ELSE 0 END) as left_count")
                ->selectRaw("SUM(CASE WHEN posts.bias_rating = 'center' THEN 1 ELSE 0 END) as center_count")
                ->selectRaw("SUM(CASE WHEN posts.bias_rating = 'right' THEN 1 ELSE 0 END) as right_count")
                ->selectRaw("SUM(CASE WHEN posts.id IS NOT NULL AND (posts.bias_rating IS NULL OR posts.bias_rating NOT IN ('left', 'center', 'right')) THEN 1 ELSE 0 END) as unknown_count")
                ->selectRaw('MAX(posts.updated_at) as latest_article_at')
                ->groupBy($select)
                ->orderByDesc('latest_article_at')
                ->orderByDesc('clusters.id')
                ->get()
                ->map(function ($row) use ($hasReviewFields) {
                    $row->left_count = (int) $row->left_count;
                    $row->center_count = (int) $row->center_count;
                    $row->right_count = (int) $row->right_count;
                    $row->unknown_count = (int) $row->unknown_count;
                    $row->total = (int) $row->total;
                    $row->active_sides = count(array_filter([
                        $row->left_count,
                        $row->center_count,
                        $row->right_count,
                    ]));
                    $row->signal = null;
                    $row->signal_kind = null;
                    $row->priority_score = 0;
                    $row->review_action = $hasReviewFields ? $row->review_action : null;
                    $row->reviewed_at = $hasReviewFields ? $row->reviewed_at : null;

                    if ($row->active_sides === 1 && $row->total > 5) {
                        $row->signal = 'Unilatéral dense';
                        $row->signal_kind = 'merge';
                        $row->priority_score = 300 + min(99, $row->total);
                    } elseif ($row->active_sides >= 3 && $row->left_count < 2 && $row->center_count < 2 && $row->right_count < 2) {
                        $row->signal = 'Tripartite trop mince';
                        $row->signal_kind = 'split';
                        $row->priority_score = 200 + min(99, $row->total);
                    }

                    return $row;
                })
                ->filter(fn ($row): bool => $row->signal !== null)
                ->sortByDesc('priority_score')
                ->values();

            $stats = [
                'total' => $rows->count(),
                'one_sided' => $rows->where('signal_kind', 'merge')->count(),
                'thin_split' => $rows->where('signal_kind', 'split')->count(),
                'acted' => $rows->filter(fn ($row) => ! empty($row->review_action))->count(),
            ];

            return view('grimba-admin.cluster-review.index', compact('rows', 'stats', 'hasReviewFields'));
        })->name('cluster-review.index');

        Route::post('cluster-review/{id}/action', function (Request $request, int $id) {
            abort_unless(Schema::hasColumn('story_clusters', 'review_action')
                && Schema::hasColumn('story_clusters', 'reviewed_at'), 500);
            abort_unless(DB::table('story_clusters')->where('id', $id)->exists(), 404);

            $data = Validator::make($request->all(), [
                'action' => ['required', 'in:merge,split,approve'],
            ])->validate();

            DB::table('story_clusters')->where('id', $id)->update([
                'review_action' => $data['action'],
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

            $label = match ($data['action']) {
                'merge' => 'fusion demandée',
                'split' => 'scission demandée',
                default => 'approuvé',
            };

            return redirect()
                ->route('grimba.cluster-review.index')
                ->with('success_msg', "Décision enregistrée: {$label}.");
        })->name('cluster-review.action');

        Route::get('coverage-map', function (Request $request) {
            $filter = $request->query('filter', 'gaps');
            $allowedFilters = ['all', 'gaps', 'one-sided', 'missing-left', 'missing-center', 'missing-right', 'empty'];

            if (! in_array($filter, $allowedFilters, true)) {
                $filter = 'gaps';
            }

            $rows = DB::table('story_clusters as clusters')
                ->leftJoin('posts', function ($join): void {
                    $join->on('posts.story_cluster_id', '=', 'clusters.id')
                        ->where('posts.status', '=', 'published');
                })
                ->select([
                    'clusters.id',
                    'clusters.topic',
                    'clusters.description',
                    'clusters.updated_at',
                ])
                ->selectRaw('COUNT(posts.id) as total')
                ->selectRaw("SUM(CASE WHEN posts.bias_rating = 'left' THEN 1 ELSE 0 END) as left_count")
                ->selectRaw("SUM(CASE WHEN posts.bias_rating = 'center' THEN 1 ELSE 0 END) as center_count")
                ->selectRaw("SUM(CASE WHEN posts.bias_rating = 'right' THEN 1 ELSE 0 END) as right_count")
                ->selectRaw("SUM(CASE WHEN posts.id IS NOT NULL AND (posts.bias_rating IS NULL OR posts.bias_rating NOT IN ('left', 'center', 'right')) THEN 1 ELSE 0 END) as unknown_count")
                ->selectRaw('MAX(posts.updated_at) as latest_article_at')
                ->groupBy('clusters.id', 'clusters.topic', 'clusters.description', 'clusters.updated_at')
                ->orderByDesc('latest_article_at')
                ->orderByDesc('clusters.id')
                ->get()
                ->map(function ($row) {
                    $row->left_count = (int) $row->left_count;
                    $row->center_count = (int) $row->center_count;
                    $row->right_count = (int) $row->right_count;
                    $row->unknown_count = (int) $row->unknown_count;
                    $row->total = (int) $row->total;

                    $row->active_sides = count(array_filter([
                        $row->left_count,
                        $row->center_count,
                        $row->right_count,
                    ]));

                    $row->missing = collect([
                        'left' => $row->left_count === 0,
                        'center' => $row->center_count === 0,
                        'right' => $row->right_count === 0,
                    ])->filter()->keys()->all();

                    $row->status = match ($row->active_sides) {
                        3 => 'balanced',
                        2 => 'partial',
                        1 => 'one-sided',
                        default => 'empty',
                    };
                    $row->coverage_score = match ($row->active_sides) {
                        3 => 100,
                        2 => 67,
                        1 => 33,
                        default => 0,
                    };
                    $row->priority_score = match ($row->status) {
                        'one-sided' => 300,
                        'partial' => 200,
                        'empty' => 100,
                        default => 0,
                    } + min(99, $row->total);

                    return $row;
                });

            $stats = [
                'total' => $rows->count(),
                'balanced' => $rows->where('status', 'balanced')->count(),
                'partial' => $rows->where('status', 'partial')->count(),
                'one_sided' => $rows->where('status', 'one-sided')->count(),
                'empty' => $rows->where('status', 'empty')->count(),
            ];
            $stats['completion_rate'] = $stats['total'] > 0
                ? (int) round($stats['balanced'] * 100 / $stats['total'])
                : 0;

            $rows = $rows
                ->filter(function ($row) use ($filter): bool {
                    return match ($filter) {
                        'all' => true,
                        'one-sided' => $row->status === 'one-sided',
                        'missing-left' => in_array('left', $row->missing, true) && $row->total > 0,
                        'missing-center' => in_array('center', $row->missing, true) && $row->total > 0,
                        'missing-right' => in_array('right', $row->missing, true) && $row->total > 0,
                        'empty' => $row->status === 'empty',
                        default => $row->status !== 'balanced',
                    };
                })
                ->sortByDesc('priority_score')
                ->values();

            return view('grimba-admin.coverage-map.index', compact('rows', 'stats', 'filter'));
        })->name('coverage-map.index');

        Route::get('story-clusters/create', function () {
            return view('grimba-admin.story-clusters.form', ['cluster' => null]);
        })->name('story-clusters.create');

        Route::get('story-clusters/{id}/edit', function (int $id) {
            $cluster = DB::table('story_clusters')->where('id', $id)->first();
            abort_if(! $cluster, 404);

            $attached = Post::query()
                ->where('story_cluster_id', $id)
                ->latest()
                ->get();

            $attachedSourceIds = $attached
                ->pluck('source_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $sourceColumns = array_values(array_filter([
                'id',
                'name',
                'website',
                'bias_rating',
                'ownership_type',
                'credibility_score',
                Schema::hasColumn('news_sources', 'owner_name') ? 'owner_name' : null,
                Schema::hasColumn('news_sources', 'slug') ? 'slug' : null,
            ]));

            $attachedSourceMeta = empty($attachedSourceIds)
                ? collect()
                : DB::table('news_sources')
                    ->whereIn('id', $attachedSourceIds)
                    ->get($sourceColumns)
                    ->keyBy('id');

            $available = Post::query()
                ->where(function ($q) use ($id) {
                    $q->whereNull('story_cluster_id')
                      ->orWhere('story_cluster_id', '!=', $id);
                })
                ->where('status', 'published')
                ->orderBy('name')
                ->get();

            $summaryInfo = null;
            $summaryIsStale = false;
            if (Schema::hasColumn('posts', 'summary_nobuai')) {
                $summaryInfo = DB::table('posts')
                    ->where('story_cluster_id', $id)
                    ->whereNotNull('summary_nobuai')
                    ->where('summary_nobuai', '!=', '')
                    ->orderByDesc('summary_generated_at')
                    ->first(['summary_nobuai', 'summary_generated_at', 'summary_driver']);

                if ($summaryInfo?->summary_generated_at) {
                    $latestArticleAt = DB::table('posts')
                        ->where('story_cluster_id', $id)
                        ->where('status', 'published')
                        ->max('updated_at');

                    $summaryIsStale = $latestArticleAt
                        && \Carbon\Carbon::parse($latestArticleAt)->gt(\Carbon\Carbon::parse($summaryInfo->summary_generated_at));
                }
            }

            $nobuAiReady = app(GrimbaNobuAi::class)->enabled();

            return view('grimba-admin.story-clusters.form', compact('cluster', 'attached', 'attachedSourceMeta', 'available', 'summaryInfo', 'summaryIsStale', 'nobuAiReady'));
        })->name('story-clusters.edit');

        Route::post('story-clusters', function (Request $request) {
            $data = Validator::make($request->all(), [
                'topic'       => ['required', 'string', 'max:200'],
                'description' => ['nullable', 'string'],
            ])->validate();
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $id = DB::table('story_clusters')->insertGetId($data);

            return redirect()
                ->route('grimba.story-clusters.edit', $id)
                ->with('success_msg', "Dossier « {$data['topic']} » créé.");
        })->name('story-clusters.store');

        Route::put('story-clusters/{id}', function (Request $request, int $id) {
            $exists = DB::table('story_clusters')->where('id', $id)->exists();
            abort_if(! $exists, 404);

            $data = Validator::make($request->all(), [
                'topic'       => ['required', 'string', 'max:200'],
                'description' => ['nullable', 'string'],
            ])->validate();
            $data['updated_at'] = now();

            DB::table('story_clusters')->where('id', $id)->update($data);

            return redirect()
                ->route('grimba.story-clusters.edit', $id)
                ->with('success_msg', "Dossier mis à jour.");
        })->name('story-clusters.update');

        Route::delete('story-clusters/{id}', function (int $id) {
            $topic = DB::table('story_clusters')->where('id', $id)->value('topic');
            // Detach posts first so we keep them.
            Post::query()->where('story_cluster_id', $id)->update(['story_cluster_id' => null]);
            DB::table('story_clusters')->where('id', $id)->delete();

            return redirect()
                ->route('grimba.story-clusters.index')
                ->with('success_msg', "Dossier « {$topic} » supprimé.");
        })->name('story-clusters.destroy');

        Route::post('story-clusters/{id}/attach', function (Request $request, int $id) {
            $postId = (int) $request->input('post_id');
            abort_if(! $postId, 422);
            Post::query()->where('id', $postId)->update(['story_cluster_id' => $id]);

            return redirect()
                ->route('grimba.story-clusters.edit', $id)
                ->with('success_msg', 'Article attaché au dossier.');
        })->name('story-clusters.attach');

        Route::post('story-clusters/{id}/detach', function (int $id, Request $request) {
            $postId = (int) $request->input('post_id');
            abort_if(! $postId, 422);
            Post::query()
                ->where('id', $postId)
                ->where('story_cluster_id', $id)
                ->update(['story_cluster_id' => null]);

            return redirect()
                ->route('grimba.story-clusters.edit', $id)
                ->with('success_msg', 'Article détaché.');
        })->name('story-clusters.detach');

        Route::post('story-clusters/{id}/nobuai-summary', function (int $id) {
            abort_unless(DB::table('story_clusters')->where('id', $id)->exists(), 404);

            $publishedCount = DB::table('posts')
                ->where('story_cluster_id', $id)
                ->where('status', 'published')
                ->count();

            if ($publishedCount < 2) {
                return redirect()
                    ->route('grimba.story-clusters.edit', $id)
                    ->with('success_msg', 'NobuAI : ajoutez au moins deux articles publiés avant de générer un insight.');
            }

            $exitCode = Artisan::call('grimba:nobuai-summaries', [
                '--cluster' => $id,
                '--limit' => 1,
                '--force' => true,
            ]);

            $output = trim(Artisan::output());
            $message = $exitCode === 0
                ? 'NobuAI insight généré pour ce dossier.'
                : 'NobuAI : génération échouée. Vérifiez les clés fournisseur.';

            if ($output !== '') {
                $message .= ' ' . \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', $output), 180);
            }

            return redirect()
                ->route('grimba.story-clusters.edit', $id)
                ->with('success_msg', $message);
        })->name('story-clusters.nobuai-summary');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-story-clusters')
                ->priority(20)
                ->parentId('grimba-root')
                ->name('Dossiers (clusters)')
                ->icon('ti ti-layout-collage')
                ->route('grimba.story-clusters.index')
        );

        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-coverage-map')
                ->priority(21)
                ->parentId('grimba-root')
                ->name('Carte couverture')
                ->icon('ti ti-radar')
                ->route('grimba.coverage-map.index')
        );

        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-cluster-review')
                ->priority(22)
                ->parentId('grimba-root')
                ->name('Revue dossiers')
                ->icon('ti ti-git-merge')
                ->route('grimba.cluster-review.index')
        );
    });
});
