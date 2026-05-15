<?php

use App\Support\GrimbaTranslationPresenter as GnTr;
use App\Support\GrimbaPostRecency;
use App\Support\GrimbaSavedSearches;
use App\Support\GrimbaVault;
use App\Support\GrimbaVaultEvents;
use Botble\Base\Http\Middleware\RequiresJsonRequestMiddleware;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Slug\Models\Slug;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Theme\Echo\Http\Controllers\EchoController;

if (! function_exists('grimba_record_source_logo_probe')) {
    function grimba_record_source_logo_probe(int $sourceId, string $provider, bool $ok, string $url): void
    {
        if ($sourceId <= 0 || ! in_array($provider, ['clearbit', 'favicon'], true)) {
            return;
        }
        if (! Schema::hasColumn('news_sources', 'logo_status')) {
            return;
        }

        $status = $ok
            ? ($provider === 'clearbit' ? 'clearbit' : 'favicon')
            : ($provider === 'favicon' ? 'missing' : 'fallback');

        DB::table('news_sources')->where('id', $sourceId)->update([
            'logo_status' => $status,
            'logo_url' => $ok ? $url : DB::raw('logo_url'),
            'logo_checked_at' => now(),
            'logo_error' => $ok ? null : ($provider . ' logo probe failed'),
            'updated_at' => now(),
        ]);
    }
}

