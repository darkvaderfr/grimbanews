<?php

/*
 * GrimbaNews — admin CRUD for story_clusters.
 *
 * Pragmatic page at /admin/grimba/story-clusters that lets editors
 * create clusters, label them, attach/detach posts, and see the
 * L/C/R bias spread of each dossier before the public /comparatif
 * page loads.
 */

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
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

            return view('grimba-admin.story-clusters.index', compact('clusters'));
        })->name('story-clusters.index');

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

            $available = Post::query()
                ->where(function ($q) use ($id) {
                    $q->whereNull('story_cluster_id')
                      ->orWhere('story_cluster_id', '!=', $id);
                })
                ->where('status', 'published')
                ->orderBy('name')
                ->get();

            return view('grimba-admin.story-clusters.form', compact('cluster', 'attached', 'available'));
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
    });
});
