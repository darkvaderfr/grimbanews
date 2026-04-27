<?php

/*
 * GrimbaNews — review / batch publish RSS-ingested drafts.
 *
 * Companion to S71 (the poller) and S72 (feed CRUD). The poller lands
 * every feed item as status=draft so a human has to approve before
 * the reader sees it. With ~80 drafts/day across the 11 active feeds,
 * the editor needs a real queue view — Botble's stock /admin/blog/
 * posts scales poorly for this workflow.
 *
 * Routes under /admin/grimba/rss-drafts :
 *   GET     /                          → queue with source/bias filters + pagination
 *   POST    /publish                   → bulk publish (ids[])
 *   POST    /delete                    → bulk delete (ids[])
 *   POST    /{id}/publish              → single publish
 *
 * Dedup invariant: deletes set rss_feed_items.post_id = NULL so the
 * ledger still blocks the poller from re-ingesting an item an editor
 * already rejected.
 */

use App\Support\GrimbaIngestGuardrails;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Blog\Models\Post;
use Botble\Slug\Models\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('rss-drafts', function (Request $request) {
            $sourceId = (int) $request->input('source', 0) ?: null;
            $bias     = in_array($request->input('bias'), ['left', 'center', 'right', 'unknown'], true)
                ? $request->input('bias')
                : null;

            $query = Post::query()
                ->whereIn('id', function ($sub) {
                    $sub->select('post_id')
                        ->from('rss_feed_items')
                        ->whereNotNull('post_id');
                })
                ->where('status', 'draft')
                ->orderByDesc('id');

            if ($sourceId) $query->where('source_id', $sourceId);
            if ($bias)     $query->where('bias_rating', $bias);

            $drafts  = $query->paginate(25)->withQueryString();
            $sources = DB::table('news_sources')->orderBy('name')->get(['id', 'name']);

            $stats = [
                'total_queue' => Post::query()
                    ->whereIn('id', function ($sub) {
                        $sub->select('post_id')->from('rss_feed_items')->whereNotNull('post_id');
                    })
                    ->where('status', 'draft')
                    ->count(),
                'total_published' => Post::query()->where('status', 'published')->count(),
            ];

            return view('grimba-admin.rss-drafts.index', compact(
                'drafts', 'sources', 'sourceId', 'bias', 'stats'
            ));
        })->name('rss-drafts.index');

        Route::post('rss-drafts/publish', function (Request $request) {
            $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
            if (empty($ids)) {
                return back()->with('success_msg', 'Aucun brouillon sélectionné.');
            }

            $result = grimba_publish_posts($ids);
            $message = "{$result['published']} brouillon(s) publié(s).";

            if ($result['blocked'] > 0) {
                $message .= " {$result['blocked']} bloqué(s) par les garde-fous: " . implode(', ', array_unique($result['reasons'])) . '.';
            }

            return back()->with(
                'success_msg',
                $message
            );
        })->name('rss-drafts.publish');

        Route::post('rss-drafts/delete', function (Request $request) {
            $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
            if (empty($ids)) {
                return back()->with('success_msg', 'Aucun brouillon sélectionné.');
            }

            $count = grimba_delete_posts($ids);
            return back()->with('success_msg', "{$count} brouillon(s) supprimé(s).");
        })->name('rss-drafts.delete');

        Route::post('rss-drafts/{id}/publish', function (int $id) {
            $result = grimba_publish_posts([$id]);
            $msg = $result['published'] > 0
                ? 'Publié.'
                : ($result['blocked'] > 0
                    ? 'Publication bloquée: ' . implode(', ', array_unique($result['reasons'])) . '.'
                    : 'Aucune action (article introuvable ou déjà publié).');
            return back()->with('success_msg', $msg);
        })->name('rss-drafts.publish-one');
    });

if (! function_exists('grimba_rss_draft_guardrails')) {
    /**
     * @return array<int, string>
     */
    function grimba_rss_draft_guardrails(Post $post): array
    {
        return GrimbaIngestGuardrails::flags($post);
    }
}

if (! function_exists('grimba_publish_posts')) {
    /**
     * @return array{published:int, blocked:int, reasons:array<int, string>}
     */
    function grimba_publish_posts(array $ids): array
    {
        return GrimbaIngestGuardrails::publishDrafts($ids, function ($query) {
            return $query->whereIn('id', function ($sub): void {
                $sub->select('post_id')
                    ->from('rss_feed_items')
                    ->whereNotNull('post_id');
            });
        });
    }
}

if (! function_exists('grimba_delete_posts')) {
    function grimba_delete_posts(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $post = Post::query()->where('id', $id)->where('status', 'draft')->first();
            if (! $post) continue;

            // Preserve the ledger entry so the poller's dedup still
            // works — we just null the pointer so a later "undelete"
            // isn't on the table but a re-ingest of the same guid
            // remains blocked.
            DB::table('rss_feed_items')->where('post_id', $id)->update(['post_id' => null]);

            Slug::where('reference_id', $id)
                ->where('reference_type', Post::class)
                ->delete();

            $post->delete();
            $count++;
        }
        return $count;
    }
}

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()
            ->registerItem(
                DashboardMenuItem::make()
                    ->id('grimba-rss-drafts')
                    ->priority(17)
                    ->parentId('grimba-root')
                    ->name('File RSS (brouillons)')
                    ->icon('ti ti-inbox')
                    ->route('grimba.rss-drafts.index')
            );
    });
});
