@php
    /**
     * S154 — "Couvert par tous les côtés" rail.
     *
     * The defining GrimbaNews promise: stories covered across the
     * political spectrum. This rail surfaces them prominently — a
     * horizontal scroll of cards, each linking to the story page
     * for that cluster (the most-covered article picks up clicks).
     *
     * Pulls only clusters with ≥2 bias sides — the legacy "single-
     * bias-cluster" output stays in the regular hero / blog grids
     * below. Capped at 8 cards; if no multi-bias clusters exist yet,
     * the rail hides itself entirely.
     */

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
            \Illuminate\Support\Facades\DB::raw('MAX(created_at) as latest')
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
    $picks = Post::query()
        ->whereIn('story_cluster_id', $clusterIds)
        ->where('status', 'published')
        ->orderByDesc('created_at')
        ->get(['id', 'name', 'story_cluster_id', 'bias_rating', 'image', 'source_name'])
        ->groupBy('story_cluster_id');

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
            <span class="grimba-methodology__kicker">Couvert par tous les côtés</span>
            <h2 class="grimba-methodology__title m-0 mt-1" style="font-size:clamp(22px, 2.6vw, 32px); letter-spacing:-0.3px;">
                Histoires que gauche, centre et droite couvrent en même temps
            </h2>
        </div>
        <span class="small opacity-65">{{ count($cards) }} {{ count($cards) === 1 ? 'histoire' : 'histoires' }} ce moment</span>
    </header>

    <div class="grimba-all-sides__rail" style="
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(280px, 1fr);
        gap: 16px;
        overflow-x: auto;
        padding-bottom: 8px;
        scrollbar-width: thin;
    ">
        @foreach($cards as $card)
            @php
                $head = $card['head'];
                $url = $head->url ?? url('/blog');
            @endphp
            <a href="{{ $url }}"
               class="grimba-all-sides__card"
               style="
                   display: flex;
                   flex-direction: column;
                   border: 1px solid rgba(26, 23, 19, 0.10);
                   border-radius: 14px;
                   background: var(--gn-paper, #f6f1e8);
                   color: var(--gn-ink, #1a1713);
                   text-decoration: none;
                   overflow: hidden;
                   transition: transform .15s ease, box-shadow .15s ease;
               "
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 28px rgba(0,0,0,0.10)';"
               onmouseout="this.style.transform=''; this.style.boxShadow='';">

                @if($card['image'])
                    <div class="ratio ratio-16x9" style="background:rgba(0,0,0,0.04);">
                        <img src="{{ \Botble\Media\Facades\RvMedia::getImageUrl($card['image']) }}"
                             alt="{{ $head->name }}"
                             loading="lazy"
                             style="object-fit:cover; width:100%; height:100%;">
                    </div>
                @else
                    <img src="{{ url('/og/placeholder/' . $head->id . '.svg') }}"
                         alt="{{ $head->name }}"
                         loading="lazy"
                         style="width:100%; aspect-ratio:16/9; object-fit:cover; background:rgba(0,0,0,0.04);">
                @endif

                <div style="padding: 14px 16px 16px; display:flex; flex-direction:column; flex:1;">
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
                        <span class="ms-auto opacity-65" style="font-size:12px;">
                            {{ $card['articles'] }} sources
                        </span>
                    </div>
                    <h3 style="
                        font-family:'Fraunces','Playfair Display',Georgia,serif;
                        font-weight:600;
                        font-size:18px;
                        line-height:1.25;
                        letter-spacing:-0.2px;
                        margin:0;
                        flex:1;
                    ">
                        {{ \Illuminate\Support\Str::limit($head->name, 110) }}
                    </h3>
                </div>
            </a>
        @endforeach
    </div>
</section>
