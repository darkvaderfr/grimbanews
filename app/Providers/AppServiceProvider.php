<?php

namespace App\Providers;

use App\Scopes\GrimbaRegionScope;
use Botble\Blog\Models\Post;
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

        // S147 — region scope. Filters reader-facing Post queries by
        // the visitor's grimba_region cookie. Self-bypassed on admin
        // / API / console runs so editor and cron always see the full
        // corpus. The scope owns the migration of legacy cookie
        // values (monde/europe/afrique).
        //
        // Scope name MUST NOT contain a dot — Eloquent's Arr::get
        // resolves dots as nested-path separators, so 'grimba.region'
        // would be invisible to hasGlobalScope/getGlobalScope.
        Post::addGlobalScope('grimba_region', new GrimbaRegionScope());
    }
}
