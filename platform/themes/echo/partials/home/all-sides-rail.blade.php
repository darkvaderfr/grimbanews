@php
    /**
     * S154 — "Couvert par tous les côtés" rail.
     *
     * The defining GrimbaNews promise: stories covered across the
     * political spectrum. This rail surfaces them prominently — a
     * horizontal scroll of cards, each linking to the comparison
     * page for that cluster, not the legacy blog index.
     *
     * Pulls only clusters with ≥2 bias sides — the legacy "single-
     * bias-cluster" output stays in the regular hero / blog grids
     * below. Capped at 8 cards; if no multi-bias clusters exist yet,
     * the rail hides itself entirely.
     */

    use App\Support\GrimbaTranslationPresenter as GnTr;
    use App\Support\GrimbaPostRecency as GnRecency;
    use Botble\Blog\Models\Post;

    // Cluster ids with ≥2 bias sides + post-counts (recency-weighted).
    $multiBiasClusters = \Illuminate\Support\Facades\DB::table('posts')
        ->whereNotNull('story_cluster_id')
        ->where('status', 'published')
        ->whereIn('bias_rating', ['left', 'center', 'right'])
        ->select(
            'story_cluster_id',
            \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT bias_rating) as sides'),
            \Illuminate\Support\Facades\DB::raw('COUNT(*) as articles'),
            \Illuminate\Support\Facades\DB::raw('MAX(' . GnRecency::expression() . ') as latest')
        )
        ->groupBy('story_cluster_id')
        ->havingRaw('COUNT(DISTINCT bias_rating) >= 2')
        ->orderByDesc('sides')
        ->orderByDesc('latest')
        ->limit(8)
        ->get();

    if ($multiBiasClusters->isEmpty()) return;

    // Hydrate the most-recent post + bias breakdown per cluster.
    $clusterIds = $multiBiasClusters->pluck('story_cluster_id')->all();
    $pickPosts = Post::query()
        ->whereIn('story_cluster_id', $clusterIds)
        ->where('status', 'published')
        ->tap(fn ($q) => GnTr::orderForTargetLocale($q))
        ->get(['id', 'name', 'translated_name', 'translated_description', 'translated_to', 'original_language', 'story_cluster_id', 'bias_rating', 'image', 'source_name']);

    GnTr::warm($pickPosts);
    $picks = $pickPosts->groupBy('story_cluster_id');

    $cards = [];
    foreach ($multiBiasClusters as $c) {
        $clusterPosts = $picks[$c->story_cluster_id] ?? collect();
        if ($clusterPosts->isEmpty()) continue;

        $head = $clusterPosts->first();
        $counts = ['left' => 0, 'center' => 0, 'right' => 0];
        foreach ($clusterPosts as $cp) {
            $b = $cp->bias_rating ?? 'unknown';
            if (isset($counts[$b])) $counts[$b]++;
        }

        $cards[] = [
            'cluster_id' => (int) $c->story_cluster_id,
            'sides'      => (int) $c->sides,
            'articles'   => (int) $c->articles,
            'head'       => $head,
            'counts'     => $counts,
            'image'      => $clusterPosts->pluck('image')->filter()->first(),
        ];
    }

    if (empty($cards)) return;

    $biasMeta = [
        'left'   => '#3b82f6',
        'center' => '#a8a8a8',
        'right'  => '#e84c3d',
    ];
@endphp

<section class="grimba-all-sides container-xxl py-3 py-md-4">
    <header class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <span class="grimba-methodology__kicker">{{ __('Couvert par tous les côtés') }}</span>
            <h2 class="grimba-methodology__title grimba-all-sides__title m-0 mt-1">
                {{ __('Histoires que gauche, centre et droite couvrent en même temps') }}
            </h2>
        </div>
        <span class="small grimba-all-sides__count">{{ trans_choice(':count histoire ce moment|:count histoires ce moment', count($cards), ['count' => count($cards)]) }}</span>
    </header>

    <div class="grimba-all-sides__rail">
        @foreach($cards as $card)
            @php
                $head = $card['head'];
                $url = url('/comparatif/' . $card['cluster_id']);
                $title = GnTr::title($head);
                $isTranslated = GnTr::isTranslated($head);
            @endphp
            <a href="{{ $url }}"
               class="grimba-all-sides__card">

                {{-- S329 — match post-hero-img.blade.php's safety: skip
                      RvMedia's 1920×1080 generic placeholder fallback by
                      pre-resolving and comparing against the default URL. --}}
                @php
                    $__rsResolved = $card['image']
                        ? \Botble\Media\Facades\RvMedia::getImageUrl($card['image'])
                        : null;
                    $__rsDefault = \Botble\Media\Facades\RvMedia::getDefaultImage();
                    $__rsUsable = $__rsResolved !== null && $__rsResolved !== $__rsDefault;
                @endphp
                @if($__rsUsable)
                    <div class="ratio ratio-16x9 grimba-all-sides__media">
                        <img src="{{ $__rsResolved }}"
                             alt="{{ $title }}"
                             loading="lazy"
                             decoding="async"
                             width="640"
                             height="360"
                             data-grimba-post-id="{{ $head->id }}"
                             class="grimba-all-sides__image">
                    </div>
                @else
                    <img src="{{ url('/og/placeholder/' . $head->id . '.svg') }}"
                         alt="{{ $title }}"
                         loading="lazy"
                         decoding="async"
                         width="640"
                         height="360"
                         data-grimba-post-id="{{ $head->id }}"
                         class="grimba-all-sides__image grimba-all-sides__image--standalone">
                @endif

                <div class="grimba-all-sides__body">
                    <div class="d-flex align-items-center gap-2 mb-2 small">
                        @foreach(['left','center','right'] as $b)
                            @if($card['counts'][$b] > 0)
                                <span style="
                                    display:inline-flex; align-items:center; gap:4px;
                                    padding:2px 8px; border-radius:9999px;
                                    background:{{ $biasMeta[$b] }}1a; color:{{ $biasMeta[$b] }};
                                    font-size:11px; font-weight:700; letter-spacing:0.4px; text-transform:uppercase;
                                ">
                                    <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:{{ $biasMeta[$b] }};"></span>
                                    {{ $card['counts'][$b] }}
                                </span>
                            @endif
                        @endforeach
                        <span class="ms-auto grimba-all-sides__source-count">
                            {{ trans_choice(':count source|:count sources', $card['articles'], ['count' => $card['articles']]) }}
                        </span>
                    </div>
                    <h3 class="grimba-all-sides__headline">
                        {{ \Illuminate\Support\Str::limit($title, 110) }}
                    </h3>
                    @if($isTranslated)
                        <div class="mt-2">{!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}</div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</section>
