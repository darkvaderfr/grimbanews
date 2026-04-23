<?php

/*
 * GrimbaNews — on admin root hit, redirect to GrimbaNews cockpit.
 * Keeps the stock Botble dashboard reachable at /admin?stock=1.
 */

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;

Event::listen(RouteMatched::class, function (RouteMatched $event) {
    $request = $event->request;
    $adminPrefix = trim((string) config('core.base.general.admin_dir', 'admin'), '/');

    if ($request->path() !== $adminPrefix) return;
    if ($request->query('stock') === '1') return;
    if (! auth()->check()) return;

    $target = url('/' . $adminPrefix . '/grimba/cockpit');
    redirect($target)->send();
    exit;
});
