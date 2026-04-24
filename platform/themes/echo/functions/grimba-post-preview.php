<?php

/*
 * GrimbaNews — live source/cluster preview card on the post editor.
 *
 * Registers two small admin-side JSON endpoints that the preview JS
 * hits when the editor changes the Source or Dossier dropdown, plus
 * the JS+CSS injection itself on the post create/edit pages only.
 *
 * The <div id="grimba-post-preview"> slot is added by
 * grimba-post-form.php as an HtmlField between the grimba fields
 * and the rest of the form; this file owns the behavior and styling.
 */

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// ---------- Admin JSON endpoints ----------

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba/api/preview')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.api.preview.')
    ->group(function (): void {

        Route::get('source/{id}', function (int $id): JsonResponse {
            $source = DB::table('news_sources')
                ->where('id', $id)
                ->first(['id', 'name', 'website', 'bias_rating', 'ownership_type', 'credibility_score', 'country', 'language']);

            if (! $source) {
                return response()->json(['found' => false], 404);
            }

            return response()->json([
                'found'             => true,
                'id'                => $source->id,
                'name'              => $source->name,
                'website'           => $source->website,
                'bias_rating'       => $source->bias_rating,
                'bias_label'        => grimba_bias_label_fr($source->bias_rating),
                'ownership_type'    => $source->ownership_type,
                'ownership_label'   => grimba_ownership_label_fr($source->ownership_type),
                'credibility_score' => $source->credibility_score,
                'country'           => $source->country,
                'language'          => $source->language,
            ]);
        })->name('source');

        Route::get('cluster/{id}', function (int $id): JsonResponse {
            $cluster = DB::table('story_clusters')->where('id', $id)->first();
            if (! $cluster) {
                return response()->json(['found' => false], 404);
            }

            $rows = DB::table('posts')
                ->where('story_cluster_id', $id)
                ->where('status', 'published')
                ->get(['id', 'name', 'bias_rating', 'source_name', 'created_at']);

            $counts = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
            foreach ($rows as $r) {
                $k = $r->bias_rating ?: 'unknown';
                if (! isset($counts[$k])) $k = 'unknown';
                $counts[$k]++;
            }

            $latest = $rows->sortByDesc('created_at')->first();

            return response()->json([
                'found'       => true,
                'id'          => $cluster->id,
                'topic'       => $cluster->topic,
                'description' => $cluster->description,
                'total'       => $rows->count(),
                'counts'      => $counts,
                'latest'      => $latest ? [
                    'id'          => $latest->id,
                    'name'        => $latest->name,
                    'bias_rating' => $latest->bias_rating,
                    'source_name' => $latest->source_name,
                ] : null,
            ]);
        })->name('cluster');
    });

// ---------- Asset injection (post create + edit pages only) ----------

app()->booted(function (): void {
    if (! class_exists(Assets::class)) {
        return;
    }

    $request = request();
    if (! $request) {
        return;
    }

    $adminPrefix = BaseHelper::getAdminPrefix();

    // Match both /admin/blog/posts/create and /admin/blog/posts/edit/{id}
    $isPostEditor = $request->is($adminPrefix . '/blog/posts/create')
        || $request->is($adminPrefix . '/blog/posts/edit/*');

    if (! $isPostEditor) {
        return;
    }

    Assets::addStylesDirectly(['/themes/echo/css/grimba-post-preview.css']);
    Assets::addScriptsDirectly(['/themes/echo/js/grimba-post-preview.js']);
});

// ---------- Small helpers (avoid collision with other grimba_* helpers) ----------

if (! function_exists('grimba_bias_label_fr')) {
    function grimba_bias_label_fr(?string $bias): string
    {
        return match ($bias) {
            'left'   => 'Gauche',
            'center' => 'Centre',
            'right'  => 'Droite',
            default  => 'Non classé',
        };
    }
}

if (! function_exists('grimba_ownership_label_fr')) {
    function grimba_ownership_label_fr(?string $ownership): string
    {
        return match ($ownership) {
            'public'      => 'Public',
            'private'     => 'Privé',
            'independent' => 'Indépendant',
            'ngo'         => 'ONG',
            'cooperative' => 'Coopérative',
            null, ''      => 'Inconnu',
            default       => ucfirst($ownership),
        };
    }
}
