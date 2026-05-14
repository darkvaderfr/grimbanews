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
        $sourceMeta = \App\Support\GrimbaSourceMeta::forIds($sourceIds, ['id', 'name', 'website', 'country', 'logo_url', 'logo_status', 'logo_checked_at']);
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
        $logo = $cp->source_id && isset($sourceMeta[$cp->source_id]) ? $sourceMeta[$cp->source_id] : null;
        $country = $logo->country ?? null;
        $originKey = \App\Support\GrimbaSourceBreakdown::originKeyForCountry($country);
        $sourcesByBias[$b][] = [
            'id' => $cp->source_id,
            'name' => $name,
            'website' => $website,
            'logo' => $logo,
            'country' => \App\Support\GrimbaSourceBreakdown::countryLabel($country),
            'origin_key' => $originKey,
            'origin_label' => \App\Support\GrimbaSourceBreakdown::originLabel($originKey),
            'origin_color' => \App\Support\GrimbaSourceBreakdown::originColor($originKey),
        ];
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
        'left'   => ['label' => __('Gauche'), 'color' => '#3b82f6'],
        'center' => ['label' => __('Centre'), 'color' => '#a8a8a8'],
        'right'  => ['label' => __('Droite'), 'color' => '#e84c3d'],
    ];

    $originEntries = collect(array_merge($sourcesByBias['left'], $sourcesByBias['center'], $sourcesByBias['right']));
    $originBuckets = $originEntries
        ->groupBy('origin_key')
        ->map(function ($items, string $key) {
            $first = $items->first();

            return (object) [
                'key' => $key,
                'label' => $first['origin_label'] ?? \App\Support\GrimbaSourceBreakdown::originLabel($key),
                'color' => $first['origin_color'] ?? \App\Support\GrimbaSourceBreakdown::originColor($key),
                'count' => $items->count(),
                'countries' => $items->pluck('country')->unique()->take(4)->values()->all(),
            ];
        })
        ->sortByDesc('count')
        ->values();
@endphp

@if($known === 0)
    {{-- No biased sources in this cluster — skip the panel entirely
         instead of rendering an empty/fake-balanced bar. --}}
