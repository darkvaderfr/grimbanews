<?php

namespace App\Support;

use Botble\Blog\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * GrimbaHomeFeed
 *
 * Single allocator that hands each home section a disjoint slice of recent
 * posts. Before this class, every home partial ran an independent query —
 * the same article surfaced in Daily Briefing, Hero Grid, Most Read, Top
 * News, Section Blocks, and Latest at once (Vader: "redundant").
 *
 * Allocation order matters: the most editorial-intent sections claim
 * posts first, leaving the rest of the pool for the latest list at the
 * bottom. A soft per-source cap also runs across the whole allocation so
 * one publisher cannot dominate the home page.
 */
class GrimbaHomeFeed
{
    private const CACHE_TTL_SECONDS = 180;

    private const SOURCE_CAP_PRIMARY = 2;

    private const SOURCE_CAP_LATEST = 3;

    /** @var array<string, mixed>|null */
    private static ?array $cached = null;

    public static function flush(): void
    {
        self::$cached = null;
        Cache::forget(self::cacheKey());
    }

    public static function build(): array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        // Cache::lock blocks a stampede when many fastcgi workers all
        // miss the cache at once — without it, every miss runs the full
        // 25-ish-query allocator. Fall back to a plain remember() when
        // the configured cache driver doesn't support atomic locks
        // (e.g. file / array in dev fixtures).
        $key = self::cacheKey();
        $lockSupported = method_exists(Cache::store(), 'lock');

        if ($lockSupported) {
            $lock = Cache::store()->lock($key . ':lock', 6);
            try {
                $lock->block(2);

                return self::$cached = Cache::remember(
                    $key,
                    self::CACHE_TTL_SECONDS,
                    static fn () => self::compose()
                );
            } catch (\Throwable) {
                // If the lock blocks too long, fall through to a
                // non-locked resolve rather than throwing back to the
                // request — readers shouldn't see a 500 because the
                // cache lock was contended.
            } finally {
                if (isset($lock) && method_exists($lock, 'release')) {
                    @$lock->release();
                }
            }
        }

