<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * GrimbaLocaleEnforce — final-locale-word middleware.
 *
 * Wired by AppServiceProvider via a priority-999 hook on
 * BASE_FILTER_GROUP_PUBLIC_ROUTE so it lands AFTER Botble's
 * `localeSessionRedirect` (priority 958) — which calls
 * App::setLocale($sessionLocale) and would otherwise overwrite
 * the locale before the route closure runs.
 *
 * The bug it fixes: EN reader hits `/breaking?lang=en`, the route
 * closure calls `SeoHelper::setTitle(__('Breaking news') ...)`, but
 * __() returns the FR string because the closure ran with locale=fr
 * (Botble's LocaleSessionRedirect having reset it from the session
 * or default config).
 *
 * Precedence (matches GrimbaTranslationPresenter::targetLocale and
 * GrimbaHomeFeed::resolveReaderLocale):
 *   `?lang=` query > grimba_lang cookie > framework default
 */
class GrimbaLocaleEnforce
{
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