@else
    <aside class="grimba-story-distribution glass-panel p-3 mb-3">
        <style>
            .grimba-story-distribution__origin {
                border-top: 1px solid rgba(20, 18, 14, .10);
                border-bottom: 1px solid rgba(20, 18, 14, .10);
                padding: 10px 0;
                margin: 0 0 14px;
            }
            [data-bs-theme="dark"] .grimba-story-distribution__origin {
                border-color: rgba(248, 243, 234, .16);
            }
            .grimba-story-distribution__origin-bar {
                display: flex;
                height: 10px;
                overflow: hidden;
                border-radius: 999px;
                background: rgba(20, 18, 14, .10);
                margin-bottom: 8px;
            }
            [data-bs-theme="dark"] .grimba-story-distribution__origin-bar {
                background: rgba(248, 243, 234, .12);
            }
            .grimba-story-distribution__origin-bar span {
                display: block;
                width: var(--w);
                min-width: 4px;
                background: linear-gradient(90deg, color-mix(in srgb, var(--dot) 64%, #fff), var(--dot));
            }
            .grimba-story-distribution__origin-chips {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }
            .grimba-story-distribution__origin-chip {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                border: 1px solid rgba(20, 18, 14, .10);
                border-radius: 999px;
                padding: 3px 7px;
                background: color-mix(in srgb, var(--dot) 9%, rgba(255, 255, 255, .68));
                color: inherit;
                font-size: 11px;
                font-weight: 800;
            }
            [data-bs-theme="dark"] .grimba-story-distribution__origin-chip {
                border-color: rgba(248, 243, 234, .15);
                background: color-mix(in srgb, var(--dot) 12%, rgba(20, 18, 14, .72));
            }
        </style>
        <h2 class="h6 mb-2" style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; font-size:13px; opacity:0.75;">
            {{ __('Distribution des biais') }}
        </h2>
        <p class="small opacity-75 mb-2">
            {{ trans_choice(':count source classée|:count sources classées', $known, ['count' => $known]) }}
            @php
                $dominant = array_keys($pct, max($pct))[0] ?? null;
                $dominantPct = $dominant ? $pct[$dominant] : 0;
            @endphp
            @if($dominant)
                · {{ $dominantPct }}%&nbsp;{{ $biasMeta[$dominant]['label'] }}
            @endif
        </p>

        {{-- S308: clickable bar segments. Each segment is a button that
              filters the article-list below to its side (left/center/right).
              Wires up to the same data-bias-tab tabs in article-list.blade.php. --}}
        <div role="group" aria-label="{{ __('Filtrer la liste par camp') }}"
             style="display:flex;height:14px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.08); margin-bottom:14px;">
            @if($pct['left'] > 0)
                <button type="button"
                        data-grimba-bar-side="left"
                        title="{{ __('Filtrer Gauche') }} · {{ $pct['left'] }}%"
                        aria-label="{{ __('Filtrer la liste : Gauche') }} ({{ $pct['left'] }}%)"
                        style="width:{{ $pct['left'] }}%;background:#3b82f6;border:0;padding:0;cursor:pointer;"></button>
            @endif
            @if($pct['center'] > 0)
                <button type="button"
                        data-grimba-bar-side="center"
                        title="{{ __('Filtrer Centre') }} · {{ $pct['center'] }}%"
                        aria-label="{{ __('Filtrer la liste : Centre') }} ({{ $pct['center'] }}%)"
                        style="width:{{ $pct['center'] }}%;background:#a8a8a8;border:0;padding:0;cursor:pointer;"></button>
            @endif
            @if($pct['right'] > 0)
                <button type="button"
                        data-grimba-bar-side="right"
                        title="{{ __('Filtrer Droite') }} · {{ $pct['right'] }}%"
                        aria-label="{{ __('Filtrer la liste : Droite') }} ({{ $pct['right'] }}%)"
                        style="width:{{ $pct['right'] }}%;background:#e84c3d;border:0;padding:0;cursor:pointer;"></button>
            @endif
        </div>

        <div class="d-flex justify-content-between small mb-3">
            <span style="color:#3b82f6;font-weight:600;">{{ $biasMeta['left']['label'] }} {{ $pct['left'] }}%</span>
            <span style="color:#a8a8a8;font-weight:600;">{{ $biasMeta['center']['label'] }} {{ $pct['center'] }}%</span>
            <span style="color:#e84c3d;font-weight:600;">{{ $biasMeta['right']['label'] }} {{ $pct['right'] }}%</span>
        </div>

        @if($originBuckets->isNotEmpty())
            <div class="grimba-story-distribution__origin" aria-label="{{ __('Origine des sources classées') }}">
                <div class="small mb-2" style="font-weight:800; text-transform:uppercase; letter-spacing:.35px; opacity:.72;">
                    {{ __('Origines') }}
                </div>
                <div class="grimba-story-distribution__origin-bar">
                    @foreach($originBuckets as $bucket)
                        @php($originPct = (int) round($bucket->count * 100 / max(1, $known)))
                        <span title="{{ $bucket->label }} · {{ $originPct }}%" style="--dot: {{ $bucket->color }}; --w: {{ max(1, $originPct) }}%;"></span>
                    @endforeach
                </div>
                <div class="grimba-story-distribution__origin-chips">
                    @foreach($originBuckets->take(4) as $bucket)
                        @php($originPct = (int) round($bucket->count * 100 / max(1, $known)))
                        <span class="grimba-story-distribution__origin-chip" style="--dot: {{ $bucket->color }};">
                            {{ $bucket->label }} {{ $originPct }}%
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;">
            @foreach(['left', 'center', 'right'] as $b)
                <div style="display:flex; flex-direction:column; align-items:center; gap:6px; min-height:1px;">
                    @foreach(array_slice($sourcesByBias[$b], 0, 6) as $entry)
                        {!! Theme::partial('source-logo', [
                            'source_id' => $entry['id'] ?? 0,
                            'name'    => $entry['name'],
                            'website' => $entry['website'] ?? null,
                            'logo_url' => $entry['logo']->logo_url ?? null,
                            'logo_status' => $entry['logo']->logo_status ?? 'unknown',
                            'logo_checked_at' => $entry['logo']->logo_checked_at ?? null,
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
                {{ __('Biais non classé') }} :
                {{ trans_choice(':count source|:count sources', count($sourcesByBias['unknown']), ['count' => count($sourcesByBias['unknown'])]) }}
            </div>
        @endif
    </aside>

    <script>
        /* S308: bar click → activate the matching bias tab in the
           article-list partial below + smooth-scroll to it. Reuses
           the existing tab handler (data-bias-tab) so we have one
           filter source of truth. */
        (function () {
            const segs = document.querySelectorAll('[data-grimba-bar-side]');
            if (! segs.length) return;
            segs.forEach((seg) => {
                seg.addEventListener('click', () => {
                    const side = seg.dataset.grimbaBarSide;
                    const tab = document.querySelector('[data-grimba-cluster-tabs] [data-bias-tab="' + side + '"]');
                    if (tab) {
                        tab.click();
                        const list = document.querySelector('[data-grimba-cluster-list]');
                        if (list) list.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        })();
    </script>
@endif
