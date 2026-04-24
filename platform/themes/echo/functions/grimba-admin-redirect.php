<?php

/*
 * GrimbaNews — on admin root hit, redirect to GrimbaNews cockpit.
 *
 * The redirect logic lives in App\Http\Middleware\GrimbaAdminRootRedirect
 * so Laravel's MiddlewareNameResolver can resolve it by class name.
 * Here we just attach it to the 'web' group during boot — attaching
 * after 'web' means StartSession has already run, so auth()->check()
 * reflects the real session state (the original RouteMatched listener
 * ran before StartSession and therefore always saw an anonymous
 * request).
 *
 * Keeps the stock Botble dashboard reachable at /admin?stock=1.
 */

use App\Http\Middleware\GrimbaAdminRootRedirect;
use Illuminate\Routing\Router;

app()->booted(function (): void {
    /** @var Router $router */
    $router = app('router');
    $router->pushMiddlewareToGroup('web', GrimbaAdminRootRedirect::class);
});
