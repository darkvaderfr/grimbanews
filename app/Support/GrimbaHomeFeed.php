<?php

namespace App\Support;

use App\Support\Continents;
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
                    ->with(['slugable', 'categories.slugable']);

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
    /**
     * S-MAP-04 (Vader 2026-05-29) — per-continent breaking-news
     * aggregation for the full-viewport /breaking-map view. Joins
     * posts → news_sources to get country, then groups by
     * App\Support\Continents bucket. Posts with no source_id or
     * NULL country fall into the 'global' bucket.
     *
     * Returns an array keyed by continent string ('africa', 'americas',
     * 'asia', 'europe', 'oceania', 'global') — every key present, even
     * if its collection is empty (the view renders an empty-state
     * ticker rather than skipping the continent).
     *
     * @return array<string, Collection<int, Post>>
     */
    public static function breakingByContinent(int $windowHours = 18, int $perContinent = 8): array
    {
        // Wider cap than breaking() — the map view tolerates older
        // content (a "calm continent" today still wants to show what
        // happened last week), and admins use ?window=N to inspect.
        $windowHours = max(1, min(720, $windowHours));
        $perContinent = max(1, min(20, $perContinent));
        $locale = self::resolveReaderLocale();
        $strict = GrimbaLanguageSettings::strictForBreaking();
        $bucket = $strict ? 'strict' : 'soft';

        $cacheKey = 'grimba_breaking_continent_v1:' . $locale . ':' . $bucket . ':' . $windowHours . ':' . $perContinent;

        return Cache::remember($cacheKey, 60, function () use ($windowHours, $strict, $locale, $perContinent): array {
            $continents = Continents::all();
            $buckets = array_fill_keys($continents, collect());

            // Join posts → news_sources on source_id to read country.
            // Use Post query so eager-loading + slugable accessor still
            // works (the rendering view will read $post->url()).
            $cols = ['posts.id','posts.name','posts.translated_name','posts.translated_to',
                     'posts.original_language','posts.source_name','posts.source_id',
                     'posts.bias_rating','posts.published_at','posts.created_at','posts.image',
                     'news_sources.country as source_country'];

            $query = Post::query()
                ->leftJoin('news_sources', 'posts.source_id', '=', 'news_sources.id')
                ->where('posts.status', 'published')
                ->whereNotNull('posts.source_name')
                ->with('slugable');

            if ($strict) {
                GrimbaTranslationPresenter::filterForTargetLocale($query, $locale);
            }

            GrimbaPostRecency::wherePublishedSince($query, now()->subHours($windowHours));
            GrimbaPostRecency::orderByPublished($query);

            // Cap upstream pull at perContinent × 6 buckets × 2 (over-pull
            // to allow per-bucket fill even when geography is skewed
            // toward Europe + Americas, which it is).
            $candidates = $query->limit($perContinent * 6 * 2)->get($cols);

            foreach ($candidates as $post) {
                $continent = Continents::forCountry($post->source_country ?? null);
                if ($buckets[$continent]->count() >= $perContinent) {
                    continue;
                }
                $buckets[$continent]->push($post);
            }

            return $buckets;
        });
    }

    /**
     * S-MAP-V4-05 (Vader 2026-05-29) — pin source for the Leaflet
     * /breaking-map rebuild. Returns one entry per *pinnable* country with at
     * least one matching post in the window (a country with a known centroid),
     * each carrying its map coordinates, continent, the exact total of matching
     * posts in the window, and the freshest $perCountry posts for the pin
     * popup / cluster expansion.
     *
     * Shape:
     *   [
     *     ['country' => 'US', 'continent' => 'americas',
     *      'lat' => 39.538, 'lng' => -97.483,
     *      'total' => 225, 'posts' => Collection<Post>(>=1, <= $perCountry)],
     *     ...
     *   ]
     *
     * Mirrors breakingByContinent(): same published + has-source filter,
     * same locale-strict gate, same 60s cache. v4 pins at the *source's*
     * country, not the story location (story-level geocoding is Phase 2).
     * Posts whose source has a NULL/empty/unknown country — or a country
     * with no centroid — carry no map location and are omitted (the
     * continent sidecar still counts them via breakingByContinent()).
     *
     * Coverage is driven by the authoritative grouped COUNT, NOT by a global
     * recency over-pull: the original V4-05 build gated pin existence on a
     * 600-row freshest-first pull, which silently dropped quiet countries
     * whose posts fell outside the freshest rows (S-MAP-V4 Phase 1 audit —
     * Zen/Echo/Critic: 17 of 28 countries hidden at window=720h, incl. GB).
     * The fix below emits a pin for EVERY pinnable country the count pass
     * finds, then fetches that country's own freshest posts — so a prolific
     * FR/US wire can never starve a quiet region off the map. This costs one
     * small indexed query per pinnable country (bounded by the distinct
     * source-country count, ~30, behind the 60s cache) and scales with
     * geography rather than total post volume.
     */
    public static function pinsForMap(int $windowHours = 18, int $perCountry = 5): array
    {
        $windowHours = max(1, min(720, $windowHours));
        $perCountry = max(1, min(20, $perCountry));
        $locale = self::resolveReaderLocale();
        $strict = GrimbaLanguageSettings::strictForBreaking();
        $bucket = $strict ? 'strict' : 'soft';

        $cacheKey = 'grimba_pins_map_v1:' . $locale . ':' . $bucket . ':' . $windowHours . ':' . $perCountry;

        return Cache::remember($cacheKey, 60, function () use ($windowHours, $strict, $locale, $perCountry): array {
            // Shared base query, rebuilt fresh for each pass so the count and
            // the per-country pulls apply identical filters and can never
            // diverge: published posts with a non-empty source country,
            // inner-joined to news_sources, inside the window, locale-strict
            // when configured.
            $base = function () use ($strict, $locale, $windowHours) {
                $query = Post::query()
                    ->join('news_sources', 'posts.source_id', '=', 'news_sources.id')
                    ->where('posts.status', 'published')
                    ->whereNotNull('posts.source_name')
                    ->whereNotNull('news_sources.country')
                    ->where('news_sources.country', '!=', '');

                if ($strict) {
                    GrimbaTranslationPresenter::filterForTargetLocale($query, $locale);
                }

                GrimbaPostRecency::wherePublishedSince($query, now()->subHours($windowHours));

                return $query;
            };

            // Pass 1 — exact per-country totals over the whole window (the pin
            // badge). Keep only pinnable countries (a known centroid). The ISO
            // is normalized (upper+trim) and summed on collision so a future
            // ingest path that writes case/whitespace variants of one code
            // can't clobber or undercount the badge.
            $cols = ['posts.id','posts.name','posts.translated_name','posts.translated_to',
                     'posts.original_language','posts.source_name','posts.source_id',
                     'posts.bias_rating','posts.published_at','posts.created_at','posts.image',
                     'news_sources.country as source_country'];

            // Grouped by country AND bias so each pin carries an EXACT per-bias
            // breakdown (counts) — the donut + the V4-18 bias filter chips run
            // off these, not the capped sample. ISO normalized (upper+trim) +
            // summed on collision; non-left/center/right folds to 'unknown'.
            $totals = [];
            $counts = [];
            foreach (
                $base()->toBase()
                    ->select('news_sources.country as country', 'posts.bias_rating as bias_rating', DB::raw('count(*) as n'))
                    ->groupBy('news_sources.country', 'posts.bias_rating')
                    ->get() as $row
            ) {
                $iso = strtoupper(trim((string) $row->country));
                if ($iso === '' || CountryCentroids::for($iso) === null) {
                    continue; // blank or unpinnable — no map location
                }
                $bias = in_array($row->bias_rating, ['left', 'center', 'right'], true) ? $row->bias_rating : 'unknown';
                $n = (int) $row->n;
                $totals[$iso] = ($totals[$iso] ?? 0) + $n;
                if (! isset($counts[$iso])) {
                    $counts[$iso] = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
                }
                $counts[$iso][$bias] += $n;
            }

            // Pass 2 — for each pinnable country, its own freshest <=perCountry
            // posts. One small indexed query per country guarantees every
            // counted country gets a pin (a country with total>0 returns >=1
            // post here, since this query shares Pass 1's exact filters).
            $pins = [];
            foreach ($totals as $iso => $total) {
                $centroid = CountryCentroids::for($iso); // non-null by construction

                $postsQuery = $base()
                    ->whereRaw('upper(trim(news_sources.country)) = ?', [$iso])
                    ->with('slugable');
                GrimbaPostRecency::orderByPublished($postsQuery);
                $posts = $postsQuery->limit($perCountry)->get($cols);

                $pins[] = [
                    'country' => $iso,
                    'continent' => Continents::forCountry($iso),
                    'lat' => $centroid[0],
                    'lng' => $centroid[1],
                    'total' => $total,
                    'counts' => $counts[$iso],
                    'posts' => $posts,
                ];
            }

            // Densest country first (drives draw order + the sidecar), ISO
            // as the deterministic tie-break.
            usort($pins, fn ($a, $b) => ($b['total'] <=> $a['total']) ?: strcmp($a['country'], $b['country']));

            return $pins;
        });
    }

    /**
     * S-MAP-V4-15 (Vader 2026-05-30) — EXACT per-continent totals + bias
     * breakdown for the /breaking-map sidecar. Unlike breakingByContinent()
     * (which caps each bucket's displayed posts), this returns the true
     * window counts, so the sidecar agrees with the map's exact per-pin
     * totals (sum of a continent's pin totals == its sidecar count, modulo
     * the synthetic 'global' bucket). One grouped query.
     *
     * Returns every continent key from Continents::all() (5 + 'global'):
     *   ['europe' => ['count' => 1202, 'left' => .., 'center' => .., 'right' => .., 'unknown' => ..], ...]
     *
     * 'global' collects NULL/empty/unrecognized-country posts (no map pin).
     * Mirrors pinsForMap's published + has-source + locale-strict filters and
     * 60s cache; uses a LEFT join so source-less posts still land in 'global'.
     *
     * @return array<string, array{count:int,left:int,center:int,right:int,unknown:int}>
     */
    public static function continentTotals(int $windowHours = 18): array
    {
        $windowHours = max(1, min(720, $windowHours));
        $locale = self::resolveReaderLocale();
        $strict = GrimbaLanguageSettings::strictForBreaking();
        $bucket = $strict ? 'strict' : 'soft';

        $cacheKey = 'grimba_continent_totals_v1:' . $locale . ':' . $bucket . ':' . $windowHours;

        return Cache::remember($cacheKey, 60, function () use ($windowHours, $strict, $locale): array {
            $query = Post::query()
                ->leftJoin('news_sources', 'posts.source_id', '=', 'news_sources.id')
                ->where('posts.status', 'published')
                ->whereNotNull('posts.source_name');

            if ($strict) {
                GrimbaTranslationPresenter::filterForTargetLocale($query, $locale);
            }

            GrimbaPostRecency::wherePublishedSince($query, now()->subHours($windowHours));

            $rows = $query->toBase()
                ->select('news_sources.country as country', 'posts.bias_rating as bias_rating', DB::raw('count(*) as n'))
                ->groupBy('news_sources.country', 'posts.bias_rating')
                ->get();

            $totals = [];
            foreach (Continents::all() as $continent) {
                $totals[$continent] = ['count' => 0, 'left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
            }

            foreach ($rows as $row) {
                $continent = Continents::forCountry($row->country !== null ? (string) $row->country : null);
                $bias = in_array($row->bias_rating, ['left', 'center', 'right'], true) ? $row->bias_rating : 'unknown';
                $n = (int) $row->n;
                $totals[$continent]['count'] += $n;
                $totals[$continent][$bias] += $n;
            }

            return $totals;
        });
    }

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

            // S-LSAT-06 — locale-scoped keyword set in strict mode
            // so the EN reader doesn't pick up FR-only "Alerte
            // enlèvement" headings the row filter then drops.
            $keywords = self::breakingKeywordsForLocale($locale, $strict);
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
                // Wave RRRRRRRR (Vader 2026-05-20) — pass $locale
                // explicitly (was relying on the implicit
                // targetLocale() request lookup, which works under
                // a normal HTTP request but drifts inside background
                // jobs or queue workers that warm this cache from a
                // request-less context. Wave LLLLLLLL's line 339
                // already used the explicit pattern; mirror it here.
                GrimbaTranslationPresenter::filterForTargetLocale($recent, $locale);
            }
            GrimbaPostRecency::wherePublishedSince($recent, now()->subHours($windowHours));
            GrimbaPostRecency::orderByPublished($recent);

            $picked = $recent->limit(14)->get($cols);
            if ($picked->isNotEmpty()) {
                return ['mode' => 'latest', 'posts' => $picked];
            }

            // Last-resort fill: drop the recency window entirely.
            //
            // Wave LLLLLLLL (Vader 2026-05-20) — keep strict locale
            // filter applied. Before this fix, EN readers would see
            // FR posts on /breaking when no fresh EN content matched
            // the window — directly contradicting the locale toggle.
            // If even the unbounded EN-eligible set is empty, return
            // an empty collection; the view renders "La couverture
            // est calme dans cette édition." which is the honest
            // signal (EN ingest stalled) — not pollute EN readers
            // with FR articles.
            $any = Post::query()
                ->where('status', 'published')
                ->with('slugable');
            if ($strict) {
                GrimbaTranslationPresenter::filterForTargetLocale($any, $locale);
            }
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
     * S-LSAT-06 (Vader 2026-05-18) — kept as the union method, but
     * the FR/EN sets are split below so a reader in strict-mode EN
     * doesn't pick up FR-keyword posts that have no EN translation
     * (and vice versa). breaking() routes through breakingKeywordsForLocale().
     *
     * @return array<int, string>
     */
    private static function breakingKeywords(): array
    {
        return array_merge(self::breakingKeywordsEn(), self::breakingKeywordsFr());
    }

    /**
     * @return array<int, string>
     */
    private static function breakingKeywordsEn(): array
    {
        return [
            'breaking news', 'breaking:', 'just in:', 'live updates',
            'state of emergency', 'declared dead', 'evacuation order',
            'mass casualty', 'death toll', 'massive explosion',
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function breakingKeywordsFr(): array
    {
        return [
            'en direct', 'dernière minute', 'flash info', 'alerte info',
            'alerte enlèvement', "état d'urgence", 'urgent :', 'urgent –',
            'plan blanc', 'attentat', 'sous les décombres',
        ];
    }

    /**
     * S-LSAT-06 — return the keyword set matching the reader's
     * locale when strict mode is on, otherwise the union. Strict
     * mode is a contract from the filter side (filterForTargetLocale
     * already drops opposite-locale-no-translation rows), but the
     * keyword filter ALSO honors it so an EN reader's breaking
     * ticker doesn't surface a FR-only "Alerte enlèvement"
     * heading that the row filter then drops, leaving a confusing
     * fallback path.
     *
     * @return array<int, string>
     */
    private static function breakingKeywordsForLocale(string $locale, bool $strict): array
    {
        if (! $strict) {
            return self::breakingKeywords();
        }
        return $locale === 'en' ? self::breakingKeywordsEn() : self::breakingKeywordsFr();
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
        $heroMiddleGround = self::pickMiddleGround($state, 2);
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
            'heroMiddleGround' => $heroMiddleGround,
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
                ->with('categories.slugable');

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
                ->with('categories.slugable')
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
                ->with('categories.slugable')
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
            ->with('categories.slugable')
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

    /**
     * Wave EEEEEEEEEEE (Vader 2026-05-26) — pick Middle Ground hero
     * articles for the home rail. Mirror of pickBlindspots: pulls
     * articles whose cluster was tagged with the 'mg_*' prefix in
     * story_clusters.review_action by `grimba:reclassify-clusters`.
     * One article per cluster.
     */
    private static function pickMiddleGround(HomeFeedState $state, int $count): Collection
    {
        $clusterIds = DB::table('story_clusters')
            ->where('review_action', 'like', GrimbaClusterBias::MG_TAG_SQL_LIKE)
            ->orderByDesc('reviewed_at')
            ->limit($count * 6)
            ->pluck('id');

        if ($clusterIds->isEmpty()) {
            return collect();
        }

        $candidates = Post::query()
            ->where('status', 'published')
            ->whereIn('story_cluster_id', $clusterIds)
            ->whereNotIn('id', $state->shownIds() ?: [0])
            ->with('categories.slugable')
            ->tap(fn ($q) => self::applyHomeRailSurfacing($q))
            ->limit($count * 4)
            ->get();

        $picked = collect();
        $seenClusters = [];
        foreach ($candidates as $post) {
            if ($picked->count() >= $count) {
                break;
            }
            $cid = (int) $post->story_cluster_id;
            if ($cid === 0 || isset($seenClusters[$cid])) {
                continue;
            }
            if ($state->take($post)) {
                $picked->push($post);
                $seenClusters[$cid] = true;
            }
        }

        return $picked;
    }

    public static function heroMiddleGround(): Collection
    {
        return self::build()['heroMiddleGround'];
    }

    private static function pickRecentForStats(int $count): Collection
    {
        // Counter values only — does NOT consume from the shown set.
        return Post::query()
            ->where('status', 'published')
            ->with('categories.slugable')
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
                ->with('categories.slugable')
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
                ->with('categories.slugable')
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
                ->with('categories.slugable')
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
            ->with('categories.slugable')
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
                ->with('categories.slugable')
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
