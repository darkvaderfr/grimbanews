@php
    /**
     * S148 — Cluster article list. Renders all posts in the same
     * story_cluster, grouped by bias, with filter tabs.
     *
     * @var \Illuminate\Database\Eloquent\Collection $clusterPosts
     * @var \Botble\Blog\Models\Post                 $currentPost  the post the reader landed on
     */

    use Illuminate\Support\Str;

    $byBias = ['left' => [], 'center' => [], 'right' => [], 'unknown' => []];
    foreach ($clusterPosts as $cp) {
        $b = $cp->bias_rating ?? 'unknown';
        if (! isset($byBias[$b])) $b = 'unknown';
        $byBias[$b][] = $cp;
    }
    $totalCount   = $clusterPosts->count();
    $countLabels  = [
        'left'    => count($byBias['left']),
        'center'  => count($byBias['center']),
        'right'   => count($byBias['right']),
        'unknown' => count($byBias['unknown']),
    ];

    $biasMeta = [
        'left'    => ['label' => 'Gauche',     'color' => '#3b82f6', 'short' => 'L'],
        'center'  => ['label' => 'Centre',     'color' => '#a8a8a8', 'short' => 'C'],
        'right'   => ['label' => 'Droite',     'color' => '#e84c3d', 'short' => 'D'],
        'unknown' => ['label' => 'Non classé', 'color' => '#6b6459', 'short' => '·'],
    ];
@endphp

<section class="grimba-story-articles">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="m-0" style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:24px; letter-spacing:-0.2px;">
            <span style="opacity:0.55;">{{ $totalCount }}</span>
            {{ $totalCount === 1 ? 'article' : 'articles' }}
        </h2>
        <div class="grimba-story-articles__tabs" role="tablist" data-grimba-cluster-tabs
             style="display:flex; gap:4px; border-radius:9999px; background:rgba(0,0,0,0.04); padding:4px;">
            <button type="button" data-bias-tab="all" role="tab" aria-selected="true"
                    style="padding:6px 12px; border-radius:9999px; border:none; background:var(--gn-ink,#1a1713); color:var(--gn-paper,#f6f1e8); font-weight:700; font-size:13px;">
                Tous · {{ $totalCount }}
            </button>
            @foreach(['left', 'center', 'right'] as $b)
                @if($countLabels[$b] > 0)
                    <button type="button" data-bias-tab="{{ $b }}" role="tab" aria-selected="false"
                            style="padding:6px 12px; border-radius:9999px; border:none; background:transparent; color:var(--gn-ink,#1a1713); font-weight:600; font-size:13px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $biasMeta[$b]['color'] }}; margin-right:4px;"></span>
                        {{ $biasMeta[$b]['label'] }} · {{ $countLabels[$b] }}
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    <ul class="list-unstyled m-0" data-grimba-cluster-list>
        @foreach(['left', 'center', 'right', 'unknown'] as $bucket)
            @foreach($byBias[$bucket] as $cp)
                @php
                    $isCurrent = (int) $cp->id === (int) $currentPost->id;
                    $meta = $biasMeta[$bucket];
                @endphp
                <li data-bias="{{ $bucket }}"
                    class="grimba-story-article {{ $isCurrent ? 'grimba-story-article--current' : '' }}"
                    style="
                        padding: 16px 18px;
                        border: 1px solid {{ $isCurrent ? $meta['color'] . '55' : 'rgba(26,23,19,0.08)' }};
                        border-left: 4px solid {{ $meta['color'] }};
                        border-radius: 12px;
                        margin-bottom: 14px;
                        background: {{ $isCurrent ? $meta['color'] . '0d' : 'rgba(255,255,255,0.55)' }};
                    ">

                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2 small">
                        <strong style="font-family:'Public Sans',system-ui,sans-serif;">{{ $cp->source_name ?? '—' }}</strong>
                        @if(! empty($cp->source_id))
                            @php
                                $src = \Illuminate\Support\Facades\DB::table('news_sources')->where('id', $cp->source_id)->first();
                            @endphp
                            @if($src && $src->ownership_type)
                                <span class="opacity-60">·</span>
                                <span class="opacity-75">{{ ucfirst((string) $src->ownership_type) }}</span>
                            @endif
                            @if($src && $src->credibility_score)
                                <span class="opacity-60">·</span>
                                <span class="opacity-75">Crédibilité {{ $src->credibility_score }}</span>
                            @endif
                        @endif
                        <span class="ms-auto" style="
                            display:inline-flex; align-items:center; gap:4px;
                            padding:3px 10px; border-radius:9999px;
                            background:{{ $meta['color'] }}1a; color:{{ $meta['color'] }};
                            font-size:11px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;
                        ">
                            {{ $meta['label'] }}
                        </span>
                        @if($isCurrent)
                            <span style="
                                padding:3px 10px; border-radius:9999px;
                                background:var(--gn-ink,#1a1713); color:var(--gn-paper,#f6f1e8);
                                font-size:11px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;
                            ">Vous lisez</span>
                        @endif
                    </div>

                    <h3 style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:18px; line-height:1.3; letter-spacing:-0.2px; margin:0 0 8px;">
                        @if($isCurrent)
                            {{ $cp->name }}
                        @else
                            <a href="{{ $cp->url ?? '#' }}" style="color:var(--gn-ink,#1a1713); text-decoration:none;">
                                {{ $cp->name }}
                            </a>
                        @endif
                    </h3>

                    @if($cp->description)
                        <p class="small opacity-85 mb-2" style="line-height:1.5;">
                            {{ Str::limit(strip_tags($cp->description), 220) }}
                        </p>
                    @endif

                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="opacity-60">
                            {{ $cp->created_at ? $cp->created_at->locale('fr')->diffForHumans() : '' }}
                        </span>
                        @if(! $isCurrent)
                            <a href="{{ $cp->url ?? '#' }}" style="color:#c0392b; text-decoration:underline; font-weight:600;">
                                Lire l'article →
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        @endforeach
    </ul>
</section>

<script>
    (function () {
        const tabs = document.querySelectorAll('[data-grimba-cluster-tabs] [data-bias-tab]');
        const items = document.querySelectorAll('[data-grimba-cluster-list] [data-bias]');
        if (! tabs.length || ! items.length) return;

        function activate(filter) {
            tabs.forEach(t => {
                const isActive = t.dataset.biasTab === filter;
                t.setAttribute('aria-selected', String(isActive));
                t.style.background = isActive ? 'var(--gn-ink, #1a1713)' : 'transparent';
                t.style.color = isActive ? 'var(--gn-paper, #f6f1e8)' : 'var(--gn-ink, #1a1713)';
            });
            items.forEach(li => {
                const bias = li.dataset.bias;
                li.style.display = (filter === 'all' || bias === filter) ? '' : 'none';
            });
        }

        tabs.forEach(t => t.addEventListener('click', () => activate(t.dataset.biasTab)));
    })();
</script>
