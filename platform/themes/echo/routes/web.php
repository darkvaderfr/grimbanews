<?php

use App\Support\GrimbaTranslationPresenter as GnTr;
use App\Support\GrimbaVault;
use Botble\Base\Http\Middleware\RequiresJsonRequestMiddleware;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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

            SeoHelper::setTitle(__('Comparer les sources') . ' — GrimbaNews')
                ->setDescription(__("Tous les dossiers en cours — chaque histoire vue sous plusieurs angles."));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Comparer les sources'), url('/comparatif'));

            return Theme::scope('comparison-index', compact('clusters'))->render();
        })->name('public.comparison.index');

        Route::get('comparatif/{clusterId}', function (int $clusterId) {
            $posts = Post::query()
                ->where('story_cluster_id', $clusterId)
                ->where('status', 'published')
                ->tap(fn ($q) => GnTr::orderForTargetLocale($q))
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
            $posts = Post::query()
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

        // S213 — constrained image proxy for outlet logos. Keeps
        // reader cards off third-party logo hosts and lets Laravel/CDN
        // cache long-tail source icons. Deliberately limited to the two
        // logo providers used by the source-logo partial, so it cannot
        // become a general-purpose open proxy.
        Route::get('img-proxy', function (Request $request) {
            $url = (string) $request->query('u', '');
            $parts = parse_url($url);
            $host = strtolower((string) ($parts['host'] ?? ''));

            abort_unless(
                in_array($parts['scheme'] ?? '', ['http', 'https'], true)
                && in_array($host, ['logo.clearbit.com', 'www.google.com'], true),
                404
            );

            $cachePath = storage_path('app/public/img-proxy/' . sha1($url) . '.bin');
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

                    \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($cachePath));
                    \Illuminate\Support\Facades\File::put($cachePath, $res->body());
                    \Illuminate\Support\Facades\File::put($metaPath, strtok($type, ';') ?: 'image/png');
                } catch (\Throwable) {
                    abort(404);
                }
            }

            $type = \Illuminate\Support\Facades\File::exists($metaPath)
                ? trim((string) \Illuminate\Support\Facades\File::get($metaPath))
                : 'image/png';

            return response(\Illuminate\Support\Facades\File::get($cachePath), 200, [
                'Content-Type' => $type ?: 'image/png',
                'Cache-Control' => 'public, max-age=604800, s-maxage=604800',
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
                        $query->whereDate('posts.created_at', '>=', $fromDate);
                    }
                    if ($toDate !== '') {
                        $query->whereDate('posts.created_at', '<=', $toDate);
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
            ])->render();
        };

        Route::get('search', $searchHandler)->name('public.grimba-search');

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

        Route::post('region/set', function (Request $request) {
            // S146 — six audience regions aligned with the picker
            // (france / uk / us / canada / africa / international).
            $region = (string) $request->input('region', 'international');
            $allowed = ['france', 'uk', 'us', 'canada', 'africa', 'international'];
            if (! in_array($region, $allowed, true)) {
                $region = 'international';
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
                    ->where('posts.created_at', '>=', now()->subDays(14))
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
                            ->where('posts.created_at', '>=', now()->subDays(14));
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
            SeoHelper::setTitle(__('Mon compte') . ' — GrimbaNews');
            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Mon compte'), url('/account'));
            return Theme::scope('account', compact('user'))->render();
        })->name('public.account');

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
                ->get(['name','slug','owner_name','bias_rating','country','credibility_score','website']);

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
            $activity = DB::table('posts')
                ->selectRaw('source_id, COUNT(*) as article_count, COUNT(DISTINCT story_cluster_id) as cluster_count, MAX(created_at) as last_published_at')
                ->selectRaw("COUNT(DISTINCT CASE WHEN created_at >= ? THEN story_cluster_id END) as recent_cluster_count", [now()->subDays(30)])
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

            $posts = Post::query()
                ->where('is_blindspot', true)
                ->where('status', 'published')
                ->when($clusterId > 0, static function ($query) use ($clusterId): void {
                    $query->orderByRaw('CASE WHEN story_cluster_id = ? THEN 0 ELSE 1 END', [$clusterId]);
                })
                ->tap(fn ($q) => GnTr::orderForTargetLocale($q))
                ->paginate(12);

            SeoHelper::setTitle(__('Angles morts') . ' — GrimbaNews')
                ->setDescription(__("Les histoires qu'un seul camp couvre."));

            Theme::breadcrumb()
                ->add(__('Accueil'), url('/'))
                ->add(__('Angles morts'), url('/angles-morts'));

            return Theme::scope('blindspot', [
                'posts' => $posts,
                'focusClusterId' => $clusterId,
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
