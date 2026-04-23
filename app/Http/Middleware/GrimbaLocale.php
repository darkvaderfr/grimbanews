<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GrimbaLocale
{
    /**
     * Honor the grimba_lang cookie by flipping the app locale for
     * this request. Runs after EncryptCookies, so the cookie is
     * decrypted and readable via $request->cookie().
     */
    public function handle(Request $request, Closure $next)
    {
        $preferred = (string) $request->cookie('grimba_lang', '');
        if ($preferred === 'en' || $preferred === 'fr') {
            app()->setLocale($preferred);
        }

        return $next($request);
    }
}
