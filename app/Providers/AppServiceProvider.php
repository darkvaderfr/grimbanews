<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Flip the app locale from the grimba_lang cookie right before
        // any view renders. This wins over Botble's Language plugin
        // because view composers run after every middleware.
        View::composer('*', function () {
            $request = request();
            if (! $request) return;

            $preferred = (string) $request->cookie('grimba_lang', '');
            if ($preferred === 'en' || $preferred === 'fr') {
                app()->setLocale($preferred);
            }
        });
    }
}
