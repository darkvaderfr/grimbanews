@php
    /**
     * S328 — Daily Briefing hero card.
     *
     * Ground.news opens with a "Daily Briefing" — biggest story of the
     * cycle, dramatic 21:9 hero treatment, X stories · Y articles ·
     * Zm read metadata. We replicate that as the lead element above
     * the all-sides rail.
     *
     * Pick: most-covered cluster (highest source count) in the last
     * 24h that has at least 3 sources AND coverage from ≥2 bias sides.
     * That filter rules out one-side-only blindspots (which have their
     * own surface) and tiny-cluster noise.
     */
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\DB;

    $__brief = Cache::remember('grimba_daily_briefing_v3', 300, function () {
        $resolveClusters = function ($since) {
            return DB::table('posts')
                ->where('status', 'published')
                ->whereNotNull('story_cluster_id')
                ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
                ->select('story_cluster_id', 'bias_rating', DB::raw('count(*) as c'))
                ->groupBy('story_cluster_id', 'bias_rating')
                ->get();
        };

        // Prefer last-36h clusters; fall back to all-time when ingest
        // is paused (dev fixtures, weekend lulls).
        $rows = $resolveClusters(now()->subHours(36));
        if ($rows->isEmpty()) {
            $rows = $resolveClusters(null);
        }

        $clusters = [];
        foreach ($rows as $r) {
            $cid = (int) $r->story_cluster_id;
            if (! isset($clusters[$cid])) {
                $clusters[$cid] = ['total' => 0, 'sides' => []];
            }
            $clusters[$cid]['total'] += (int) $r->c;
            if (in_array($r->bias_rating, ['left', 'center', 'right'], true)) {
                $clusters[$cid]['sides'][$r->bias_rating] = (int) $r->c;
            }
        }

        $candidates = collect($clusters)
            ->filter(fn ($c) => $c['total'] >= 3 && count($c['sides']) >= 2)
            ->sortByDesc(fn ($c) => $c['total'])
            ->take(5)
            ->keys();

        if ($candidates->isEmpty()) {
            return null;
        }

        $clusterId = (int) $candidates->first();
        $count = $clusters[$clusterId]['total'];
        $sides = $clusters[$clusterId]['sides'];

        $lead = Post::query()
            ->where('story_cluster_id', $clusterId)
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->first();

        return $lead ? [
            'post'      => $lead,
            'clusterId' => $clusterId,
            'count'     => $count,
            'sides'     => $sides,
        ] : null;
    });

    if (! $__brief) return;

    $__post  = $__brief['post'];
    $__count = $__brief['count'];
    $__sides = $__brief['sides'];

    $__total = max(1, array_sum($__sides));
    $__pct = [
        'left'   => (int) round(($__sides['left']   ?? 0) * 100 / $__total),
        'center' => (int) round(($__sides['center'] ?? 0) * 100 / $__total),
        'right'  => (int) round(($__sides['right']  ?? 0) * 100 / $__total),
    ];

    $__title       = GnTr::title($__post);
    $__desc        = GnTr::description($__post);
    $__readMin     = max(1, (int) ceil(str_word_count(strip_tags((string) $__desc)) / 200));
    $__sourceLabel = $__post->source_name ?? '';
    $__date        = optional($__post->created_at)->locale('fr')->isoFormat('D MMMM');
@endphp

