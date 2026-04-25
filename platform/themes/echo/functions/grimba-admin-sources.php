<?php

/*
 * GrimbaNews — admin CRUD for news_sources.
 *
 * Registers a pragmatic admin UI at /admin/grimba/news-sources behind
 * Botble's admin auth. Not a full Botble plugin — a focused page
 * backed by raw DB calls — but it unblocks editors from having to
 * use tinker to add/edit source rows.
 */

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('news-sources', function (Request $request) {
            $q = trim((string) $request->input('q', ''));

            $query = DB::table('news_sources');
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('website', 'like', "%{$q}%")
                      ->orWhere('country', 'like', "%{$q}%");
                });
            }
            $sources = $query->orderBy('name')->paginate(30)->withQueryString();

            return view('grimba-admin.news-sources.index', compact('sources', 'q'));
        })->name('news-sources.index');

        // S133 — unknown-bias triage queue. NewsAPI auto-creates
        // source rows for outlets not in its catalog (BFMTV,
        // Lalibre.be, etc.); they land here marked unknown for
        // editor classification.
        Route::get('news-sources/triage', function () {
            $rows = DB::table('news_sources')
                ->where('bias_rating', 'unknown')
                ->orderByDesc('updated_at')
                ->get();

            // Hydrate per-row article counts + a sample headline.
            $ids = $rows->pluck('id')->all();
            $counts = collect();
            $samples = collect();
            if (! empty($ids)) {
                $counts = DB::table('posts')
                    ->whereIn('source_id', $ids)
                    ->select('source_id', DB::raw('COUNT(*) as c'))
                    ->groupBy('source_id')
                    ->pluck('c', 'source_id');

                $samples = DB::table('posts')
                    ->whereIn('source_id', $ids)
                    ->orderByDesc('id')
                    ->get(['id', 'name', 'source_id'])
                    ->groupBy('source_id')
                    ->map(fn ($g) => $g->take(2)->pluck('name')->all());
            }

            return view('grimba-admin.news-sources.triage', [
                'rows'    => $rows,
                'counts'  => $counts,
                'samples' => $samples,
            ]);
        })->name('news-sources.triage');

        Route::post('news-sources/{id}/quick-classify', function (Request $request, int $id) {
            $exists = DB::table('news_sources')->where('id', $id)->exists();
            abort_if(! $exists, 404);

            $data = Validator::make($request->all(), [
                'bias_rating'       => 'required|in:left,center,right,unknown',
                'ownership_type'    => 'nullable|string|max:64',
                'credibility_score' => 'nullable|integer|min:0|max:100',
                'country'           => 'nullable|string|max:8',
                'language'          => 'nullable|string|max:8',
            ])->validate();

            $data['updated_at'] = now();
            DB::table('news_sources')->where('id', $id)->update($data);

            return response()->json(['ok' => true]);
        })->name('news-sources.quick-classify');

        Route::get('news-sources/create', function () {
            return view('grimba-admin.news-sources.form', [
                'source' => null,
            ]);
        })->name('news-sources.create');

        Route::get('news-sources/{id}/edit', function (int $id) {
            $source = DB::table('news_sources')->where('id', $id)->first();
            abort_if(! $source, 404);

            return view('grimba-admin.news-sources.form', compact('source'));
        })->name('news-sources.edit');

        Route::post('news-sources', function (Request $request) {
            $data = Validator::make($request->all(), grimba_news_source_rules())->validate();
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table('news_sources')->insert($data);

            return redirect()
                ->route('grimba.news-sources.index')
                ->with('success_msg', "Source « {$data['name']} » créée.");
        })->name('news-sources.store');

        Route::put('news-sources/{id}', function (Request $request, int $id) {
            $exists = DB::table('news_sources')->where('id', $id)->exists();
            abort_if(! $exists, 404);

            $data = Validator::make(
                $request->all(),
                grimba_news_source_rules($id)
            )->validate();
            $data['updated_at'] = now();

            DB::table('news_sources')->where('id', $id)->update($data);

            return redirect()
                ->route('grimba.news-sources.edit', $id)
                ->with('success_msg', "Source « {$data['name']} » mise à jour.");
        })->name('news-sources.update');

        Route::delete('news-sources/{id}', function (int $id) {
            $name = DB::table('news_sources')->where('id', $id)->value('name');
            DB::table('news_sources')->where('id', $id)->delete();

            return redirect()
                ->route('grimba.news-sources.index')
                ->with('success_msg', "Source « {$name} » supprimée.");
        })->name('news-sources.destroy');
    });

if (! function_exists('grimba_news_source_rules')) {
    function grimba_news_source_rules(?int $ignoreId = null): array
    {
        $unique = 'unique:news_sources,name';
        if ($ignoreId) {
            $unique .= ',' . $ignoreId;
        }

        return [
            'name'              => ['required', 'string', 'max:120', $unique],
            'website'           => ['nullable', 'string', 'max:255'],
            'bias_rating'       => ['required', 'in:left,center,right,unknown'],
            'ownership_type'    => ['nullable', 'in:state,corporate,independent,nonprofit'],
            'credibility_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'country'           => ['nullable', 'string', 'max:3'],
            'language'          => ['nullable', 'string', 'max:5'],
            'notes'             => ['nullable', 'string'],
        ];
    }
}

// Dashboard menu hook.
app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()
            ->registerItem(
                DashboardMenuItem::make()
                    ->id('grimba-root')
                    ->priority(4)
                    ->name('GrimbaNews')
                    ->icon('ti ti-news')
            )
            ->registerItem(
                DashboardMenuItem::make()
                    ->id('grimba-news-sources')
                    ->priority(10)
                    ->parentId('grimba-root')
                    ->name('Sources')
                    ->icon('ti ti-building-broadcast-tower')
                    ->route('grimba.news-sources.index')
            );
    });
});
