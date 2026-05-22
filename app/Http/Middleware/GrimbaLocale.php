<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GrimbaLocale
{
    /**
     * Honor the `?lang=` query + grimba_lang cookie by flipping
     * the app locale for this request. Runs after EncryptCookies,
     * so the cookie is decrypted and readable.
     *
     * Wave DDDDDDDDD (Vader 2026-05-22) — query precedence added.
     * Without it, a first-time EN reader hitting `/breaking?lang=en`
     * (no cookie yet) got FR titles because SeoHelper::setTitle(__())
     * in the route handler ran with framework-default locale (fr).
     * Now matches `GrimbaTranslationPresenter::targetLocale()` and
     * `GrimbaHomeFeed::resolveReaderLocale()`: query > cookie >
     * framework default.
     */
    public function handle(Request $request, Closure $next)
    {
        $query = (string) $request->query('lang', '');
        if ($query === 'en' || $query === 'fr') {
            app()->setLocale($query);
            return $next($request);
        }
        $preferred = (string) $request->cookie('grimba_lang', '');
        if ($preferred === 'en' || $preferred === 'fr') {
            app()->setLocale($preferred);
        }

        return $next($request);
    }
}