<section class="grimba-daily-briefing mb-4" aria-labelledby="grimba-daily-briefing-title">
    <a href="{{ url('/comparatif/' . $__brief['clusterId']) }}" class="grimba-daily-briefing__card">
        <div class="grimba-daily-briefing__cover">
            {!! Theme::partial('post-hero-img', ['post' => $__post, 'size' => 'extra-large', 'eager' => true]) !!}
            <div class="grimba-daily-briefing__veil" aria-hidden="true"></div>
        </div>

        <div class="grimba-daily-briefing__body">
            <div class="grimba-daily-briefing__kicker">
                <span class="grimba-daily-briefing__badge">{{ __('Briefing du jour') }}</span>
                @if($__date)
                    <span class="grimba-daily-briefing__date">{{ $__date }}</span>
                @endif
            </div>

            <h2 id="grimba-daily-briefing-title" class="grimba-daily-briefing__title">
                {{ $__title }}
            </h2>

            @if($__desc)
                <p class="grimba-daily-briefing__lede">
                    {{ \Illuminate\Support\Str::limit(strip_tags((string) $__desc), 220) }}
                </p>
            @endif

            <div class="grimba-daily-briefing__bar" role="img" aria-label="{{ __('Répartition gauche centre droite') }}">
                <div class="grimba-daily-briefing__bar-seg" style="width: {{ $__pct['left'] }}%; background: var(--gn-left);"></div>
                <div class="grimba-daily-briefing__bar-seg" style="width: {{ $__pct['center'] }}%; background: var(--gn-center);"></div>
                <div class="grimba-daily-briefing__bar-seg" style="width: {{ $__pct['right'] }}%; background: var(--gn-right);"></div>
            </div>

            <div class="grimba-daily-briefing__meta">
                <span>{{ trans_choice(':count source|:count sources', $__count, ['count' => $__count]) }}</span>
                <span class="opacity-50">·</span>
                <span>{{ trans_choice(':count min de lecture|:count min de lecture', $__readMin, ['count' => $__readMin]) }}</span>
                @if($__sourceLabel)
                    <span class="opacity-50">·</span>
                    <span>{{ __("D'abord chez :source", ['source' => $__sourceLabel]) }}</span>
                @endif
            </div>
        </div>
    </a>
</section>

<style>
    .grimba-daily-briefing__card {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
        gap: 0;
        border-radius: 18px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        background: var(--gn-paper);
        border: 1px solid var(--gn-rule);
        box-shadow: 0 18px 44px rgba(26, 23, 19, 0.10);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .grimba-daily-briefing__card:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 60px rgba(26, 23, 19, 0.14);
    }
    .grimba-daily-briefing__cover {
        position: relative;
        aspect-ratio: 16/9;
        background: #1a1713;
        overflow: hidden;
    }
    .grimba-daily-briefing__cover img {
        width: 100%; height: 100%;
        object-fit: cover;
        display: block;
    }
    .grimba-daily-briefing__veil {
        position: absolute; inset: 0;
        pointer-events: none;
        background: linear-gradient(135deg, rgba(0,0,0,0.10) 0%, rgba(0,0,0,0.42) 100%);
    }
    .grimba-daily-briefing__body {
        padding: 22px 24px 18px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .grimba-daily-briefing__kicker {
        display: flex; align-items: center; gap: 10px;
        font-family: 'Public Sans', system-ui, sans-serif;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .grimba-daily-briefing__badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 9999px;
        background: var(--gn-ink);
        color: var(--gn-paper);
        box-shadow: 0 4px 12px rgba(26, 23, 19, 0.18);
    }
    .grimba-daily-briefing__date {
        color: var(--gn-ink-soft);
    }
    .grimba-daily-briefing__title {
        margin: 0;
        font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
        font-weight: 700;
        font-size: clamp(22px, 2.6vw, 32px);
        line-height: 1.12;
        letter-spacing: -0.02em;
        color: var(--gn-ink);
    }
    .grimba-daily-briefing__lede {
        margin: 0;
        color: var(--gn-ink-muted);
        font-size: 14.5px;
        line-height: 1.5;
    }
    .grimba-daily-briefing__bar {
        display: flex;
        height: 6px;
        border-radius: 9999px;
        overflow: hidden;
        background: rgba(26, 23, 19, 0.08);
    }
    .grimba-daily-briefing__bar-seg { display: block; height: 100%; }
    .grimba-daily-briefing__meta {
        display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        color: var(--gn-ink-muted);
        font-size: 12.5px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .grimba-daily-briefing__card {
            grid-template-columns: 1fr;
        }
        .grimba-daily-briefing__cover {
            aspect-ratio: 21/9;
        }
        .grimba-daily-briefing__body {
            padding: 16px 18px;
        }
    }

    /* Dark-mode tints */
    html.grimba-home-html[data-bs-theme="dark"] .grimba-daily-briefing__card {
        background: rgba(28, 24, 17, 0.78);
        border-color: rgba(246, 241, 232, 0.10);
        box-shadow: 0 18px 44px rgba(0, 0, 0, 0.42);
    }
    html.grimba-home-html[data-bs-theme="dark"] .grimba-daily-briefing__veil {
        background: linear-gradient(135deg, rgba(0,0,0,0.14) 0%, rgba(0,0,0,0.55) 100%);
    }
    html.grimba-home-html[data-bs-theme="dark"] .grimba-daily-briefing__bar {
        background: rgba(255, 255, 255, 0.10);
    }
</style>
