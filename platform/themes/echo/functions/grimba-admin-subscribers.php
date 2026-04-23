<?php

/*
 * GrimbaNews — newsletter subscribers admin.
 *
 * List + search + toggle unsubscribe + CSV export for
 * newsletter_subscriptions, styled with the cinematic admin chrome.
 *
 * Mythos P5 / S60 in the backend redesign plan.
 */

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

Route::prefix(BaseHelper::getAdminPrefix() . '/grimba')
    ->middleware(['web', 'core', 'auth'])
    ->as('grimba.')
    ->group(function (): void {

        Route::get('subscribers', function (Request $request) {
            $q = trim((string) $request->input('q', ''));
            $active = $request->input('active');

            $query = DB::table('newsletter_subscriptions');
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('email', 'like', "%{$q}%")
                      ->orWhere('source_key', 'like', "%{$q}%")
                      ->orWhere('locale', 'like', "%{$q}%");
                });
            }
            if ($active === '1') $query->whereNull('unsubscribed_at');
            if ($active === '0') $query->whereNotNull('unsubscribed_at');

            $subs = $query->orderByDesc('created_at')->paginate(40)->withQueryString();

            $total       = DB::table('newsletter_subscriptions')->count();
            $activeCount = DB::table('newsletter_subscriptions')->whereNull('unsubscribed_at')->count();
            $unsubCount  = $total - $activeCount;
            $last7d      = DB::table('newsletter_subscriptions')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            return view('grimba-admin.subscribers.index', compact(
                'subs', 'q', 'active', 'total', 'activeCount', 'unsubCount', 'last7d'
            ));
        })->name('subscribers.index');

        Route::post('subscribers/{id}/toggle', function (int $id) {
            $row = DB::table('newsletter_subscriptions')->where('id', $id)->first();
            abort_if(! $row, 404);

            DB::table('newsletter_subscriptions')
                ->where('id', $id)
                ->update([
                    'unsubscribed_at' => $row->unsubscribed_at ? null : now(),
                    'updated_at'      => now(),
                ]);

            return back()->with('success_msg', 'Statut mis à jour.');
        })->name('subscribers.toggle');

        Route::delete('subscribers/{id}', function (int $id) {
            DB::table('newsletter_subscriptions')->where('id', $id)->delete();

            return back()->with('success_msg', 'Abonné supprimé.');
        })->name('subscribers.destroy');

        Route::get('subscribers/export.csv', function () {
            $response = new StreamedResponse(function () {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['email', 'locale', 'source_key', 'created_at', 'unsubscribed_at', 'ip_address']);
                DB::table('newsletter_subscriptions')
                    ->orderBy('created_at')
                    ->lazy()
                    ->each(function ($row) use ($out) {
                        fputcsv($out, [
                            $row->email,
                            $row->locale,
                            $row->source_key,
                            $row->created_at,
                            $row->unsubscribed_at,
                            $row->ip_address,
                        ]);
                    });
                fclose($out);
            });

            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="grimbanews-subscribers-' . now()->format('Ymd-His') . '.csv"');
            return $response;
        })->name('subscribers.export');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-subscribers')
                ->priority(30)
                ->parentId('grimba-root')
                ->name('Infolettre')
                ->icon('ti ti-mail')
                ->route('grimba.subscribers.index')
        );
    });
});