        return self::$cached = Cache::remember(
            $key,
            self::CACHE_TTL_SECONDS,
            static fn () => self::compose()
        );
    }

    public static function briefing(): ?array
    {
        return self::build()['briefing'];
    }

    public static function allSides(): array
    {
        return self::build()['allSides'];
    }

    public static function hero(): ?Post
    {
        return self::build()['hero'];
    }

    public static function heroBriefingColumn(): Collection
    {
        return self::build()['heroBriefingColumn'];
    }

    public static function heroBlindspots(): Collection
    {
        return self::build()['heroBlindspots'];
    }

    public static function heroStats(): Collection
    {
        return self::build()['heroStats'];
    }

    public static function mostRead(string $bias): Collection
    {
        return self::build()['mostRead'][$bias] ?? collect();
    }

    public static function topNews(): Collection
    {
        return self::build()['topNews'];
    }

    public static function sections(): array
    {
        return self::build()['sections'];
    }

    public static function latest(): Collection
    {
        return self::build()['latest'];
    }

    /**
     * @return array<string, Collection<int, Post>>
     */
    public static function regionalMix(): array
    {
        return self::build()['regionalMix'] ?? [];
    }

    /**
     * Resolve the active editorial region from the request cookie. Used
     * by both the allocator (to gate the regional-mix on International)
     * and partials (to conditionally render the mix rails).
     */
    public static function activeRegion(): string
    {
        return self::resolveRegionKey();
    }

    /**
     * Latest stream — Phase D-02. Symmetric sibling to breaking().
     * Returns the freshest N posts for the active region + locale, no
     * exclusions, configurable count. Cached separately so home rails,
     * S-LSAT-04 — resolve the active reader locale BEFORE the layout
     * runs. Order: `?lang=` query → `grimba_lang` cookie → framework
     * default. Always returns one of `fr` or `en`.
     *
     * Zen audit fix 2026-05-18: `request()->query('lang')` returns an
     * ARRAY when the URL contains `?lang[]=fr&lang[]=en` (Symfony
     * parses bracket-syntax automatically); `(string) [...]` would
     * throw `Array to string conversion`. Same risk for a multi-value
     * cookie header. `is_string()` guard makes the cast safe.
     */
    public static function resolveReaderLocale(): string
    {
        $rawQuery = request()->query('lang', '');
        $query = is_string($rawQuery) ? strtolower($rawQuery) : '';
        if ($query === 'fr' || $query === 'en') {
            return $query;
        }
        $rawCookie = request()->cookie('grimba_lang', '');
        $cookie = is_string($rawCookie) ? strtolower($rawCookie) : '';
        if ($cookie === 'fr' || $cookie === 'en') {
            return $cookie;
        }
        $fallback = strtolower(substr(app()->getLocale() ?: 'fr', 0, 2));
        return in_array($fallback, ['fr', 'en'], true) ? $fallback : 'fr';
    }

    /**
     * Cached "latest" stream used by the hero rail, /latest route,
     * and any future surface. Strict mode (S-LSAT-04) drops posts
     * that have neither a native locale match nor a translation
     * available in the reader's locale.
     *
     * @return Collection<int, Post>
     */
    public static function latestStream(int $count = 14): Collection
    {
        $count = max(1, min(60, $count));
        $region = self::resolveRegionKey();
        // S-LSAT-04 (Vader 2026-05-18): resolve the reader locale
        // BEFORE the layout runs. `app()->getLocale()` returns the
        // framework default (`fr`) at this stage; the `?lang=` query
        // param + `grimba_lang` cookie are the load-bearing signals
        // for reader-language surfacing.
        $locale = self::resolveReaderLocale();
        $strict = GrimbaLanguageSettings::strictForLatest();
        $bucket = $strict ? 'strict' : 'soft';

        return Cache::remember(
            'grimba_latest_stream_v2:' . $locale . ':' . $region . ':' . $bucket . ':' . $count,
            60,
            function () use ($count, $strict, $locale): Collection {
                $cols = ['id','name','translated_name','translated_description','translated_to','original_language','description','content','summary_nobuai','source_name','source_id','bias_rating','published_at','created_at','image','editorial_region'];

                $query = Post::query()
                    ->where('status', 'published')
                    ->whereNotNull('source_name')
                    ->with(['slugable', 'categories']);

                if ($strict) {
                    // Strict mode = native target locale OR has a target
                    // translation. Drops wrong-locale-no-translation +
                    // unclassified content. We pass the resolved reader
                    // locale explicitly because `app()->getLocale()` is
                    // still the framework default at this stage.
                    GrimbaTranslationPresenter::filterForTargetLocale($query, $locale);
                    GrimbaTranslationPresenter::orderForTargetLocale($query, $locale);
                } else {
                    GrimbaTranslationPresenter::orderForTargetLocale($query, $locale);
                }

                $posts = $query->limit($count)->get($cols);

                GrimbaTranslationPresenter::warm($posts);

                return $posts;
            }
        );
    }

    /**
     * Breaking-news stream — Phase D-01 of the architect plan.
     *
     * Strict keyword match on titles within an 18h recency window
     * (Vader 2026-05-17 hard cap). Falls back to recent posts when
     * nothing matches the breaking phrase set, so the rail still
     * moves — the caller receives a `mode` flag to render an honest
     * eyebrow ("En direct" vs "Dernières").
     *
     * Independent from the home-feed compose() pass — the ticker
     * runs every page paint and shouldn't block on the full allocator.
     *
     * @return array{mode: string, posts: Collection<int, Post>}
     */
    public static function breaking(int $windowHours = 18): array
    {
        $windowHours = max(1, min(72, $windowHours));
        $region = self::resolveRegionKey();
        $locale = self::resolveReaderLocale();
        $strict = GrimbaLanguageSettings::strictForBreaking();
        $bucket = $strict ? 'strict' : 'soft';

        $cacheKey = 'grimba_breaking_v2:' . $locale . ':' . $region . ':' . $bucket . ':' . $windowHours;

        return Cache::remember($cacheKey, 45, function () use ($windowHours, $strict, $locale): array {
            $cols = ['id','name','translated_name','translated_description','translated_to','original_language','description','content','summary_nobuai','source_name','source_id','bias_rating','published_at','created_at','image','editorial_region'];

            $keywords = self::breakingKeywords();
            $regex = self::breakingRegex($keywords);

            // Strict pass: SQL LIKE on TITLE fields only (descriptions
            // contain subscribe-prompt boilerplate that would pollute
            // the ticker with non-breaking coverage).
            $candidatesQuery = Post::query()
                ->where('status', 'published')
                ->whereNotNull('source_name')
                ->where(function ($q) use ($keywords): void {
                    foreach ($keywords as $kw) {
                        $like = '%' . $kw . '%';
                        $q->orWhere('name', 'like', $like)
                          ->orWhere('translated_name', 'like', $like);
                    }
                })
                ->with('slugable');

            // S-LSAT-04 — strict mode hides wrong-locale-no-translation
            // posts so a FR reader doesn't see EN-origin headlines (and
            // vice versa) on the breaking ticker.
            if ($strict) {
                GrimbaTranslationPresenter::filterForTargetLocale($candidatesQuery, $locale);
            }

            GrimbaPostRecency::wherePublishedSince($candidatesQuery, now()->subHours(24));
            GrimbaPostRecency::orderByPublished($candidatesQuery);

            $candidates = $candidatesQuery->limit(40)->get($cols);

            $breaking = $candidates->filter(function ($post) use ($regex): bool {
                $haystack = mb_strtolower(trim(
                    (string) ($post->name ?? '') . ' ' . (string) ($post->translated_name ?? '')
                ));
                return (bool) preg_match($regex, $haystack);
            })->take(14);

            if ($breaking->isNotEmpty()) {
                return ['mode' => 'real', 'posts' => $breaking->values()];
            }

            // Fallback: freshest posts in the window so the rail still
            // moves. Strict mode applies here too so the fallback
            // doesn't silently undo the locale filter when breaking
            // matches are empty.
            $recent = Post::query()
                ->where('status', 'published')
                ->whereNotNull('source_name')
                ->with('slugable');
            if ($strict) {
                GrimbaTranslationPresenter::filterForTargetLocale($recent);
            }
            GrimbaPostRecency::wherePublishedSince($recent, now()->subHours($windowHours));
            GrimbaPostRecency::orderByPublished($recent);

            $picked = $recent->limit(14)->get($cols);
            if ($picked->isNotEmpty()) {
                return ['mode' => 'latest', 'posts' => $picked];
            }

            // Last-resort fill: drop the recency window entirely.
            $any = Post::query()
                ->where('status', 'published')
                ->with('slugable');
            GrimbaPostRecency::orderByPublished($any);

            return ['mode' => 'latest', 'posts' => $any->limit(10)->get($cols)];
        });
    }

    /**
     * Multi-word editorial phrases that qualify as "real" breaking
     * news. Single ambiguous words ("urgent", "fire", "breaking")
     * deliberately excluded — too liberal, "ground-breaking" and
     * "urgent refresh" match them.
     *
     * @return array<int, string>
     */
    private static function breakingKeywords(): array
    {
        return [
            'breaking news', 'breaking:', 'just in:', 'live updates',
            'state of emergency', 'declared dead', 'evacuation order',
            'mass casualty', 'death toll', 'massive explosion',
            'en direct', 'dernière minute', 'flash info', 'alerte info',
            'alerte enlèvement', "état d'urgence", 'urgent :', 'urgent –',
            'plan blanc', 'attentat', 'sous les décombres',
        ];
    }

    /**
     * @param  array<int, string>  $keywords
     */
    private static function breakingRegex(array $keywords): string
    {
        return '/(?:' .
            implode('|', array_map(fn ($kw) => preg_quote($kw, '/'), $keywords)) .
            ')/iu';
    }

    public static function isShown(int $postId): bool
    {
        return isset(self::build()['allShown'][$postId]);
    }

    private static function compose(): array
    {
        $state = new HomeFeedState(self::SOURCE_CAP_PRIMARY);

        // International is the only edition that gets the curated
        // regional mix at the top — Africa / Europe / Americas readers
        // already see only their region, so the mix would be redundant.
        $regionalMix = self::resolveRegionKey() === 'international'
            ? self::pickRegionalMix($state, 3)
            : [];

        $briefing = self::pickBriefing($state);
        $allSides = self::pickAllSides($state);
        $hero = self::pickHero($state);
        $heroBriefingColumn = self::pickHeroBriefingColumn($state, 5);
        $heroBlindspots = self::pickBlindspots($state, 2);
        $heroStats = self::pickRecentForStats(9);
        $mostRead = self::pickMostReadByBias($state, 4);
        $topNews = self::pickTopNewsInline($state, 6);
        $sections = self::pickSectionBlocks($state);
        $latest = self::pickLatest($state, 10);

        return [
            'regionalMix' => $regionalMix,
            'briefing' => $briefing,
            'allSides' => $allSides,
            'hero' => $hero,
            'heroBriefingColumn' => $heroBriefingColumn,
            'heroBlindspots' => $heroBlindspots,
            'heroStats' => $heroStats,
            'mostRead' => $mostRead,
            'topNews' => $topNews,
            'sections' => $sections,
            'latest' => $latest,
            'allShown' => $state->shown,
        ];
    }

    /**
     * Three top stories per named region, surfaced as mini-rails at
     * the top of the International home. Each pick is added to the
     * shown set so the rest of the page doesn't repeat them.
     *
     * @return array<string, Collection<int, Post>>
     */
    /** @var bool|null */
    private static ?bool $regionColumnCache = null;

    private static function pickRegionalMix(HomeFeedState $state, int $perRegion): array
    {
        $out = [];

        // Cache Schema::hasColumn since it hits information_schema each
        // call — once per request lifecycle is plenty (Zen 2026-05-16).
        if (self::$regionColumnCache === null) {
            self::$regionColumnCache = \Illuminate\Support\Facades\Schema::hasColumn('posts', 'editorial_region');
        }
        $hasColumn = self::$regionColumnCache;

        foreach (['africa', 'europe', 'americas'] as $region) {
            $query = Post::query()
                ->withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->whereNotIn('id', $state->shownIds() ?: [0])
                ->with('categories');

            if ($hasColumn) {
                $query->where('editorial_region', $region);
            } else {
                $countries = \App\Ground\Regions::countries($region) ?? [];
                if ($countries === []) {
                    $out[$region] = collect();
                    continue;
                }
                $query->whereIn('source_id', function ($q) use ($countries): void {
                    $q->select('id')
                      ->from('news_sources')
                      ->whereIn('country', $countries);
                });
            }

            $candidates = $query
                ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
                ->limit($perRegion * 4)
                ->get();

            $picked = collect();
            foreach ($candidates as $post) {
                if ($picked->count() >= $perRegion) {
                    break;
                }
                if ($state->take($post)) {
                    $picked->push($post);
                }
            }

            GrimbaTranslationPresenter::warm($picked);
            $out[$region] = $picked;
        }

        return $out;
    }

    private static function pickBriefing(HomeFeedState $state): ?array
    {
        $resolveClusters = static function ($since) {
            return DB::table('posts')
                ->where('status', 'published')
                ->whereNotNull('story_cluster_id')
                ->when($since, fn ($q) => GrimbaPostRecency::wherePublishedSince($q, $since))
                ->select('story_cluster_id', 'bias_rating', DB::raw('count(*) as c'))
                ->groupBy('story_cluster_id', 'bias_rating')
                ->get();
        };

        $rows = $resolveClusters(now()->subHours(36));
        if ($rows->isEmpty()) {
            $rows = $resolveClusters(null);
        }

        $clusters = [];
        foreach ($rows as $r) {
            $cid = (int) $r->story_cluster_id;
            $clusters[$cid]['total'] = ($clusters[$cid]['total'] ?? 0) + (int) $r->c;
            if (in_array($r->bias_rating, ['left', 'center', 'right'], true)) {
                $clusters[$cid]['sides'][$r->bias_rating] = (int) $r->c;
            }
        }

        $candidate = collect($clusters)
            ->filter(fn ($c) => ($c['total'] ?? 0) >= 3 && count($c['sides'] ?? []) >= 2)
            ->sortByDesc(fn ($c) => $c['total'])
            ->keys()
            ->first();

        if ($candidate === null) {
            return null;
        }

        $clusterId = (int) $candidate;

        $lead = Post::query()
            ->where('story_cluster_id', $clusterId)
            ->where('status', 'published')
            ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
            ->first();

        if (! $lead) {
            return null;
        }

        $state->take($lead);

        return [
            'post' => $lead,
            'clusterId' => $clusterId,
            'count' => $clusters[$clusterId]['total'] ?? 0,
            'sides' => $clusters[$clusterId]['sides'] ?? [],
        ];
    }

    /**
     * @return array<int, array{cluster_id: int, sides: int, articles: int, head: Post, counts: array<string,int>, image: ?string}>
     */
    private static function pickAllSides(HomeFeedState $state): array
    {
        $multiBiasClusters = DB::table('posts')
            ->whereNotNull('story_cluster_id')
            ->where('status', 'published')
            ->whereIn('bias_rating', ['left', 'center', 'right'])
            ->select(
                'story_cluster_id',
                DB::raw('COUNT(DISTINCT bias_rating) as sides'),
                DB::raw('COUNT(*) as articles'),
                DB::raw('MAX(' . GrimbaPostRecency::expression() . ') as latest')
            )
            ->groupBy('story_cluster_id')
            ->havingRaw('COUNT(DISTINCT bias_rating) >= 2')
            ->orderByDesc('sides')
            ->orderByDesc('latest')
            ->limit(12)
            ->get();

        if ($multiBiasClusters->isEmpty()) {
            return [];
        }

        $clusterIds = $multiBiasClusters->pluck('story_cluster_id')->all();
        $posts = Post::query()
            ->whereIn('story_cluster_id', $clusterIds)
            ->where('status', 'published')
            ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
            ->get(['id', 'name', 'translated_name', 'translated_description', 'translated_to', 'original_language', 'story_cluster_id', 'bias_rating', 'image', 'source_id', 'source_name']);

        GrimbaTranslationPresenter::warm($posts);
        $byCluster = $posts->groupBy('story_cluster_id');

        $cards = [];
        foreach ($multiBiasClusters as $c) {
            if (count($cards) >= 8) {
                break;
            }

            $clusterPosts = $byCluster[$c->story_cluster_id] ?? collect();
            if ($clusterPosts->isEmpty()) {
                continue;
            }

            $head = null;
            foreach ($clusterPosts as $cp) {
                if (! $state->isShown((int) $cp->id) && $state->canTakeSource($cp->source_id ?? null)) {
                    $head = $cp;
                    break;
                }
            }

            if (! $head) {
                continue;
            }

            $counts = ['left' => 0, 'center' => 0, 'right' => 0];
            foreach ($clusterPosts as $cp) {
                $b = $cp->bias_rating ?? 'unknown';
                if (isset($counts[$b])) {
                    $counts[$b]++;
                }
            }

            $state->take($head);

            $cards[] = [
                'cluster_id' => (int) $c->story_cluster_id,
                'sides' => (int) $c->sides,
                'articles' => (int) $c->articles,
                'head' => $head,
                'counts' => $counts,
                'image' => $clusterPosts->pluck('image')->filter()->first(),
            ];
        }

        return $cards;
    }

    private static function pickHero(HomeFeedState $state): ?Post
    {
        $balancedClusters = self::balancedClusterIds();
        $realImage = fn ($q) => $q->where('image', 'like', 'http%');

        $hero = self::firstFeaturedHero($state, $balancedClusters, $realImage)
            ?? self::firstFeaturedHero($state, null, $realImage)
            ?? self::firstFeaturedHero($state, $balancedClusters, null)
            ?? self::firstFeaturedHero($state, null, null)
            ?? self::firstFallbackHero($state, $realImage)
            ?? self::firstFallbackHero($state, null);

        if ($hero) {
            $state->take($hero);
        }

        return $hero;
    }

    private static function firstFeaturedHero(HomeFeedState $state, ?Collection $clusterIds, ?callable $imageFilter): ?Post
    {
        $query = Post::query()
            ->where('status', 'published')
            ->where('is_featured', true);

        if ($clusterIds !== null) {
            $query->whereIn('story_cluster_id', $clusterIds);
        }

        if ($imageFilter !== null) {
            $imageFilter($query);
        }

        return $state->firstAvailable(
            $query->tap(fn ($q) => self::applyHomeRailSurfacing($q))->limit(15)->get()
        );
    }

    private static function firstFallbackHero(HomeFeedState $state, ?callable $imageFilter): ?Post
    {
        $query = Post::query()->where('status', 'published');
        if ($imageFilter !== null) {
            $imageFilter($query);
        }

        return $state->firstAvailable(
            $query->tap(fn ($q) => self::applyHomeRailSurfacing($q))->limit(20)->get()
        );
    }

    private static function pickHeroBriefingColumn(HomeFeedState $state, int $count): Collection
    {
        $balancedClusters = self::balancedClusterIds();
        $picked = collect();

        if ($balancedClusters->isNotEmpty()) {
            $candidates = Post::query()
                ->where('status', 'published')
                ->whereIn('story_cluster_id', $balancedClusters)
                ->whereNotIn('id', $state->shownIds() ?: [0])
                ->with('categories')
                ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
                ->limit($count * 4)
                ->get();

            foreach ($candidates as $post) {
                if ($picked->count() >= $count) {
                    break;
                }
                if ($state->take($post)) {
                    $picked->push($post);
                }
            }
        }

        if ($picked->count() < $count) {
            $fill = Post::query()
                ->where('status', 'published')
                ->whereNotIn('id', $state->shownIds() ?: [0])
                ->with('categories')
                ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
                ->limit(($count - $picked->count()) * 4)
                ->get();

            foreach ($fill as $post) {
                if ($picked->count() >= $count) {
                    break;
                }
                if ($state->take($post)) {
                    $picked->push($post);
                }
            }
        }

        return $picked;
    }

    private static function pickBlindspots(HomeFeedState $state, int $count): Collection
    {
        $candidates = Post::query()
            ->where('status', 'published')
            ->where('is_blindspot', true)
            ->whereNotIn('id', $state->shownIds() ?: [0])
            ->with('categories')
            ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
            ->limit($count * 4)
            ->get();

        $picked = collect();
        foreach ($candidates as $post) {
            if ($picked->count() >= $count) {
                break;
            }
            if ($state->take($post)) {
                $picked->push($post);
            }
        }

        return $picked;
    }

    private static function pickRecentForStats(int $count): Collection
    {
        // Counter values only — does NOT consume from the shown set.
        return Post::query()
            ->where('status', 'published')
            ->with('categories')
            ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
            ->limit($count)
            ->get();
    }

    /**
     * @return array<string, Collection<int, Post>>
     */
    private static function pickMostReadByBias(HomeFeedState $state, int $perBias): array
    {
        $out = [];
        foreach (['left', 'center', 'right'] as $bias) {
            $candidates = Post::query()
                ->where('status', 'published')
                ->where('bias_rating', $bias)
                ->where('views', '>', 0)
                ->whereNotIn('id', $state->shownIds() ?: [0])
                ->with('categories')
                ->orderByDesc('views')
                ->tap(fn ($q) => GrimbaPostRecency::orderByPublished($q))
                ->limit($perBias * 4)
                ->get();

            $picked = collect();
            foreach ($candidates as $post) {
                if ($picked->count() >= $perBias) {
                    break;
                }
                if ($state->take($post)) {
                    $picked->push($post);
                }
            }

            $out[$bias] = $picked;
        }

        return $out;
    }

    private static function pickTopNewsInline(HomeFeedState $state, int $count): Collection
    {
        $balancedClusters = self::balancedClusterIds();
        $picked = collect();

        if ($balancedClusters->isNotEmpty()) {
            $candidates = Post::query()
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->where('is_featured', false)->orWhereNull('is_featured');
                })
                ->whereIn('story_cluster_id', $balancedClusters)
                ->whereNotIn('id', $state->shownIds() ?: [0])
                ->with('categories')
                ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
                ->limit($count * 4)
                ->get();

            foreach ($candidates as $post) {
                if ($picked->count() >= $count) {
                    break;
                }
                if ($state->take($post)) {
                    $picked->push($post);
                }
            }
        }

        if ($picked->count() < $count) {
            $pad = Post::query()
                ->where('status', 'published')
                ->whereNotIn('id', $state->shownIds() ?: [0])
                ->with('categories')
                ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
                ->limit(($count - $picked->count()) * 4)
                ->get();

            foreach ($pad as $post) {
                if ($picked->count() >= $count) {
                    break;
                }
                if ($state->take($post)) {
                    $picked->push($post);
                }
            }
        }

        return $picked;
    }

    /**
     * @return array<int, array{category: object, latest: ?Post, blindspots: Collection<int, Post>}>
     */
    private static function pickSectionBlocks(HomeFeedState $state): array
    {
        $featuredCategories = GrimbaEditorialCategories::sectionTopics(2);
        $out = [];

        foreach ($featuredCategories as $cat) {
            $latestCandidates = Post::query()
                ->whereHas('categories', fn ($q) => $q->where('categories.id', $cat->id))
                ->where('status', 'published')
                ->whereNotIn('id', $state->shownIds() ?: [0])
                ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
                ->limit(8)
                ->get();

            $latest = null;
            foreach ($latestCandidates as $post) {
                if ($state->take($post)) {
                    $latest = $post;
                    break;
                }
            }

            $blindspotCandidates = Post::query()
                ->whereHas('categories', fn ($q) => $q->where('categories.id', $cat->id))
                ->where('status', 'published')
                ->where('is_blindspot', true)
                ->whereNotIn('id', $state->shownIds() ?: [0])
                ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
                ->limit(6)
                ->get();

            $blindspots = collect();
            foreach ($blindspotCandidates as $post) {
                if ($blindspots->count() >= 2) {
                    break;
                }
                if ($state->take($post)) {
                    $blindspots->push($post);
                }
            }

            if ($blindspots->count() < 2) {
                $globalFill = Post::query()
                    ->where('status', 'published')
                    ->where('is_blindspot', true)
                    ->whereNotIn('id', $state->shownIds() ?: [0])
                    ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
                    ->limit(6)
                    ->get();

                foreach ($globalFill as $post) {
                    if ($blindspots->count() >= 2) {
                        break;
                    }
                    if ($state->take($post)) {
                        $blindspots->push($post);
                    }
                }
            }

            $out[] = [
                'category' => $cat,
                'latest' => $latest,
                'blindspots' => $blindspots,
            ];
        }

        return $out;
    }

    private static function pickLatest(HomeFeedState $state, int $count): Collection
    {
        $state->relaxSourceCap(self::SOURCE_CAP_LATEST);

        $candidates = Post::query()
            ->where('status', 'published')
            ->whereNotIn('id', $state->shownIds() ?: [0])
            ->with('categories')
            ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
            ->limit($count * 3)
            ->get();

        $picked = collect();
        foreach ($candidates as $post) {
            if ($picked->count() >= $count) {
                break;
            }
            if ($state->take($post)) {
                $picked->push($post);
            }
        }

        // Last-resort fill: keep re-checking shownIds so we never
        // reintroduce a post the briefing/hero/allSides already
        // claimed (Zen's concern). Source-cap is intentionally
        // relaxed via relaxSourceCap above so the latest list can
        // mention a publisher one extra time when the pool is thin.
        if ($picked->count() < $count) {
            $pad = Post::query()
                ->where('status', 'published')
                ->whereNotIn('id', $state->shownIds() ?: [0])
                ->with('categories')
                ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
                ->limit(($count - $picked->count()) * 3)
                ->get();

            foreach ($pad as $post) {
                if ($picked->count() >= $count) {
                    break;
                }
                if ($state->take($post)) {
                    $picked->push($post);
                }
            }
        }

        GrimbaTranslationPresenter::warm($picked);

        return $picked;
    }

    private static function balancedClusterIds(): Collection
    {
        return DB::table('posts')
            ->select('story_cluster_id')
            ->where('status', 'published')
            ->whereNotNull('story_cluster_id')
            ->whereIn('bias_rating', ['left', 'center', 'right'])
            ->groupBy('story_cluster_id')
            ->havingRaw('COUNT(DISTINCT bias_rating) >= 2')
            ->pluck('story_cluster_id');
    }

    /**
     * S-LSAT-04 phase 2 (Vader 2026-05-18): every home rail that
     * touches a Post query goes through this single helper. When
     * `strict_home` is on (default), we apply the WHERE filter that
     * drops wrong-locale-no-translation rows. Either way, we apply
     * the soft ranker last so within the surviving set, native-locale
     * posts come before translated ones.
     *
     * Pass the resolved reader locale explicitly: `app()->getLocale()`
     * still returns the framework default at this stage.
     */
    public static function applyHomeRailSurfacing(mixed $query): mixed
    {
        $locale = self::resolveReaderLocale();
        if (GrimbaLanguageSettings::strictForHome()) {
            GrimbaTranslationPresenter::filterForTargetLocale($query, $locale);
        }
        return GrimbaTranslationPresenter::orderForTargetLocale($query, $locale);
    }

    private static function cacheKey(): string
    {
        // Region selection lives in a cookie. Without it in the key, the
        // first request poisons the cache for every region for the
        // duration of the TTL — readers selecting Africa would see
        // whoever's region landed first.
        //
        // S-LSAT-04 phase 2 bump v1 → v2: now keyed on the resolved
        // reader locale (?lang= → cookie → fallback) AND the strict
        // bucket, so flipping `grimba_lang_strict_home` toggles the
        // bucket and prior soft results don't bleed in.
        $region = self::resolveRegionKey();
        $locale = self::resolveReaderLocale();
        $bucket = GrimbaLanguageSettings::strictForHome() ? 'strict' : 'soft';

        return 'grimba_home_feed_v2_' . $locale . '_' . $region . '_' . $bucket;
    }

    private static function resolveRegionKey(): string
    {
        $request = request();
        if (! $request) {
            return 'international';
        }

        $raw = (string) $request->cookie(\App\Scopes\GrimbaRegionScope::COOKIE_NAME, 'international');
        $migrated = \App\Ground\Regions::migrate($raw);

        return in_array($migrated, ['africa', 'europe', 'americas', 'international'], true)
            ? $migrated
            : 'international';
    }
}
