<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // GrimbaNews reader-history cookie is set client-side via JS
        // on every post view — can't be Laravel-encrypted. Exclude it
        // so EncryptCookies doesn't null it out server-side.
        $middleware->encryptCookies(except: [
            'grimba_read',
        ]);

        // Apply our locale-from-cookie switch on every web request.
        $middleware->web(append: [
            \App\Http\Middleware\GrimbaLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
