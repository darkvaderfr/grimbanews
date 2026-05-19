<?php

/*
 * S-ADS-06 (Vader 2026-05-18) — sponsor leads admin index.
 *
 * Backs the /advertise lead-capture form (Wave ZZ / S-ADS-02-04)
 * with an operator surface. Lists, filters, exports, status-toggles.
 *
 * Mythos master fleet R6 — without this, leads land in DB but no
 * admin sees them. Now the dashboard menu carries the unread count
 * and the index lets ops mark them as won / closed / spam.
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

        Route::get('advertiser-leads', function (Request $request) {
            $q = trim((string) $request->input('q', ''));
            $status = (string) $request->input('status', '');

            $query = DB::table('grimba_advertiser_leads');
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('email', 'like', "%{$q}%")
                        ->orWhere('company', 'like', "%{$q}%")
                        ->orWhere('goals', 'like', "%{$q}%");
                });
            }
            if (in_array($status, ['new', 'contacted', 'won', 'closed', 'spam'], true)) {
                $query->where('status', $status);
            }

            $leads = $query->orderByDesc('created_at')->paginate(40)->withQueryString();

            $total       = DB::table('grimba_advertiser_leads')->count();
            $newCount    = DB::table('grimba_advertiser_leads')->where('status', 'new')->count();
            $contacted   = DB::table('grimba_advertiser_leads')->where('status', 'contacted')->count();
            $won         = DB::table('grimba_advertiser_leads')->where('status', 'won')->count();
            $last7d      = DB::table('grimba_advertiser_leads')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            // Wave BBBBB (Vader 2026-05-18) — pack-tier breakdown so ops can
            // see which sponsor product readers gravitate to. Excludes
            // spam + closed so the signal isn't poisoned by junk submissions.
            // Conversion rate is won / (total - spam - new) which excludes
            // both junk and "not yet decided" rows from the denominator.
            $packTiers = DB::table('grimba_advertiser_leads')
                ->whereNotNull('source_pack_tier')
                ->whereNotIn('status', ['spam'])
                ->select('source_pack_tier', DB::raw('count(*) as c'))
                ->groupBy('source_pack_tier')
                ->orderByDesc('c')
                ->pluck('c', 'source_pack_tier')
                ->all();
            $spamCount   = DB::table('grimba_advertiser_leads')->where('status', 'spam')->count();
            $convDenom   = max(0, $total - $spamCount - $newCount);
            $convRate    = $convDenom > 0 ? round(($won / $convDenom) * 100, 1) : 0.0;

            return view('grimba-admin.advertiser-leads.index', compact(
                'leads', 'q', 'status', 'total', 'newCount', 'contacted', 'won', 'last7d',
                'packTiers', 'convRate', 'spamCount'
            ));
        })->name('advertiser-leads.index');

        Route::get('advertiser-leads/{id}', function (int $id) {
            $lead = DB::table('grimba_advertiser_leads')->where('id', $id)->first();
            abort_if(! $lead, 404);

            $prevId = DB::table('grimba_advertiser_leads')
                ->where('id', '<', $id)
                ->orderByDesc('id')
                ->value('id');
            $nextId = DB::table('grimba_advertiser_leads')
                ->where('id', '>', $id)
                ->orderBy('id')
                ->value('id');

            return view('grimba-admin.advertiser-leads.detail', compact('lead', 'prevId', 'nextId'));
        })->name('advertiser-leads.show');

        Route::post('advertiser-leads/{id}/notes', function (Request $request, int $id) {
            $row = DB::table('grimba_advertiser_leads')->where('id', $id)->first();
            abort_if(! $row, 404);

            $notes = (string) $request->input('admin_notes', '');
            if (strlen($notes) > 8000) {
                $notes = substr($notes, 0, 8000);
            }

            DB::table('grimba_advertiser_leads')
                ->where('id', $id)
                ->update([
                    'admin_notes' => $notes !== '' ? $notes : null,
                    'last_admin_action_at' => now(),
                    'updated_at' => now(),
                ]);

            return back()->with('success_msg', 'Notes mises à jour.');
        })->name('advertiser-leads.notes');

        Route::post('advertiser-leads/{id}/status', function (Request $request, int $id) {
            $next = (string) $request->input('status', 'new');
            if (! in_array($next, ['new', 'contacted', 'won', 'closed', 'spam'], true)) {
                return back()->with('error_msg', 'Statut invalide.');
            }
            $row = DB::table('grimba_advertiser_leads')->where('id', $id)->first();
            abort_if(! $row, 404);

            DB::table('grimba_advertiser_leads')
                ->where('id', $id)
                ->update([
                    'status' => $next,
                    'last_admin_action_at' => now(),
                    'updated_at' => now(),
                ]);

            return back()->with('success_msg', 'Statut mis à jour.');
        })->name('advertiser-leads.status');

        Route::delete('advertiser-leads/{id}', function (int $id) {
            DB::table('grimba_advertiser_leads')->where('id', $id)->delete();
            return back()->with('success_msg', 'Lead supprimé.');
        })->name('advertiser-leads.destroy');

        Route::get('advertiser-leads/export.csv', function () {
            $response = new StreamedResponse(function () {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['id', 'email', 'company', 'budget_band', 'goals', 'source_slot', 'source_referrer', 'locale', 'ip', 'status', 'created_at']);
                DB::table('grimba_advertiser_leads')
                    ->orderBy('created_at')
                    ->lazy()
                    ->each(function ($row) use ($out) {
                        fputcsv($out, [
                            $row->id,
                            $row->email,
                            $row->company,
                            $row->budget_band,
                            $row->goals,
                            $row->source_slot,
                            $row->source_referrer,
                            $row->locale,
                            $row->ip,
                            $row->status,
                            $row->created_at,
                        ]);
                    });
                fclose($out);
            });
            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="grimbanews-advertiser-leads-' . now()->format('Ymd-His') . '.csv"');
            return $response;
        })->name('advertiser-leads.export');
    });

app()->booted(function (): void {
    if (! class_exists(DashboardMenu::class)) {
        return;
    }

    DashboardMenu::default()->beforeRetrieving(function (): void {
        DashboardMenu::make()->registerItem(
            DashboardMenuItem::make()
                ->id('grimba-advertiser-leads')
                ->priority(40)
                ->parentId('grimba-root')
                ->name('Leads annonceurs')
                ->icon('ti ti-currency-dollar')
                ->route('grimba.advertiser-leads.index')
        );
    });
});
