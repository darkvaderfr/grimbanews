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
        $this->preservePaginationInCanonical();

        // Wave IIIIIIIII (Vader 2026-05-22) — kill Botble's
        // auto-emitted NewsArticle JSON-LD on single article pages.
        //
        // The Blog plugin's HookServiceProvider (line 134) hooks
        // BASE_ACTION_PUBLIC_RENDER_SINGLE to emit a NewsArticle
        // schema with publisher.name = Theme::getSiteTitle(), which
        // resolves to the FR-only "Grimba News — Voyez chaque angle
        // de chaque histoire". For EN readers this poisoned Google's
        // article rich-result publisher metadata.
        //
        // post.blade.php already emits its OWN richer NewsArticle
        // JSON-LD with the correct `'publisher' => ['name' => 'GrimbaNews']`
        // (line 82-92), so Botble's auto-emission is pure duplicate +
        // FR bleed. Forcing the setting to false in-memory (no DB
        // save) kills the duplicate on every request.
        if (function_exists('setting')) {
            setting()->set('blog_post_schema_enabled', false);
        }

        // Wave DDDDDDDDD (Vader 2026-05-22) — locale-override fix.
        //
        // EN reader hits `/breaking?lang=en`. Route closure calls
        // `SeoHelper::setTitle(__('Breaking news') . ' — GrimbaNews')`.
        // Without this fix, the page <title> rendered as the FR string
        // "Dernières nouvelles — GrimbaNews" because Botble's
        // LocaleSessionRedirect + LocalizationRedirectFilter middleware
        // were pushed to the `web` group AFTER our GrimbaLocale and
        // reset the app locale to config('app.locale') = 'fr' before
        // the closure ran. __() then returned the FR translation and
        // setTitle stored the FR string permanently.
        //
        // Fix: register GrimbaLocaleEnforce both as a route-level alias
        // AND push it to the `web` middleware group from
        // `$this->app->booted()`. booted() fires AFTER every service
        // provider's boot(), so this push lands at the END of the
        // `web` group — AFTER all Botble pushes — and wins the race.
        $this->app->booted(function () {
            $router = $this->app['router'];
            $router->aliasMiddleware('grimba.locale.enforce', \App\Http\Middleware\GrimbaLocaleEnforce::class);
        });

        // Hook Botble's public-route filter at priority 999 — runs
        // AFTER Botble's own LanguageServiceProvider hook at priority
        // 958 which appends `localeSessionRedirect` +
        // `localizationRedirect`. We append our middleware LAST so it
        // gets the final word on the locale before the route closure.
        if (function_exists('add_filter') && defined('BASE_FILTER_GROUP_PUBLIC_ROUTE')) {
            add_filter(BASE_FILTER_GROUP_PUBLIC_ROUTE, function (array $data): array {
                $data['middleware'] = array_merge(
                    \Illuminate\Support\Arr::get($data, 'middleware', []),
                    [\App\Http\Middleware\GrimbaLocaleEnforce::class],
                );
                $data['middleware'] = array_unique($data['middleware']);
                return $data;
            }, 999);
        }

        $flipFromRequest = static function (): void {
            $request = request();
            if (! $request) return;
            $query = (string) $request->query('lang', '');
            if ($query === 'en' || $query === 'fr') {
                app()->setLocale($query);
                return;
            }
            $preferred = (string) $request->cookie('grimba_lang', '');
            if ($preferred === 'en' || $preferred === 'fr') {
                app()->setLocale($preferred);
            }
        };
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Routing\Events\RouteMatched::class,
            fn () => $flipFromRequest(),
        );
        View::composer('*', fn () => $flipFromRequest());

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
        //
        // NOTE: Schema::hasColumn hits information_schema on every call.
        // We cache the result in a static so a bulk ingest cron doesn't
        // pay 2x extra round-trips per saved post (Zen audit 2026-05-16).
        //
        // ASSUMPTION: every ingest pipeline (GrimbaRssPoller line ~475,
        // GrimbaNewsApiFetcher, GrimbaLiveNewsFetcher) calls $post->save()
        // — raw DB::table('posts')->insert paths would bypass this hook.
        // If a future "perf optimization" switches one path to raw insert,
        // the column needs to be set explicitly there.
        static $hasRegionCol = null;
        Post::saving(function (Post $post) use (&$hasRegionCol): void {
            if ($hasRegionCol === null) {
                $hasRegionCol = Schema::hasColumn('posts', 'editorial_region');
            }
            if (! $hasRegionCol) {
                return;
            }
            if (! empty($post->editorial_region)) {
                return; // already set explicitly upstream
            }
            // Vader 2026-05-18: topic-based detection first. If the
            // article's title + description has strong region anchors
            // (e.g. Le Monde covering Senegal), tag by what it's
            // ABOUT rather than who published it.
            //
            // S-LSAT-18b — use detectAllFromText so cross-region
            // stories also populate `editorial_secondary_region`
            // (Macron-meets-Zelensky-in-Kigali → primary=europe,
            // secondary=africa).
            $topical = \App\Support\GrimbaArticleRegion::detectAllFromText(
                (string) ($post->name ?? ''),
                (string) ($post->description ?? ''),
                (string) ($post->summary_nobuai ?? ''),
            );
            if (($topical['primary'] ?? null) !== null) {
                $post->editorial_region = $topical['primary'];
                $hasSecondaryCol = Schema::hasColumn('posts', 'editorial_secondary_region');
                if ($hasSecondaryCol) {
                    // Only assign secondary when it's NOT the same
                    // as primary (defense-in-depth — the detector
                    // already guards but the DB column is the
                    // load-bearing read).
                    $secondary = $topical['secondary'] ?? null;
                    if ($secondary !== null && $secondary !== $topical['primary']) {
                        $post->editorial_secondary_region = $secondary;
                    }
                }
                return;
            }
            // Fallback: source-country region (the legacy path).
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
     * Wave BBBBBBBB (Vader 2026-05-19) — preserve `?page=N` (N>1) in
     * the rel=canonical URL. Botble's SeoHelper unconditionally
     * strips ALL query params before emitting the canonical, so
     * /breaking?page=2 ended up canonicaling to /breaking — telling
     * Google to ignore pages 2+ as duplicates. That blocks every
     * article on page 2+ from getting indexed.
     *
     * Google's official guidance (post-2019 rel=prev/next deprecation):
     * each paginated page should canonical to itself. So
     * /breaking?page=2 → canonical /breaking?page=2. We do NOT
     * canonical ?page=1 to itself (treat that as the same content
     * as the bare URL — Google's standard pagination convention).
     *
     * Tracking params (utm_*, fbclid, gclid) are still stripped —
     * only `page` is preserved.
     */
    private function preservePaginationInCanonical(): void
    {
        if (! function_exists('add_filter')) {
            return;
        }

        add_filter('core_seo_canonical', function (string $canonicalUrl): string {
            $page = (int) request()->query('page', 1);
            if ($page <= 1) {
                return $canonicalUrl;
            }
            // Append page=N. Use parse_url to handle URLs that
            // already have a fragment (#section); fragment must
            // come after the query.
            $parts = parse_url($canonicalUrl);
            $base = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
            if (isset($parts['port'])) {
                $base .= ':' . $parts['port'];
            }
            $base .= $parts['path'] ?? '';
            $base .= '?page=' . $page;
            if (isset($parts['fragment'])) {
                $base .= '#' . $parts['fragment'];
            }
            return $base;
        }, 50);
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
