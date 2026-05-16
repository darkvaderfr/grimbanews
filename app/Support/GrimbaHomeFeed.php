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

    public static function isShown(int $postId): bool
    {
        return isset(self::build()['allShown'][$postId]);
    }

    private static function compose(): array
    {
        $state = new HomeFeedState(self::SOURCE_CAP_PRIMARY);

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
            ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
            ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
            $query->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))->limit(15)->get()
        );
    }

    private static function firstFallbackHero(HomeFeedState $state, ?callable $imageFilter): ?Post
    {
        $query = Post::query()->where('status', 'published');
        if ($imageFilter !== null) {
            $imageFilter($query);
        }

        return $state->firstAvailable(
            $query->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))->limit(20)->get()
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
                ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
                ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
            ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
            ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
                ->latest()
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
                ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
                ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
                ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
                ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
                    ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
            ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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
                ->tap(fn ($q) => GrimbaTranslationPresenter::orderForTargetLocale($q))
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

    private static function cacheKey(): string
    {
        return 'grimba_home_feed_v1_' . app()->getLocale();
    }
}
