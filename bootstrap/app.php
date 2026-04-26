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
        // GrimbaNews reader-preference cookies live unencrypted so:
        //   1. JS on the client can read them (already true for
        //      grimba_read which is set in JS)
        //   2. The server-side region scope (S147) reads the same
        //      plain value JS would write
        //   3. End users / tests can inspect them in devtools without
        //      having to round-trip through a Laravel decrypt
        // None of these store anything sensitive — they're UI prefs.
        $middleware->encryptCookies(except: [
            'grimba_read',
            'grimba_cookie_consent',
            'grimba_region',
            'grimba_lang',
            'grimba_translate',
            'grimba_follow',
            'grimba_onboarded',
            // S167 — local-news preferences (city / country / ISO code)
            'grimba_local_city',
            'grimba_local_country',
            'grimba_local_cc',
            // S172 — theme preference (light / dark / auto). Was
            // encrypted: server reads landed on null → "auto" default →
            // wrong CSS variables on first paint for users who chose
            // dark explicitly. Now the cookie value the JS writes is
            // the same string the SSR sees.
            'grimba_theme',
            // S173 — saved-for-later vault (post id list, JS-managed).
            'grimba_vault',
        ]);

        // Apply our locale-from-cookie switch on every web request.
        $middleware->web(append: [
            \App\Http\Middleware\GrimbaLocale::class,
            \App\Http\Middleware\GrimbaPublicCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
