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
