<?php

use Botble\Base\Http\Middleware\RequiresJsonRequestMiddleware;
use Botble\Blog\Models\Post;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Theme\Echo\Http\Controllers\EchoController;

Route::group(['middleware' => ['web', 'core']], function (): void {
    Theme::registerRoutes(function (): void {
        Route::get('comparatif', function () {
            $clusters = DB::table('story_clusters')
                ->orderByDesc('id')
                ->get()
                ->map(function ($c) {
                    $rows = DB::table('posts')
                        ->where('story_cluster_id', $c->id)
                        ->where('status', 'published')
                        ->get(['id', 'name', 'bias_rating', 'source_name', 'image', 'created_at']);

                    $counts = ['left' => 0, 'center' => 0, 'right' => 0];
                    foreach ($rows as $r) {
                        if (isset($counts[$r->bias_rating])) $counts[$r->bias_rating]++;
                    }
                    $c->posts    = $rows;
                    $c->total    = $rows->count();
                    $c->counts   = $counts;
                    $c->latestAt = $rows->max('created_at');
                    return $c;
                })
                ->filter(fn ($c) => $c->total > 0)
                ->values();

            SeoHelper::setTitle('Comparer les sources — GrimbaNews')
                ->setDescription("Tous les dossiers en cours — chaque histoire vue sous plusieurs angles.");

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Comparer les sources', url('/comparatif'));

            return Theme::scope('comparison-index', compact('clusters'))->render();
        })->name('public.comparison.index');

        Route::get('comparatif/{clusterId}', function (int $clusterId) {
            $posts = Post::query()
                ->where('story_cluster_id', $clusterId)
                ->where('status', 'published')
                ->orderByRaw("CASE bias_rating WHEN 'left' THEN 1 WHEN 'center' THEN 2 WHEN 'right' THEN 3 ELSE 4 END")
                ->get();

            $storyTitle = $posts->first()->name ?? ('Dossier #' . $clusterId);

            SeoHelper::setTitle('Comparaison des sources — ' . $storyTitle)
                ->setDescription('Comparez comment les médias couvrent la même histoire.');

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Comparaison', url('/comparatif/' . $clusterId));

            return Theme::scope('comparison', [
                'posts'      => $posts,
                'storyTitle' => $storyTitle,
                'clusterId'  => $clusterId,
            ])->render();
        })->name('public.comparison');

        $feedHandler = function () {
            $posts = Post::query()
                ->where('status', 'published')
                ->latest()
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
        Route::get('og/home.png', [\App\Http\Controllers\GrimbaOgImageController::class, 'home'])->name('public.og.home');
        Route::get('og/home',     [\App\Http\Controllers\GrimbaOgImageController::class, 'home'])->name('public.og.home.alt');

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

        // GrimbaNews /search — SQLite FTS5 with source + bias facets.
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

                    // Preserve the BM25 ordering from the FTS result.
                    $idOrder = implode(',', array_map('intval', $ids));
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

            SeoHelper::setTitle(($q !== '' ? "Recherche : {$q}" : 'Recherche') . ' — GrimbaNews')
                ->setDescription('Explorez les articles, sources et dossiers de GrimbaNews.');

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Recherche', url('/search'));

            return Theme::scope('search', [
                'posts'            => $posts,
                'availableSources' => $availableSources,
                'selectedSource'   => $sourceId,
                'selectedBias'     => $bias,
            ])->render();
        };

        Route::get('search', $searchHandler)->name('public.grimba-search');

        Route::post('translate/set', function (Request $request) {
            $mode = $request->input('mode');
            $allowed = ['original', 'auto', 'both'];
            if (! in_array($mode, $allowed, true)) {
                $mode = 'original';
            }

            return response()
                ->json(['ok' => true, 'mode' => $mode])
                ->cookie('grimba_translate', $mode, 60 * 24 * 365, '/', null, false, false);
        })->name('public.translate.set');

        Route::post('lang/set', function (Request $request) {
            $lang = $request->input('lang') === 'en' ? 'en' : 'fr';
            return response()
                ->json(['ok' => true, 'lang' => $lang])
                ->cookie('grimba_lang', $lang, 60 * 24 * 365, '/', null, false, false);
        })->name('public.lang.set');

        Route::post('region/set', function (Request $request) {
            $region = (string) $request->input('region', 'monde');
            $allowed = ['monde', 'afrique', 'europe', 'france', 'international'];
            if (! in_array($region, $allowed, true)) {
                $region = 'monde';
            }

            return response()
                ->json(['ok' => true, 'region' => $region])
                ->cookie('grimba_region', $region, 60 * 24 * 365, '/', null, false, false);
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

            $postsQuery = Post::query()
                ->where('status', 'published')
                ->latest();

            if (! empty($ids)) {
                $postsQuery->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
            }

            $posts = $postsQuery->paginate(12);

            SeoHelper::setTitle('Pour vous — GrimbaNews')
                ->setDescription("Votre fil personnalisé selon les sujets que vous suivez.");

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Pour vous', url('/pour-vous'));

            return Theme::scope('for-you', [
                'posts'         => $posts,
                'followedIds'   => $ids,
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
                $byId = Post::query()
                    ->whereIn('id', $ids)
                    ->where('status', 'published')
                    ->get(['id', 'name', 'bias_rating', 'source_name', 'created_at'])
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
                        'published_at'=> optional($p->created_at)->toDateString() ?? '',
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

            $payload = [
                'email'      => $email,
                'locale'     => $locale,
                'source_key' => $data['source_key'] ?? 'unknown',
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
                ->with('newsletter_flash', 'Merci ! Votre inscription à l\'infolettre GrimbaNews est enregistrée.')
                ->withFragment('newsletter');
        })->name('public.newsletter.subscribe');

        Route::get('methodologie', function () {
            SeoHelper::setTitle('Méthodologie — GrimbaNews')
                ->setDescription("Comment GrimbaNews classe les biais, repère les angles morts et note la crédibilité des sources.");

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Méthodologie', url('/methodologie'));

            return Theme::scope('methodology', [])->render();
        })->name('public.methodology');

        Route::get('sources', function () {
            $rows = \Illuminate\Support\Facades\DB::table('news_sources')
                ->orderBy('credibility_score', 'desc')
                ->orderBy('name')
                ->get();

            $grouped = $rows->groupBy(fn ($r) => in_array($r->bias_rating, ['left','center','right']) ? $r->bias_rating : 'unknown');

            SeoHelper::setTitle('Sources classées — GrimbaNews')
                ->setDescription('Biais, propriété, crédibilité et origine des sources suivies.');

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Sources', url('/sources'));

            return Theme::scope('sources', [
                'grouped' => $grouped,
                'total'   => $rows->count(),
            ])->render();
        })->name('public.sources');

        Route::get('angles-morts', function () {
            $posts = Post::query()
                ->where('is_blindspot', true)
                ->where('status', 'published')
                ->latest()
                ->paginate(12);

            SeoHelper::setTitle('Angles morts — GrimbaNews')
                ->setDescription("Les histoires qu'un seul camp couvre.");

            Theme::breadcrumb()
                ->add('Accueil', url('/'))
                ->add('Angles morts', url('/angles-morts'));

            return Theme::scope('blindspot', compact('posts'))->render();
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
