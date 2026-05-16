<?php

namespace App\Providers;

use App\Ground\Regions;
use App\Scopes\GrimbaRegionScope;
use Botble\Blog\Models\Post;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Browser log collectors are useful in isolated debugging, but they
        // add client-side monkey patches to every HTML response. Keep the
        // GrimbaNews admin/auth surfaces deterministic while Steve is testing.
        config()->set('boost.browser_logs_watcher', false);
    }

    public function boot(): void
    {
        $this->disableDebugbarOnAdmin();
        $this->canonicalizeArticleUrls();

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

        // Editorial region tag, set at save time so every ingest path
        // (RSS, NewsAPI, LiveNews, manual editor) writes the column
        // automatically. Falls back gracefully when the column is not
        // present (the migration is opt-in per Vader's "no migrations
        // without ask" rule).
        Post::saving(function (Post $post): void {
            if (! Schema::hasColumn('posts', 'editorial_region')) {
                return;
            }
            if (! empty($post->editorial_region)) {
                return; // already set explicitly upstream
            }
            $country = null;
            if (! empty($post->source_id)) {
                $country = DB::table('news_sources')
                    ->where('id', $post->source_id)
                    ->value('country');
            }
            $post->editorial_region = Regions::regionForCountry($country);
        });
    }

    private function canonicalizeArticleUrls(): void
    {
        if (! function_exists('add_filter')) {
            return;
        }

        add_filter('slug_filter_url', function (string $url): string {
            static $postSlugCache = [];

            $parts = parse_url($url);
            $path = (string) ($parts['path'] ?? '');

            if (! preg_match('~/blog/([^/?#]+)$~', $path, $matches)) {
                return $url;
            }

            $slug = rawurldecode($matches[1]);
            $isPost = $postSlugCache[$slug] ??= Slug::query()
                ->where('key', $slug)
                ->where('prefix', 'blog')
                ->where('reference_type', Post::class)
                ->exists();

            if (! $isPost) {
                return $url;
            }

            $parts['path'] = preg_replace('~/blog/([^/?#]+)$~', '/article/' . rawurlencode($slug), $path) ?: $path;

            return $this->buildUrlFromParts($parts);
        }, 99);
    }

    /**
     * @param array{scheme?: string, host?: string, port?: int, user?: string, pass?: string, path?: string, query?: string, fragment?: string} $parts
     */
    private function buildUrlFromParts(array $parts): string
    {
        $auth = isset($parts['user'])
            ? $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . '@'
            : '';

        return (isset($parts['scheme']) ? $parts['scheme'] . '://' : '')
            . $auth
            . ($parts['host'] ?? '')
            . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . ($parts['path'] ?? '')
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    private function disableDebugbarOnAdmin(): void
    {
        $adminPrefix = trim((string) config('core.base.general.admin_dir', env('ADMIN_DIR', 'admin')), '/');
        $except = (array) config('debugbar.except', []);

        config()->set('debugbar.except', array_values(array_unique([
            ...$except,
            $adminPrefix,
            $adminPrefix . '/*',
        ])));
    }
}