Route::group(['middleware' => ['web', 'core']], function (): void {
    Theme::registerRoutes(function (): void {
        Route::get('comparatif', function (Request $request) {
            // S324 — paginate (was rendering all 575 clusters in one wall,
            // page measured 72,000px tall). 24 per page, ?diversity= filter.
            $perPage = 24;
            $page = max(1, (int) $request->query('page', 1));
            $diversityFilter = (string) $request->query('diversity', 'all');
            if (! in_array($diversityFilter, ['all', 'balanced', 'partial', 'one_sided'], true)) {
                $diversityFilter = 'all';
            }

            // Pre-aggregate post counts per cluster + per bias side in
            // a single grouped query so we don't fan out N+1 below.
            $aggregateRows = DB::table('posts')
                ->where('status', 'published')
                ->whereNotNull('story_cluster_id')
                ->select('story_cluster_id', 'bias_rating', DB::raw('count(*) as c'), DB::raw('max(' . GrimbaPostRecency::expression() . ') as latest_at'))
                ->groupBy('story_cluster_id', 'bias_rating')
                ->get();

            $aggByCluster = [];
            foreach ($aggregateRows as $row) {
                $cid = (int) $row->story_cluster_id;
                if (! isset($aggByCluster[$cid])) {
                    $aggByCluster[$cid] = ['left' => 0, 'center' => 0, 'right' => 0, 'total' => 0, 'latestAt' => null];
                }
                if (isset($aggByCluster[$cid][$row->bias_rating])) {
                    $aggByCluster[$cid][$row->bias_rating] = (int) $row->c;
                }
                $aggByCluster[$cid]['total'] += (int) $row->c;
                if ($aggByCluster[$cid]['latestAt'] === null || $row->latest_at > $aggByCluster[$cid]['latestAt']) {
                    $aggByCluster[$cid]['latestAt'] = $row->latest_at;
                }
            }

            $allClusters = DB::table('story_clusters')
                ->orderByDesc('id')
                ->get()
                ->map(function ($c) use ($aggByCluster) {
                    $cid = (int) $c->id;
                    $agg = $aggByCluster[$cid] ?? ['left' => 0, 'center' => 0, 'right' => 0, 'total' => 0, 'latestAt' => null];
                    $c->total    = $agg['total'];
                    $c->counts   = ['left' => $agg['left'], 'center' => $agg['center'], 'right' => $agg['right']];
                    $c->latestAt = $agg['latestAt'];

                    $sidesPresent = (int) ($agg['left'] > 0) + (int) ($agg['center'] > 0) + (int) ($agg['right'] > 0);
                    $c->diversity = match ($sidesPresent) {
                        3 => 'balanced',
                        2 => 'partial',
                        1 => 'one_sided',
                        default => 'unrated',
                    };
                    return $c;
                })
                ->filter(fn ($c) => $c->total > 0)
                ->values();

            $filtered = $diversityFilter === 'all'
                ? $allClusters
                : $allClusters->filter(fn ($c) => $c->diversity === $diversityFilter)->values();

            $totalCount = $filtered->count();
            $clusters = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

            $pagination = (object) [
                'currentPage' => $page,
                'lastPage'    => max(1, (int) ceil($totalCount / $perPage)),
                'totalCount'  => $totalCount,
                'perPage'     => $perPage,
            ];

            SeoHelper::setTitle(__('Comparer les sources') . ' — GrimbaNews')
                ->setDescription(__("Tous les dossiers en cours — chaque histoire vue sous plusieurs angles."));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Comparer les sources'), url('/comparatif'));

            return Theme::scope('comparison-index', compact('clusters', 'pagination', 'diversityFilter'))->render();
        })->name('public.comparison.index');

        Route::get('comparatif/{clusterId}', function (int $clusterId) {
            $posts = Post::withoutGlobalScope('grimba_region')
                ->where('story_cluster_id', $clusterId)
                ->where('status', 'published')
                ->tap(fn ($q) => GnTr::orderForTargetLocale($q, withRecency: false))
                ->orderByRaw("CASE bias_rating WHEN 'left' THEN 1 WHEN 'center' THEN 2 WHEN 'right' THEN 3 ELSE 4 END")
                ->get();

            $storyTitle = $posts->first()->name ?? ('Dossier #' . $clusterId);

            SeoHelper::setTitle(__('Comparaison des sources') . ' — ' . $storyTitle)
                ->setDescription(__('Comparez comment les médias couvrent la même histoire.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Comparaison'), url('/comparatif/' . $clusterId));

            return Theme::scope('comparison', [
                'posts'      => $posts,
                'storyTitle' => $storyTitle,
                'clusterId'  => $clusterId,
            ])->render();
        })->name('public.comparison');

        $feedHandler = function () {
            $posts = Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->tap(fn ($q) => GnTr::orderForTargetLocale($q))
                ->limit(30)
                ->get();

            $xml = view('theme.grimba-feed', [
                'posts'     => $posts,
                'siteTitle' => 'GrimbaNews',
                'siteUrl'   => url('/'),
                'siteDesc'  => 'Voyez chaque angle de chaque histoire — actualités francophones classées par biais éditorial.',
                'feedUrl'   => url('/feed.xml'),
                'builtAt'   => now()->toRssString(),
            ])->render();

            return response($xml, 200)
                ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
        };

        // Both paths exposed: /feed.xml is the canonical public URL
        // (prod rewrites .xml through index.php); /feed works with
        // PHP's built-in dev server which skips the router for .xml.
        Route::get('feed.xml', $feedHandler)->name('public.feed');
        Route::get('feed',     $feedHandler)->name('public.feed.alt');

        // Both variants because PHP's built-in dev server short-circuits
        // .png paths before routing kicks in; /og/post/{id} works in dev,
        // /og/post/{id}.png is the canonical URL in prod (Apache/Nginx rewrites).
        Route::get('og/post/{id}.png', [\App\Http\Controllers\GrimbaOgImageController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('public.og.post');
        Route::get('og/post/{id}', [\App\Http\Controllers\GrimbaOgImageController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('public.og.post.alt');
        Route::get('og/story/{id}.png', [\App\Http\Controllers\GrimbaOgImageController::class, 'story'])
            ->where('id', '[0-9]+')
            ->name('public.og.story');
        Route::get('og/story/{id}', [\App\Http\Controllers\GrimbaOgImageController::class, 'story'])
            ->where('id', '[0-9]+')
            ->name('public.og.story.alt');
        // S145 — cookie-consent endpoint. Records the visitor's
        // accept/reject choice as an unencrypted cookie + writes a
        // fire-and-forget log line for audit. Returns 204; the
        // banner JS already hides itself client-side.
        Route::post('cookie-consent/{action}', function (Request $request, string $action) {
            if (! in_array($action, ['accept', 'reject'], true)) {
                return response()->noContent(400);
            }
            $value = $action === 'accept' ? 'accepted' : 'rejected';

            \Illuminate\Support\Facades\Log::info('[grimba.cookie-consent] choice', [
                'choice'  => $value,
                'ip_hash' => substr(sha1((string) $request->ip()), 0, 12),
                'ua_hash' => substr(sha1((string) $request->userAgent()), 0, 12),
            ]);

            return response()
                ->noContent()
                ->cookie('grimba_cookie_consent', $value, 60 * 24 * 365, '/', null, false, false);
        })->name('public.cookie-consent');

        Route::get('og/home.png', [\App\Http\Controllers\GrimbaOgImageController::class, 'home'])->name('public.og.home');
        Route::get('og/home',     [\App\Http\Controllers\GrimbaOgImageController::class, 'home'])->name('public.og.home.alt');
        Route::get('og/{surface}.png', [\App\Http\Controllers\GrimbaOgImageController::class, 'surface'])
            ->where('surface', 'local|coffre')
            ->name('public.og.surface');
        Route::get('og/{surface}', [\App\Http\Controllers\GrimbaOgImageController::class, 'surface'])
            ->where('surface', 'local|coffre')
            ->name('public.og.surface.alt');

        // S213 / B-IMG-01 — constrained image proxy for outlet logos
        // and allowlisted publisher hero images. Keeps reader cards off
        // third-party image hosts without becoming a general-purpose
        // open proxy.
        Route::get('img-proxy', function (Request $request) {
            $url = (string) $request->query('u', '');
            $sourceId = (int) $request->query('sid', 0);
            $provider = (string) $request->query('provider', '');
            $postId = max(0, (int) $request->query('pid', 0));
            $parts = parse_url($url);
            $host = strtolower((string) ($parts['host'] ?? ''));
            $theme = (string) $request->query('theme', $request->cookie('grimba_theme', 'light'));
            if (! in_array($theme, ['light', 'dark'], true)) {
                $theme = 'light';
            }

            $allowedHosts = match ($provider) {
                'clearbit' => ['logo.clearbit.com'],
                'favicon' => ['www.google.com'],
                'article-hero' => array_map(
                    static fn (string $host): string => strtolower($host),
                    config('grimba_image_proxy.article_hero_hosts', [])
                ),
                default => [],
            };
            $isArticleHero = $provider === 'article-hero';

            abort_unless(
                in_array($parts['scheme'] ?? '', ['http', 'https'], true)
                && in_array($host, $allowedHosts, true),
                404
            );

            $cacheKey = sha1($provider . '|' . $url . ($isArticleHero ? '|pid:' . $postId . '|theme:' . $theme : ''));
            $cachePath = storage_path('app/public/img-proxy/' . $cacheKey . '.bin');
            $metaPath = $cachePath . '.type';

            if (! \Illuminate\Support\Facades\File::exists($cachePath)) {
                try {
                    $res = \Illuminate\Support\Facades\Http::timeout(6)
                        ->connectTimeout(3)
                        ->withHeaders(['Accept' => 'image/avif,image/webp,image/png,image/jpeg,image/*'])
                        ->get($url);

                    abort_unless($res->successful(), 404);

                    $type = strtolower((string) $res->header('Content-Type', 'image/png'));
                    abort_unless(str_starts_with($type, 'image/'), 404);

                    $body = $res->body();
                    abort_if(strlen($body) > (int) config('grimba_image_proxy.max_bytes', 8 * 1024 * 1024), 404);

                    \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($cachePath));
                    \Illuminate\Support\Facades\File::put($cachePath, $body);
                    \Illuminate\Support\Facades\File::put($metaPath, strtok($type, ';') ?: 'image/png');
                    grimba_record_source_logo_probe($sourceId, $provider, true, $url);
                } catch (\Throwable) {
                    if (! $isArticleHero) {
                        grimba_record_source_logo_probe($sourceId, $provider, false, $url);
                        abort(404);
                    }

                    $fallback = app(\App\Http\Controllers\GrimbaPlaceholderController::class)
                        ->show($postId, $request);
                    $type = (string) ($fallback->headers->get('Content-Type') ?: 'image/svg+xml; charset=UTF-8');

                    \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($cachePath));
                    \Illuminate\Support\Facades\File::put($cachePath, $fallback->getContent());
                    \Illuminate\Support\Facades\File::put($metaPath, $type);
                }
            }

            $type = \Illuminate\Support\Facades\File::exists($metaPath)
                ? trim((string) \Illuminate\Support\Facades\File::get($metaPath))
                : 'image/png';
            $maxAge = $isArticleHero
                ? (int) config('grimba_image_proxy.article_hero_cache_seconds', 2592000)
                : (int) config('grimba_image_proxy.logo_cache_seconds', 604800);

            return response(\Illuminate\Support\Facades\File::get($cachePath), 200, [
                'Content-Type' => $type ?: 'image/png',
                'Cache-Control' => 'public, max-age=' . $maxAge . ', s-maxage=' . $maxAge,
            ]);
        })->name('public.img-proxy');

        // S96 — editorial SVG placeholder for posts with no image.
        // Served cheap (no GD, no file cache — one string build per
        // request, HTTP cached for 24h). Reader cards + hero fall back
        // to this when posts.image is null.
        Route::get('og/placeholder/{id}.svg', [\App\Http\Controllers\GrimbaPlaceholderController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('public.og.placeholder');
        Route::get('og/placeholder/{id}', [\App\Http\Controllers\GrimbaPlaceholderController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('public.og.placeholder.alt');

        // GrimbaNews /search — SQLite FTS5 with source, owner, date,
        // and bias facets.
        // Registered before Botble's default /search (Botble\Blog\Http\
        // Controllers\PublicController) so our handler wins. Keeps the
        // existing search.blade.php view (expects $posts) and layers on
        // the optional $availableSources / $availableBiases / $selected
        // view vars for the facet UI.
        $searchHandler = function (Request $request) {
            $q        = trim((string) $request->query('q', ''));
            $sourceId = (int) $request->query('source', 0) ?: null;
            $bias     = in_array($request->query('bias'), ['left', 'center', 'right', 'unknown'], true)
                ? $request->query('bias')
                : null;
            $owner = trim((string) $request->query('owner', ''));
            $owner = mb_strlen($owner) > 180 ? mb_substr($owner, 0, 180) : $owner;
            $fromDate = trim((string) $request->query('from_date', ''));
            $toDate = trim((string) $request->query('to_date', ''));

            $validDate = static fn (string $date): bool => $date === '' || (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
            if (! $validDate($fromDate)) {
                $fromDate = '';
            }
            if (! $validDate($toDate)) {
                $toDate = '';
            }

            $savedSearchCriteria = GrimbaSavedSearches::normalize([
                'q' => $q,
                'source' => $sourceId,
                'bias' => $bias,
                'owner' => $owner,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ]);
            $savedSearchActive = false;
            $savedSearchLimitReached = false;
            $savedSearchCount = 0;

            if ($q !== '' && ($member = auth('member')->user())) {
                $savedSearchActive = GrimbaSavedSearches::existsForMember($member, $savedSearchCriteria);
                $savedSearchCount = GrimbaSavedSearches::countForMember($member);
                $savedSearchLimitReached = ! $savedSearchActive && $savedSearchCount >= GrimbaSavedSearches::MAX_PER_MEMBER;
            }

            $posts = collect();

            if ($q !== '') {
                // Quote every term so FTS5's syntax characters in user
                // input ("OR", "NOT", "*", "-") never throw fts5: syntax
                // error. Terms are ANDed implicitly.
                $ftsQuery = collect(preg_split('/\s+/u', $q))
                    ->filter(fn ($t) => mb_strlen($t) > 0)
                    ->map(fn ($t) => '"' . str_replace('"', '""', $t) . '"')
                    ->implode(' ');

                $ids = DB::table('posts_fts')
                    ->whereRaw('posts_fts MATCH ?', [$ftsQuery])
                    ->orderByRaw('bm25(posts_fts)')
                    ->limit(500)
                    ->pluck('rowid')
                    ->all();

                if (! empty($ids)) {
                    $query = \Botble\Blog\Models\Post::query()
                        ->whereIn('posts.id', $ids)
                        ->where('posts.status', 'published');

                    if ($sourceId) {
                        $query->where('posts.source_id', $sourceId);
                    }
                    if ($bias) {
                        $query->where('posts.bias_rating', $bias);
                    }
                    if ($owner !== '') {
                        $query->whereIn('posts.source_id', function ($sub) use ($owner): void {
                            $sub->select('id')
                                ->from('news_sources')
                                ->where('owner_name', $owner);
                        });
                    }
                    if ($fromDate !== '') {
                        GrimbaPostRecency::wherePublishedDateFrom($query, $fromDate);
                    }
                    if ($toDate !== '') {
                        GrimbaPostRecency::wherePublishedDateTo($query, $toDate);
                    }

                    // Preserve the BM25 ordering from the FTS result after locale priority.
                    GnTr::orderForTargetLocale($query, withRecency: false);
                    $query->orderByRaw("CASE posts.id " .
                        collect($ids)
                            ->values()
                            ->map(fn ($id, $i) => 'WHEN ' . (int) $id . ' THEN ' . $i)
                            ->implode(' ') .
                        " END");

                    $posts = $query->paginate(12)->withQueryString();
                } else {
                    $posts = \Botble\Blog\Models\Post::query()->whereRaw('1 = 0')->paginate(12);
                }
            }

            $availableSources = DB::table('news_sources')->orderBy('name')->get(['id', 'name']);
            $availableOwners = DB::table('news_sources')
                ->whereNotNull('owner_name')
                ->where('owner_name', '!=', '')
                ->select('owner_name')
                ->distinct()
                ->orderBy('owner_name')
                ->pluck('owner_name');

            SeoHelper::setTitle(($q !== '' ? __('Recherche : :query', ['query' => $q]) : __('Recherche')) . ' — GrimbaNews')
                ->setDescription(__('Explorez les articles, sources et dossiers de GrimbaNews.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Recherche'), url('/search'));

            return Theme::scope('search', [
                'posts'            => $posts,
                'availableSources' => $availableSources,
                'availableOwners'   => $availableOwners,
                'selectedSource'   => $sourceId,
                'selectedBias'     => $bias,
                'selectedOwner'    => $owner,
                'fromDate'         => $fromDate,
                'toDate'           => $toDate,
                'savedSearchCriteria' => $savedSearchCriteria,
                'savedSearchActive' => $savedSearchActive,
                'savedSearchLimitReached' => $savedSearchLimitReached,
                'savedSearchCount' => $savedSearchCount,
            ])->render();
        };

        Route::get('search', $searchHandler)->name('public.grimba-search');

        Route::post('search/alerts', function (Request $request) {
            $user = auth('member')->user();
            if (! $user) {
                return redirect(route('public.member.login'));
            }

            $criteria = GrimbaSavedSearches::normalize($request->only([
                'q',
                'source',
                'bias',
                'owner',
                'from_date',
                'to_date',
            ]));

            if ($criteria['search_query'] === '') {
                return redirect(url('/search'))->with('status', __('Entrez une recherche avant de créer une alerte.'));
            }

            $saved = GrimbaSavedSearches::upsertForMember($user, $criteria);

            return redirect(GrimbaSavedSearches::searchUrl($criteria))->with(
                'status',
                $saved
                    ? __('Alerte recherche activée.')
                    : __('Limite atteinte : supprimez une alerte depuis Mon compte.')
            );
        })->middleware('member')->name('public.saved-searches.store');

        Route::get('article/{slug}', function (string $slug) {
            $prefix = Slug::query()
                ->where('key', $slug)
                ->where('reference_type', Post::class)
                ->whereIn('prefix', ['article', 'blog'])
                ->orderByRaw("CASE prefix WHEN 'article' THEN 0 ELSE 1 END")
                ->value('prefix');

            abort_unless($prefix, 404);

            return app(\Botble\Theme\Http\Controllers\PublicController::class)->getView($slug, (string) $prefix);
        })->where('slug', '[A-Za-z0-9\-_]+')->name('public.grimba-article');

        Route::get('blog/{slug}', function (Request $request, string $slug) {
            $isPostSlug = Slug::query()
                ->where('key', $slug)
                ->where('prefix', 'blog')
                ->where('reference_type', Post::class)
                ->exists();

            if ($isPostSlug) {
                $target = route('public.grimba-article', $slug);
                $query = $request->getQueryString();

                return redirect()->to($query ? $target . '?' . $query : $target, 301);
            }

            return app(\Botble\Theme\Http\Controllers\PublicController::class)->getView($slug, 'blog');
        })->where('slug', '[A-Za-z0-9\-_]+')->name('public.grimba-blog-legacy');

        Route::get('command-palette.json', function () {
            $stories = DB::table('posts')
                ->leftJoin('slugs', function ($join): void {
                    $join->on('slugs.reference_id', '=', 'posts.id')
                        ->where('slugs.reference_type', Post::class)
                        ->where('slugs.prefix', 'blog');
                })
                ->where('posts.status', 'published')
                ->tap(fn ($query) => GrimbaPostRecency::orderByPublished($query, 'posts'))
                ->limit(30)
                ->get([
                    'posts.id',
                    'posts.name',
                    'posts.description',
                    'posts.source_name',
                    'posts.bias_rating',
                    'posts.created_at',
                    'slugs.key as slug',
                ])
                ->map(fn ($post): array => [
                    'type' => 'story',
                    'label' => __('Article'),
                    'title' => (string) $post->name,
                    'subtitle' => trim((string) ($post->source_name ?: $post->description)),
                    'meta' => (string) ($post->bias_rating ?: __('non classé')),
                    'url' => $post->slug
                        ? route('public.grimba-article', $post->slug)
                        : url('/search?q=' . rawurlencode((string) $post->name)),
                ]);

            $sources = DB::table('news_sources')
                ->orderByDesc('credibility_score')
                ->orderBy('name')
                ->limit(24)
                ->get(['name', 'slug', 'owner_name', 'bias_rating', 'credibility_score'])
                ->map(fn ($source): array => [
                    'type' => 'source',
                    'label' => __('Source'),
                    'title' => (string) $source->name,
                    'subtitle' => trim((string) ($source->owner_name ?: __('Source suivie par GrimbaNews'))),
                    'meta' => (int) ($source->credibility_score ?? 0) > 0
                        ? __('Crédibilité :score', ['score' => (int) $source->credibility_score])
                        : (string) ($source->bias_rating ?: __('non classé')),
                    'url' => url('/sources/' . ($source->slug ?: \Illuminate\Support\Str::slug((string) $source->name))),
                ]);

            $recentCategoryActivity = DB::table('post_categories')
                ->join('posts', 'posts.id', '=', 'post_categories.post_id')
                ->where('posts.status', 'published')
                ->tap(fn ($q) => GrimbaPostRecency::wherePublishedSince($q, now()->subDays(30)))
                ->selectRaw('post_categories.category_id, COUNT(*) as article_count')
                ->groupBy('post_categories.category_id');

            $categories = DB::table('categories')
                ->joinSub($recentCategoryActivity, 'activity', function ($join): void {
                    $join->on('activity.category_id', '=', 'categories.id');
                })
                ->leftJoin('slugs', function ($join): void {
                    $join->on('slugs.reference_id', '=', 'categories.id')
                        ->where('slugs.reference_type', Category::class)
                        ->where('slugs.prefix', 'blog');
                })
                ->where('categories.status', 'published')
                ->orderByDesc('activity.article_count')
                ->orderBy('categories.name')
                ->limit(24)
                ->get(['categories.id', 'categories.name', 'categories.description', 'activity.article_count', 'slugs.key as slug'])
                ->map(fn ($category): array => [
                    'type' => 'category',
                    'label' => __('Sujet'),
                    'title' => (string) $category->name,
                    'subtitle' => (string) ($category->description ?: __('Rubrique active')),
                    'meta' => trans_choice(':count article récent|:count articles récents', (int) $category->article_count, ['count' => (int) $category->article_count]),
                    'url' => $category->slug
                        ? url('/blog/' . $category->slug)
                        : url('/search?q=' . rawurlencode((string) $category->name)),
                ]);

            $items = collect()
                ->merge([
                    [
                        'type' => 'nav',
                        'label' => __('Navigation'),
                        'title' => __('Angles morts'),
                        'subtitle' => __('Histoires peu couvertes ou déséquilibrées'),
                        'meta' => 'GrimbaNews',
                        'url' => url('/angles-morts'),
                    ],
                    [
                        'type' => 'nav',
                        'label' => __('Navigation'),
                        'title' => __('Pour vous'),
                        'subtitle' => __('Votre fil, vos suivis et vos angles morts personnels'),
                        'meta' => 'GrimbaNews',
                        'url' => url('/pour-vous'),
                    ],
                    [
                        'type' => 'nav',
                        'label' => __('Navigation'),
                        'title' => __('Sources'),
                        'subtitle' => __('Biais, crédibilité et propriété des médias suivis'),
                        'meta' => 'GrimbaNews',
                        'url' => url('/sources'),
                    ],
                ])
                ->merge($stories)
                ->merge($sources)
                ->merge($categories)
                ->values();

            return response()->json([
                'generated_at' => now()->toIso8601String(),
                'ttl_seconds' => 300,
                'items' => $items,
            ])->header('Cache-Control', 'public, max-age=300, s-maxage=300');
        })->name('public.command-palette.index');

        // S170 — POST /translate/set removed with the translation
        // feature. Legacy clients that hit it just get a no-op JSON;
        // the cookie is no longer read anywhere.
        Route::post('translate/set', fn () => response()->json(['ok' => true, 'note' => 'translation feature removed']))
            ->name('public.translate.set');

        Route::post('lang/set', function (Request $request) {
            $lang = $request->input('lang') === 'en' ? 'en' : 'fr';
            return response()
                ->json(['ok' => true, 'lang' => $lang])
                ->cookie('grimba_lang', $lang, 60 * 24 * 365, '/', null, false, false);
        })->name('public.lang.set');

        $editionRedirect = function (string $region) {
            return redirect(url('/'))
                ->cookie('grimba_region', $region, 60 * 24 * 365, '/', null, false, false)
                ->cookie('grimba_onboarded', '1', 60 * 24 * 365, '/', null, false, false);
        };

        Route::get('afrique', fn () => $editionRedirect('africa'))->name('public.edition.africa');
        Route::get('europe', fn () => $editionRedirect('europe'))->name('public.edition.europe');
        Route::get('amerique', fn () => $editionRedirect('americas'))->name('public.edition.americas');
        Route::get('international', fn () => $editionRedirect('international'))->name('public.edition.international');

        Route::post('region/set', function (Request $request) {
            // Fleet K — 4-region split. Cookie name remains
            // grimba_region for back-compat. App\Ground\Regions::migrate
            // is the single source of truth for legacy → canonical
            // mapping (covers monde / europe / afrique / france / uk /
            // us / canada / amerique → 4 canonical keys).
            $region = \App\Ground\Regions::migrate((string) $request->input('region', 'international'));

            return response()
                ->json(['ok' => true, 'region' => $region])
                ->cookie('grimba_region', $region, 60 * 24 * 365, '/', null, false, false)
                ->cookie('grimba_onboarded', '1', 60 * 24 * 365, '/', null, false, false);
        })->name('public.region.set');

        Route::post('onboarding/complete', function (Request $request) {
            $ids = array_filter(array_map('intval', (array) $request->input('category_ids', [])));
            $ids = array_values(array_unique($ids));
            $value = implode(',', $ids);

            $resp = response()->json(['ok' => true, 'followed' => $ids, 'count' => count($ids)]);
            $oneYear = 60 * 24 * 365;
            $resp->cookie('grimba_follow', $value, $oneYear, '/', null, false, false);
            $resp->cookie('grimba_onboarded', '1', $oneYear, '/', null, false, false);
            return $resp;
        })->name('public.onboarding.complete');

        Route::post('topics/follow', function (Request $request) {
            $id = (int) $request->input('category_id');
            if (! $id) {
                return response()->json(['ok' => false, 'message' => 'Missing category_id'], 422);
            }

            $raw = (string) $request->cookie('grimba_follow', '');
            $ids = array_filter(array_map('intval', explode(',', $raw)));

            $action = $request->input('action', 'toggle');
            if ($action === 'follow' || ($action === 'toggle' && ! in_array($id, $ids, true))) {
                $ids[] = $id;
            } elseif ($action === 'unfollow' || ($action === 'toggle' && in_array($id, $ids, true))) {
                $ids = array_values(array_filter($ids, fn ($i) => $i !== $id));
            }

            $ids = array_values(array_unique($ids));
            $value = implode(',', $ids);

            return response()
                ->json(['ok' => true, 'followed' => $ids, 'count' => count($ids)])
                ->cookie('grimba_follow', $value, 60 * 24 * 365, '/', null, false, false);
        })->name('public.topics.follow');

        Route::get('pour-vous', function (Request $request) {
            $raw = (string) $request->cookie('grimba_follow', '');
            $ids = array_filter(array_map('intval', explode(',', $raw)));
            $readIds = collect(explode(',', (string) $request->cookie('grimba_read', '')))
                ->filter(fn ($id) => ctype_digit((string) $id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->take(30)
                ->values();
            $readHistoryCount = $readIds->count();
            $avoidedTopics = collect();

            $postsQuery = Post::query()
                ->where('status', 'published')
                ->tap(fn ($q) => GnTr::orderForTargetLocale($q));

            if (! empty($ids)) {
                $postsQuery->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
            }

            $posts = $postsQuery->paginate(12);

            if ($readHistoryCount > 10) {
                $recentReadCategoryIds = DB::table('post_categories')
                    ->join('posts', 'posts.id', '=', 'post_categories.post_id')
                    ->whereIn('posts.id', $readIds)
                    ->where('posts.status', 'published')
                    ->tap(fn ($q) => GrimbaPostRecency::wherePublishedSince($q, now()->subDays(14)))
                    ->pluck('post_categories.category_id')
                    ->unique()
                    ->values()
                    ->all();

                $avoidedTopics = Category::query()
                    ->where('status', 'published')
                    ->whereNotIn('id', $recentReadCategoryIds ?: [0])
                    ->whereIn('id', function ($query): void {
                        $query->select('post_categories.category_id')
                            ->from('post_categories')
                            ->join('posts', 'posts.id', '=', 'post_categories.post_id')
                            ->where('posts.status', 'published')
                            ->tap(fn ($q) => GrimbaPostRecency::wherePublishedSince($q, now()->subDays(14)));
                    })
                    ->orderBy('name')
                    ->limit(6)
                    ->get(['id', 'name', 'description']);
            }

            SeoHelper::setTitle(__('Pour vous') . ' — GrimbaNews')
                ->setDescription(__('Votre fil personnalisé selon les sujets que vous suivez.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Pour vous'), url('/pour-vous'));

            return Theme::scope('for-you', [
                'posts'            => $posts,
                'followedIds'      => $ids,
                'avoidedTopics'    => $avoidedTopics,
                'readHistoryCount' => $readHistoryCount,
            ])->render();
        })->name('public.for-you');

        // S104 — bias-history CSV export. Cookie-only data; the server
        // owns nothing about reading history. We hydrate post titles /
        // sources / biases from the cookie's id list and stream a CSV
        // back. If the cookie is empty, the CSV has only the header.
        Route::get('pour-vous/export.csv', function (Request $request) {
            $raw = (string) $request->cookie('grimba_read', '');
            $ids = array_values(array_filter(array_map('intval', explode(',', $raw))));

            $rows = collect();
            if (! empty($ids)) {
                // Preserve cookie order (most-recent first).
                $postColumns = ['id', 'name', 'bias_rating', 'source_name', 'created_at'];
                if (Schema::hasColumn('posts', 'published_at')) {
                    $postColumns[] = 'published_at';
                }

                $byId = Post::query()
                    ->whereIn('id', $ids)
                    ->where('status', 'published')
                    ->get($postColumns)
                    ->keyBy('id');

                foreach ($ids as $i => $id) {
                    if (! isset($byId[$id])) continue;
                    $p = $byId[$id];
                    $rows->push([
                        'rank'        => $i + 1,
                        'post_id'     => (int) $p->id,
                        'title'       => (string) $p->name,
                        'source'      => (string) ($p->source_name ?? ''),
                        'bias'        => (string) ($p->bias_rating ?? 'unknown'),
                        'published_at'=> optional(GrimbaPostRecency::value($p))->toDateString() ?? '',
                    ]);
                }
            }

            $filename = 'grimbanews-historique-' . now()->format('Y-m-d') . '.csv';

            return response()->streamDownload(function () use ($rows) {
                $h = fopen('php://output', 'w');
                // BOM so Excel renders UTF-8 correctly.
                fwrite($h, "\xEF\xBB\xBF");
                fputcsv($h, ['rang', 'post_id', 'titre', 'source', 'biais', 'publie_le']);
                foreach ($rows as $r) {
                    fputcsv($h, [
                        $r['rank'], $r['post_id'], $r['title'],
                        $r['source'], $r['bias'], $r['published_at'],
                    ]);
                }
                fclose($h);
            }, $filename, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Cache-Control'       => 'no-store, max-age=0',
                'X-GN-Privacy'        => 'cookie-only-no-server-record',
            ]);
        })->name('public.for-you.export');

        Route::post('newsletter/subscribe', function (Request $request) {
            $data = Validator::make($request->all(), [
                'email'      => ['required', 'email:rfc', 'max:191'],
                'source_key' => ['nullable', 'string', 'max:64'],
            ])->validate();

            $now    = now();
            $locale = app()->getLocale();

            $email = mb_strtolower($data['email']);
            $table = DB::table('newsletter_subscriptions');
            $existing = $table->where('email', $email)->first();
            $readIds = collect(explode(',', (string) $request->cookie('grimba_read', '')))
                ->filter(fn ($id) => ctype_digit((string) $id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->take(30)
                ->values();

            $readerBias = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
            if ($readIds->isNotEmpty()) {
                DB::table('posts')
                    ->whereIn('id', $readIds)
                    ->get(['bias_rating'])
                    ->each(function ($post) use (&$readerBias): void {
                        $bias = in_array($post->bias_rating, ['left', 'center', 'right'], true)
                            ? $post->bias_rating
                            : 'unknown';
                        $readerBias[$bias]++;
                    });
            }

            $knownBias = collect($readerBias)->only(['left', 'center', 'right']);
            $digestVariant = null;
            if ($knownBias->sum() > 0) {
                $min = $knownBias->min();
                $max = $knownBias->max();
                $underBias = $knownBias->sort()->keys()->first();
                $digestVariant = ($max - $min) <= 1 ? 'balanced' : 'rebalance_' . $underBias;
            }

            $payload = [
                'email'      => $email,
                'locale'     => $locale,
                'source_key' => $data['source_key'] ?? 'unknown',
                'reader_bias_left' => $readerBias['left'],
                'reader_bias_center' => $readerBias['center'],
                'reader_bias_right' => $readerBias['right'],
                'reader_bias_unknown' => $readerBias['unknown'],
                'digest_variant' => $digestVariant,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                'updated_at' => $now,
            ];

            if ($existing) {
                $table->where('email', $email)->update($payload);
            } else {
                $payload['created_at'] = $now;
                $table->insert($payload);
            }

            return back()
                ->with('newsletter_flash', __("Merci ! Votre inscription à l'infolettre GrimbaNews est enregistrée."))
                ->withFragment('newsletter');
        })->name('public.newsletter.subscribe');

        Route::get('methodologie', function () {
            SeoHelper::setTitle(__('Méthodologie') . ' — GrimbaNews')
                ->setDescription(__('Comment GrimbaNews classe les biais, repère les angles morts et note la crédibilité des sources.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Méthodologie'), url('/methodologie'));

            return Theme::scope('methodology', [])->render();
        })->name('public.methodology');

        // S317 — About page.
        Route::get('a-propos', function () {
            SeoHelper::setTitle(__('À propos') . ' — GrimbaNews')
                ->setDescription(__('GrimbaNews est une plateforme francophone qui rend visible le biais éditorial et les angles morts de l\'actualité.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('À propos'), url('/a-propos'));

            return Theme::scope('about', [])->render();
        })->name('public.about');

        // S318 — FAQ page.
        Route::get('faq', function () {
            SeoHelper::setTitle(__('Foire aux questions') . ' — GrimbaNews')
                ->setDescription(__('Questions fréquentes sur GrimbaNews : méthodologie, biais, NobuAI, vie privée et abonnement.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('FAQ'), url('/faq'));

            return Theme::scope('faq', [])->render();
        })->name('public.faq');

        // S350 — per-page OG image generator for static editorial pages.
        Route::get('og/page', [\App\Http\Controllers\GrimbaPageOgController::class, 'show'])
            ->name('public.og.page');

        // S312 — Bias-bar explainer page. Standalone "how to read the bar"
        // surface (the methodology page covers everything; this one drills
        // into just the coverage bar with edge cases + edition convention).
        Route::get('comprendre-le-barometre', function () {
            SeoHelper::setTitle(__('Comprendre le baromètre de couverture') . ' — GrimbaNews')
                ->setDescription(__('Comment lire le baromètre de couverture (gauche/centre/droite), pourquoi nous gardons la convention francophone et comment nous traitons les cas limites.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Comprendre le baromètre'), url('/comprendre-le-barometre'));

            return Theme::scope('explainer-bias-bar', [])->render();
        })->name('public.bias-bar-explainer');

        // S168 — member dashboard hijack. Botble Member plugin's
        // dashboard ships an admin-style sidebar layout (built for
        // sites where members write blog posts). GrimbaNews members
        // are READERS, so the published-posts/draft/pending widgets
        // are noise. Our route returns a Steve-styled "Mon compte"
        // landing — bias-mix widget + 4 action cards + logout.
        Route::get('account', function () {
            $user = auth('member')->user();
            if (! $user) {
                return redirect(route('public.member.login'));
            }
            $vaultIds = GrimbaVault::parseIds((string) request()->cookie(GrimbaVault::COOKIE, ''));
            $savedSearches = GrimbaSavedSearches::forMember($user);

            SeoHelper::setTitle(__('Mon compte') . ' — GrimbaNews');
            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Mon compte'), url('/account'));

            return Theme::scope('account', compact('user', 'vaultIds', 'savedSearches'))->render();
        })->name('public.account');

        Route::post('account/vault-digest', function (Request $request) {
            $user = auth('member')->user();
            if (! $user) {
                return redirect(route('public.member.login'));
            }

            $enabled = $request->boolean('weekly_vault_digest');
            $ids = GrimbaVault::parseIds((string) $request->cookie(GrimbaVault::COOKIE, ''));

            GrimbaVault::syncMemberDigestSnapshot($user, $ids, $enabled);

            return redirect(url('/account'))->with(
                'status',
                $enabled
                    ? __('Digest coffre hebdomadaire activé.')
                    : __('Digest coffre hebdomadaire désactivé.')
            );
        })->middleware('member')->name('public.account.vault-digest');

        Route::delete('account/saved-searches/{id}', function (int $id) {
            $user = auth('member')->user();
            if (! $user) {
                return redirect(route('public.member.login'));
            }

            GrimbaSavedSearches::deleteForMember($user, $id);

            return redirect(url('/account'))->with('status', __('Alerte recherche supprimée.'));
        })->middleware('member')->where('id', '[0-9]+')->name('public.saved-searches.destroy');

        // S167 — Local news. Reads grimba_local_city + _country
        // cookies, falls back to IP geolocation via GrimbaGeoLocator
        // (no key required — ip-api.com / ipapi.co cascade), then
        // filters posts: source country match + city keyword scan.
        // Cookies-only flow respects the consent banner: we only fire
        // the IP lookup when the visitor has no manually-set location.
        Route::get('local', function (Request $request) {
            $city    = trim((string) $request->cookie('grimba_local_city', ''));
            $country = trim((string) $request->cookie('grimba_local_country', ''));
            $cc      = trim((string) $request->cookie('grimba_local_cc', ''));
            $detected = false;

            if ($city === '' && $country === '') {
                $geo = app(\App\Services\GrimbaGeoLocator::class)->locate((string) $request->ip());
                if ($geo) {
                    $city    = $geo['city'];
                    $country = $geo['country'];
                    $cc      = $geo['country_code'];
                    $detected = true;
                }
            }

            $posts = collect();
            if ($cc !== '' || $city !== '') {
                $q = Post::query()
                    ->where('status', 'published')
                    ->tap(fn ($q) => GnTr::orderForTargetLocale($q));

                if ($cc !== '') {
                    $q->whereIn('source_id', function ($sub) use ($cc): void {
                        $sub->select('id')->from('news_sources')
                            ->where('country', mb_strtoupper($cc));
                    });
                }
                if ($city !== '') {
                    $needle = '%' . $city . '%';
                    $q->where(function ($w) use ($needle): void {
                        $w->where('name', 'like', $needle)
                          ->orWhere('description', 'like', $needle);
                    });
                }

                $posts = $q->limit(36)->get();
            }

            SeoHelper::setTitle(($city ?: $country ?: __('Local')) . ' — GrimbaNews')
                ->setDescription(__('Actualité locale, sourcée et croisée.'));
            Theme::set('grimba_og_image', url('/og/local.png'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Local'), url('/local'));

            return Theme::scope('local', [
                'city'     => $city,
                'country'  => $country,
                'cc'       => $cc,
                'detected' => $detected,
                'posts'    => $posts,
            ])->render();
        })->name('public.local');

        // S167 — POST endpoint to persist a manually-entered location.
        Route::post('local/set', function (Request $request) {
            $city    = trim((string) $request->input('city',    ''));
            $country = trim((string) $request->input('country', ''));
            $cc      = trim((string) $request->input('cc',      ''));

            $resp = response()->json(['ok' => true, 'city' => $city, 'country' => $country, 'cc' => $cc]);
            $oneYear = 60 * 24 * 365;
            $resp->cookie('grimba_local_city',    $city,    $oneYear, '/', null, false, false);
            $resp->cookie('grimba_local_country', $country, $oneYear, '/', null, false, false);
            $resp->cookie('grimba_local_cc',      mb_strtoupper($cc), $oneYear, '/', null, false, false);
            return $resp;
        })->name('public.local.set');

        // S156 — ownership map page. Aggregates news_sources by
        // owner_name, ranks by # of outlets controlled, surfaces
        // multi-bias owners (single owner controlling outlets across
        // the spectrum) with a chip.
        Route::get('proprietaires', function () {
            $sources = \Illuminate\Support\Facades\DB::table('news_sources')
                ->whereNotNull('owner_name')
                ->where('owner_name', '!=', '')
                ->orderBy('name')
                ->get(['id','name','slug','owner_name','bias_rating','country','credibility_score','website','logo_url','logo_status','logo_checked_at']);

            $owners = $sources
                ->groupBy('owner_name')
                ->map(fn ($group, $name) => [
                    'name'    => $name,
                    'sources' => $group,
                ])
                ->sortByDesc(fn ($o) => count($o['sources']))
                ->values();

            SeoHelper::setTitle(__('Qui possède quoi') . ' — GrimbaNews')
                ->setDescription(__('Carte de la concentration des médias suivis par GrimbaNews.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Sources'), url('/sources'))
                ->add(__('Propriétaires'), url('/proprietaires'));

            return Theme::scope('owners', [
                'owners'       => $owners,
                'totalOwners'  => $owners->count(),
                'totalSources' => $sources->count(),
            ])->render();
        })->name('public.owners');

        Route::get('sources', function () {
            $publishedExpr = GrimbaPostRecency::expression();
            $activity = DB::table('posts')
                ->selectRaw('source_id, COUNT(*) as article_count, COUNT(DISTINCT story_cluster_id) as cluster_count, MAX(' . $publishedExpr . ') as last_published_at')
                ->selectRaw("COUNT(DISTINCT CASE WHEN {$publishedExpr} >= ? THEN story_cluster_id END) as recent_cluster_count", [now()->subDays(30)])
                ->where('status', 'published')
                ->whereNotNull('source_id')
                ->groupBy('source_id');

            $rows = DB::table('news_sources')
                ->leftJoinSub($activity, 'activity', function ($join): void {
                    $join->on('activity.source_id', '=', 'news_sources.id');
                })
                ->orderBy('credibility_score', 'desc')
                ->orderByDesc('recent_cluster_count')
                ->orderBy('name')
                ->get([
                    'news_sources.*',
                    DB::raw('COALESCE(activity.article_count, 0) as article_count'),
                    DB::raw('COALESCE(activity.cluster_count, 0) as cluster_count'),
                    DB::raw('COALESCE(activity.recent_cluster_count, 0) as recent_cluster_count'),
                    'activity.last_published_at',
                ]);

            $grouped = $rows->groupBy(fn ($r) => in_array($r->bias_rating, ['left','center','right']) ? $r->bias_rating : 'unknown');

            SeoHelper::setTitle(__('Sources classées') . ' — GrimbaNews')
                ->setDescription(__('Biais, propriété, crédibilité et origine des sources suivies.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Sources'), url('/sources'));

            return Theme::scope('sources', [
                'grouped' => $grouped,
                'total'   => $rows->count(),
            ])->render();
        })->name('public.sources');

        // S111 — per-source page. Editorial outlet card + actual
        // bias distribution from the source's published-article
        // archive + recent articles grid. Slug column added by the
        // 2026_04_25 migration.
        Route::get('sources/{slug}', function (string $slug) {
            $source = \Illuminate\Support\Facades\DB::table('news_sources')
                ->where('slug', $slug)
                ->first();

            abort_if(! $source, 404);

            $stats = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0, 'total' => 0];
            \Illuminate\Support\Facades\DB::table('posts')
                ->where('source_id', $source->id)
                ->where('status', 'published')
                ->select('bias_rating', \Illuminate\Support\Facades\DB::raw('count(*) as c'))
                ->groupBy('bias_rating')
                ->get()
                ->each(function ($row) use (&$stats): void {
                    $b = in_array($row->bias_rating, ['left','center','right'], true) ? $row->bias_rating : 'unknown';
                    $stats[$b] += (int) $row->c;
                    $stats['total'] += (int) $row->c;
                });

            $posts = Post::query()
                ->where('source_id', $source->id)
                ->where('status', 'published')
                ->tap(fn ($q) => GnTr::orderForTargetLocale($q))
                ->paginate(12);

            SeoHelper::setTitle($source->name . ' — GrimbaNews')
                ->setDescription(__(':source — biais déclaré :bias. Couverture archivée par GrimbaNews.', [
                    'source' => $source->name,
                    'bias' => $source->bias_rating ?? __('non classé'),
                ]));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Sources'), url('/sources'))
                ->add($source->name, url('/sources/' . $source->slug));

            return Theme::scope('source', [
                'source' => $source,
                'posts'  => $posts,
                'stats'  => $stats,
            ])->render();
        })->where('slug', '[a-z0-9\-]+')->name('public.source');

        // S173 — saved-for-later vault. Cookie-only (no auth required).
        // Reads grimba_vault CSV (CSV of post ids, last-saved-first,
        // capped at 50 by client JS) and renders the saved articles.
        Route::get('coffre', function (Request $request) {
            $ids = GrimbaVault::parseIds((string) $request->cookie(GrimbaVault::COOKIE, ''));
            if ($ids !== []) {
                GrimbaVaultEvents::recordReturnVisit($request);
            }

            $posts = GrimbaVault::resolvePosts($ids);
            $staleIds = GrimbaVault::staleIds($ids, $posts);

            if ($staleIds !== []) {
                $clean = $posts->pluck('id')->map(static fn ($id): int => (int) $id)->all();

                if ($clean === []) {
                    Cookie::queue(Cookie::forget(GrimbaVault::COOKIE, '/'));
                } else {
                    Cookie::queue(GrimbaVault::COOKIE, GrimbaVault::serializeIds($clean), 60 * 24 * 365, '/', null, false, false, false, 'Lax');
                }
            }

            SeoHelper::setTitle(__('Mon coffre') . ' — GrimbaNews')
                ->setDescription(__('Articles sauvegardés pour plus tard.'));
            Theme::set('grimba_og_image', url('/og/coffre.png'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Mon coffre'), url('/coffre'));

            return Theme::scope('coffre', [
                'posts' => $posts,
                'count' => $posts->count(),
                'staleCount' => count($staleIds),
            ])->render();
        })->name('public.coffre');

        Route::post('coffre/toggle', function (Request $request) {
            $validator = Validator::make($request->all(), [
                'post_id' => ['required', 'integer', 'min:1'],
                'action' => ['nullable', 'string', 'in:toggle,save,unsave'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false,
                    'message' => __('Requête de coffre invalide.'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $postId = (int) $request->input('post_id');
            $exists = Post::query()
                ->whereKey($postId)
                ->where('status', 'published')
                ->exists();

            if (! $exists) {
                return response()->json([
                    'ok' => false,
                    'message' => __('Article introuvable.'),
                ], 404);
            }

            $ids = GrimbaVault::parseIds((string) $request->cookie(GrimbaVault::COOKIE, ''));
            $action = (string) $request->input('action', 'toggle');
            $wasSaved = in_array($postId, $ids, true);

            if ($action === 'toggle') {
                $action = $wasSaved ? 'unsave' : 'save';
            }

            $ids = array_values(array_filter(
                $ids,
                static fn (int $id): bool => $id !== $postId
            ));

            $saved = $action === 'save';
            if ($saved) {
                array_unshift($ids, $postId);
            }

            $ids = GrimbaVault::parseIds(implode(',', $ids));
            $value = GrimbaVault::serializeIds($ids);
            GrimbaVaultEvents::record($request, $saved ? 'save' : 'unsave', $postId);
            GrimbaVault::syncMemberDigestSnapshot(auth('member')->user(), $ids);

            return response()
                ->json([
                    'ok' => true,
                    'saved' => $saved,
                    'ids' => $ids,
                    'count' => count($ids),
                ])
                ->cookie(GrimbaVault::COOKIE, $value, 60 * 24 * 365, '/', null, false, false, false, 'Lax');
        })->name('public.coffre.toggle');

        Route::get('coffre/partager', function (Request $request) {
            $ids = GrimbaVault::parseIds((string) $request->cookie(GrimbaVault::COOKIE, ''));
            $shareUrl = url('/coffre/depuis-lien') . '#ids=' . GrimbaVault::serializeIds($ids);

            SeoHelper::setTitle(__('Partager mon coffre') . ' — GrimbaNews')
                ->setDescription(__('Créer un lien local pour partager votre sélection sauvegardée.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Mon coffre'), url('/coffre'))
                ->add(__('Partager'), url('/coffre/partager'));

            return Theme::scope('coffre-share', [
                'mode' => 'share',
                'count' => count($ids),
                'shareUrl' => $shareUrl,
            ])->render();
        })->name('public.coffre.share');

        Route::get('coffre/depuis-lien', function () {
            SeoHelper::setTitle(__('Importer un coffre') . ' — GrimbaNews')
                ->setDescription(__('Importer une sélection GrimbaNews partagée.'));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Mon coffre'), url('/coffre'))
                ->add(__('Depuis un lien'), url('/coffre/depuis-lien'));

            return Theme::scope('coffre-share', [
                'mode' => 'import',
                'count' => 0,
                'shareUrl' => '',
            ])->render();
        })->name('public.coffre.import');

        // S182 — vault CSV export. Cookie-only data; mirror of
        // /pour-vous/export.csv. Hydrates titles/sources/bias from the
        // grimba_vault id list and streams a CSV. Empty cookie → CSV
        // with header only.
        Route::get('coffre/export.csv', function (Request $request) {
            $ids = GrimbaVault::parseIds((string) $request->cookie(GrimbaVault::COOKIE, ''));

            $rows = collect();
            if (! empty($ids)) {
                $postColumns = ['id', 'name', 'bias_rating', 'source_name', 'created_at'];
                if (Schema::hasColumn('posts', 'published_at')) {
                    $postColumns[] = 'published_at';
                }

                $byId = Post::query()
                    ->whereIn('id', $ids)
                    ->where('status', 'published')
                    ->get($postColumns)
                    ->keyBy('id');

                foreach ($ids as $i => $id) {
                    if (! isset($byId[$id])) continue;
                    $p = $byId[$id];
                    $rows->push([
                        'rank'        => $i + 1,
                        'post_id'     => (int) $p->id,
                        'title'       => (string) $p->name,
                        'source'      => (string) ($p->source_name ?? ''),
                        'bias'        => (string) ($p->bias_rating ?? 'unknown'),
                        'published_at'=> optional(GrimbaPostRecency::value($p))->toDateString() ?? '',
                    ]);
                }
            }

            $filename = 'grimbanews-coffre-' . now()->format('Y-m-d') . '.csv';

            return response()->streamDownload(function () use ($rows) {
                $h = fopen('php://output', 'w');
                fwrite($h, "\xEF\xBB\xBF");
                fputcsv($h, ['rang', 'post_id', 'titre', 'source', 'biais', 'publie_le']);
                foreach ($rows as $r) {
                    fputcsv($h, [
                        $r['rank'], $r['post_id'], $r['title'],
                        $r['source'], $r['bias'], $r['published_at'],
                    ]);
                }
                fclose($h);
            }, $filename, [
                'Content-Type'  => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-store, max-age=0',
                'X-GN-Privacy'  => 'cookie-only-no-server-record',
            ]);
        })->name('public.coffre.export');

        Route::get('angles-morts', function (Request $request) {
            $clusterId = (int) $request->query('cluster', 0);

            // S315 — bias-side filter. ?for=left → blindspot AND covered
            // mostly by left-leaning sources (= right blindspot). Inverse
            // for ?for=right. ?for=all (default) shows all.
            $for = (string) $request->query('for', 'all');
            if (! in_array($for, ['all', 'left', 'right'], true)) {
                $for = 'all';
            }

            $posts = Post::query()
                ->where('is_blindspot', true)
                ->where('status', 'published')
                ->when($for !== 'all', static function ($query) use ($for): void {
                    // The post's bias_rating is the dominant covering side.
                    // A blindspot "for the left" means right-leaning sources
                    // dominate it (so left has nothing) — bias_rating='right'.
                    // A blindspot "for the right" means left-leaning sources
                    // dominate it — bias_rating='left'.
                    $query->where('bias_rating', $for === 'left' ? 'right' : 'left');
                })
                ->when($clusterId > 0, static function ($query) use ($clusterId): void {
                    $query->orderByRaw('CASE WHEN story_cluster_id = ? THEN 0 ELSE 1 END', [$clusterId]);
                })
                ->tap(fn ($q) => GnTr::orderForTargetLocale($q))
                ->paginate(12)
                ->appends($request->only(['for', 'cluster']));

            SeoHelper::setTitle(__('Angles morts') . ' — GrimbaNews')
                ->setDescription(__("Les histoires qu'un seul camp couvre."));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Angles morts'), url('/angles-morts'));

            return Theme::scope('blindspot', [
                'posts' => $posts,
                'focusClusterId' => $clusterId,
                'forFilter' => $for,
            ])->render();
        })->name('public.blindspot');

        Route::group([
            'prefix' => 'ajax',
            'as' => 'public.ajax.',
            'middleware' => RequiresJsonRequestMiddleware::class,
            'controller' => EchoController::class,
        ], function (): void {
            Route::get('categories/{categoryId}/posts', 'ajaxGetPostByCategory')
                ->name('posts-by-category');

            Route::get('shortcode-blog-posts', 'ajaxShortcodeBlogPosts')
                ->name('shortcode-blog-posts');

            Route::get('shortcode-blog-categories', 'ajaxShortcodeBlogCategories')
                ->name('shortcode-blog-categories');

            Route::get('widget-blog-posts', 'ajaxWidgetBlogPosts')
                ->name('widget-blog-posts');

            Route::get('widget-blog-categories', 'ajaxWidgetBlogCategories')
                ->name('widget-blog-categories');

            Route::get('widget-breaking-news', 'ajaxWidgetBreakingNews')
                ->name('widget-breaking-news');

            Route::get('menu-sidebar', 'ajaxMenuSidebar')
                ->name('menu-sidebar');
        });
    });
});

Theme::routes();
