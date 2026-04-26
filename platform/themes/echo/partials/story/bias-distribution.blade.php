@php
    /**
     * S148 — Bias Distribution panel (right sidebar). Horizontal
     * stacked bar + three columns of source initial-badges grouped
     * by lean. Mirrors the GroundNews block.
     *
     * @var \Illuminate\Database\Eloquent\Collection $clusterPosts
     */
    // S161 — fetch website for each unique source so the logo
    // partial can lift Clearbit + favicon. Map name → website via
    // a single news_sources lookup keyed on the source_id list.
    $sourceMeta = $sourceMeta ?? null;
    if (! $sourceMeta) {
        $sourceIds = $clusterPosts->pluck('source_id')->filter()->unique()->all();
        $sourceMeta = empty($sourceIds) ? collect() :
            \Illuminate\Support\Facades\DB::table('news_sources')
                ->whereIn('id', $sourceIds)
                ->get(['id', 'name', 'website'])
                ->keyBy('id');
    }

    $sourcesByBias = ['left' => [], 'center' => [], 'right' => [], 'unknown' => []];
    $seen = [];
    foreach ($clusterPosts as $cp) {
        $name = trim((string) ($cp->source_name ?? ''));
        if ($name === '') continue;
        $key = mb_strtolower($name);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $b = $cp->bias_rating ?? 'unknown';
        if (! isset($sourcesByBias[$b])) $b = 'unknown';
        $website = $cp->source_id && isset($sourceMeta[$cp->source_id])
            ? $sourceMeta[$cp->source_id]->website
            : null;
        $sourcesByBias[$b][] = ['name' => $name, 'website' => $website];
    }

    $counts = [
        'left'   => count($sourcesByBias['left']),
        'center' => count($sourcesByBias['center']),
        'right'  => count($sourcesByBias['right']),
    ];
    $known = $counts['left'] + $counts['center'] + $counts['right'];
    $pct = [
        'left'   => $known ? round($counts['left']   * 100 / $known) : 0,
        'center' => $known ? round($counts['center'] * 100 / $known) : 0,
        'right'  => $known ? round($counts['right']  * 100 / $known) : 0,
    ];

    $biasMeta = [
        'left'   => ['label' => 'Gauche', 'color' => '#3b82f6'],
        'center' => ['label' => 'Centre', 'color' => '#a8a8a8'],
        'right'  => ['label' => 'Droite', 'color' => '#e84c3d'],
    ];
@endphp

@if($known === 0)
    {{-- No biased sources in this cluster — skip the panel entirely
         instead of rendering an empty/fake-balanced bar. --}}
@else
    <aside class="grimba-story-distribution glass-panel p-3 mb-3">
        <h2 class="h6 mb-2" style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; font-size:13px; opacity:0.75;">
            Distribution des biais
        </h2>
        <p class="small opacity-75 mb-2">
            {{ $known }} {{ $known === 1 ? 'source classée' : 'sources classées' }}
            @php
                $dominant = array_keys($pct, max($pct))[0] ?? null;
                $dominantPct = $dominant ? $pct[$dominant] : 0;
            @endphp
            @if($dominant)
                · {{ $dominantPct }}%&nbsp;{{ $biasMeta[$dominant]['label'] }}
            @endif
        </p>

        <div style="display:flex;height:14px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.08); margin-bottom:14px;">
            <div style="width:{{ $pct['left'] }}%;background:#3b82f6;" title="Gauche {{ $pct['left'] }}%"></div>
            <div style="width:{{ $pct['center'] }}%;background:#a8a8a8;" title="Centre {{ $pct['center'] }}%"></div>
            <div style="width:{{ $pct['right'] }}%;background:#e84c3d;" title="Droite {{ $pct['right'] }}%"></div>
        </div>

        <div class="d-flex justify-content-between small mb-3">
            <span style="color:#3b82f6;font-weight:600;">L {{ $pct['left'] }}%</span>
            <span style="color:#a8a8a8;font-weight:600;">C {{ $pct['center'] }}%</span>
            <span style="color:#e84c3d;font-weight:600;">D {{ $pct['right'] }}%</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;">
            @foreach(['left', 'center', 'right'] as $b)
                <div style="display:flex; flex-direction:column; align-items:center; gap:6px; min-height:1px;">
                    @foreach(array_slice($sourcesByBias[$b], 0, 6) as $entry)
                        {!! Theme::partial('source-logo', [
                            'name'    => $entry['name'],
                            'website' => $entry['website'] ?? null,
                            'size'    => 36,
                            'color'   => $biasMeta[$b]['color'],
                        ]) !!}
                    @endforeach
                    @if(count($sourcesByBias[$b]) > 6)
                        <span style="font-size:11px; opacity:0.6; font-weight:600;">
                            +{{ count($sourcesByBias[$b]) - 6 }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>

        @if(! empty($sourcesByBias['unknown']))
            <div class="mt-3 pt-2 small opacity-60" style="border-top:1px dashed rgba(0,0,0,0.08);">
                Biais non classé : {{ count($sourcesByBias['unknown']) }}
                {{ count($sourcesByBias['unknown']) === 1 ? 'source' : 'sources' }}
            </div>
        @endif
    </aside>
@endif
