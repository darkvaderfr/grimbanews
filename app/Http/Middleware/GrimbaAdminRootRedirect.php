<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/*
 * When an authenticated user hits /admin (the Botble stock dashboard
 * root), redirect them to /admin/grimba/cockpit. Anyone wanting the
 * stock dashboard back can append ?stock=1.
 *
 * Registered on the 'web' middleware group by grimba-admin-redirect.php
 * so it sees a resolved session (and therefore a real auth()->check()).
 */
class GrimbaAdminRootRedirect
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $adminPrefix = trim((string) config('core.base.general.admin_dir', 'admin'), '/');
        if ($request->path() !== $adminPrefix) {
            return $next($request);
        }
        if ($request->query('stock') === '1') {
            return $next($request);
        }
        if (! auth()->check()) {
            return $next($request);
        }

        return redirect('/' . $adminPrefix . '/grimba/cockpit');
    }
}
