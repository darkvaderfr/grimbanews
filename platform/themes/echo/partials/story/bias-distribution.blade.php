@php
    /**
     * Bias Distribution panel (right sidebar).
     *
     * Compact story-side cockpit for bias mix, source origins, and outlet
     * logos. The article list below owns filtering; this panel only routes
     * clicks into those existing tabs.
     *
     * @var \Illuminate\Database\Eloquent\Collection $clusterPosts
     */
    $sourceMeta = $sourceMeta ?? null;
    if (! $sourceMeta) {
        $sourceIds = $clusterPosts->pluck('source_id')->filter()->unique()->all();
        $sourceMeta = \App\Support\GrimbaSourceMeta::forIds($sourceIds, ['id', 'name', 'website', 'country', 'logo_url', 'logo_status', 'logo_checked_at']);
    }

    $sourcesByBias = ['left' => [], 'center' => [], 'right' => [], 'unknown' => []];
    $seen = [];

    foreach ($clusterPosts as $cp) {
        $name = trim((string) ($cp->source_name ?? ''));
        if ($name === '') {
            continue;
        }

        $key = mb_strtolower($name);
        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $bias = $cp->bias_rating ?? 'unknown';
        if (! isset($sourcesByBias[$bias])) {
            $bias = 'unknown';
        }

        $logo = $cp->source_id && isset($sourceMeta[$cp->source_id]) ? $sourceMeta[$cp->source_id] : null;
        $country = $logo->country ?? null;
        $originKey = \App\Support\GrimbaSourceBreakdown::originKeyForCountry($country);

        $sourcesByBias[$bias][] = [
            'id' => $cp->source_id,
            'name' => $name,
            'website' => $logo->website ?? null,
            'logo' => $logo,
            'country' => \App\Support\GrimbaSourceBreakdown::countryLabel($country),
            'origin_key' => $originKey,
            'origin_label' => \App\Support\GrimbaSourceBreakdown::originLabel($originKey),
            'origin_color' => \App\Support\GrimbaSourceBreakdown::originColor($originKey),
        ];
    }

    $counts = [
        'left' => count($sourcesByBias['left']),
        'center' => count($sourcesByBias['center']),
        'right' => count($sourcesByBias['right']),
    ];
    $known = $counts['left'] + $counts['center'] + $counts['right'];
    $pct = [
        'left' => $known ? (int) round($counts['left'] * 100 / $known) : 0,
        'center' => $known ? (int) round($counts['center'] * 100 / $known) : 0,
        'right' => $known ? (int) round($counts['right'] * 100 / $known) : 0,
    ];

    $biasMeta = [
        'left' => ['label' => __('Gauche'), 'short' => 'L', 'color' => '#3b82f6'],
        'center' => ['label' => __('Centre'), 'short' => 'C', 'color' => '#a8a8a8'],
        'right' => ['label' => __('Droite'), 'short' => 'R', 'color' => '#e84c3d'],
    ];

    $dominant = collect($pct)->sortDesc()->keys()->first();
    $dominantPct = $dominant ? $pct[$dominant] : 0;
    $maxCount = max(1, max($counts));
    $minCount = $known > 0 ? min($counts) : 0;
    $balanceScore = $known > 0 ? (int) round($minCount * 100 / $maxCount) : 0;

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
    {{-- No ranked sources in this cluster. --}}
@else
    <aside class="grimba-story-distribution glass-panel p-3 mb-3">
        <style>
            .grimba-story-distribution {
                --gsd-ink: var(--gn-ink, #171717);
                --gsd-muted: rgba(23, 23, 23, .66);
                --gsd-line: rgba(23, 23, 23, .12);
                --gsd-card: rgba(255, 255, 255, .72);
                --gsd-track: rgba(23, 23, 23, .10);
                position: relative;
                overflow: hidden;
                color: var(--gsd-ink);
                border-radius: 18px;
                background:
                    linear-gradient(135deg, rgba(59, 130, 246, .10), transparent 32%),
                    linear-gradient(155deg, rgba(232, 76, 61, .10), transparent 55%),
                    rgba(255, 255, 255, .62);
            }

            [data-bs-theme="dark"] .grimba-story-distribution,
            body[data-theme="dark"] .grimba-story-distribution {
                --gsd-ink: #fffaf0;
                --gsd-muted: rgba(255, 250, 240, .74);
                --gsd-line: rgba(255, 250, 240, .16);
                --gsd-card: rgba(24, 22, 17, .76);
                --gsd-track: rgba(255, 250, 240, .12);
                background:
                    linear-gradient(135deg, rgba(76, 117, 255, .14), transparent 34%),
                    linear-gradient(155deg, rgba(232, 76, 61, .14), transparent 58%),
                    rgba(18, 16, 12, .82);
            }

            .grimba-story-distribution__top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 12px;
            }

            .grimba-story-distribution__kicker,
            .grimba-story-distribution__label,
            .grimba-story-distribution__column-head span {
                color: var(--gsd-muted);
                font-size: 11px;
                font-weight: 850;
                letter-spacing: 0;
                line-height: 1.1;
                text-transform: uppercase;
            }

            .grimba-story-distribution__title {
                margin: 3px 0 0;
                color: var(--gsd-ink);
                font: 800 19px/1.05 "Fraunces", Georgia, serif;
                letter-spacing: 0;
            }

            .grimba-story-distribution__score {
                min-width: 72px;
                text-align: right;
            }

            .grimba-story-distribution__score strong {
                display: block;
                color: var(--gsd-ink);
                font: 850 27px/1 "Fraunces", Georgia, serif;
            }

            .grimba-story-distribution__score span {
                color: var(--gsd-muted);
                font-size: 11px;
                font-weight: 800;
            }

            .grimba-story-distribution__signal {
                padding: 12px;
                border: 1px solid var(--gsd-line);
                border-radius: 16px;
                background: linear-gradient(180deg, var(--gsd-card), rgba(255, 255, 255, .38));
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .10);
                margin-bottom: 10px;
            }

            [data-bs-theme="dark"] .grimba-story-distribution__signal,
            body[data-theme="dark"] .grimba-story-distribution__signal {
                background: linear-gradient(180deg, var(--gsd-card), rgba(18, 16, 12, .48));
            }

            .grimba-story-distribution__bar {
                display: flex;
                height: 18px;
                overflow: hidden;
                border-radius: 999px;
                background: var(--gsd-track);
                box-shadow: inset 0 0 0 1px var(--gsd-line), 0 14px 26px rgba(0, 0, 0, .08);
            }

            .grimba-story-distribution__bar button {
                position: relative;
                width: var(--w);
                min-width: 42px;
                border: 1px solid rgba(255, 255, 255, .34);
                padding: 0;
                cursor: pointer;
                background: linear-gradient(90deg, color-mix(in srgb, var(--dot) 72%, #fff), var(--dot));
                box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .08);
                transition: filter .16s ease, transform .16s ease, box-shadow .16s ease;
            }

            .grimba-story-distribution__bar button:hover {
                filter: saturate(1.14) brightness(1.04);
                transform: translateY(-1px);
            }

            .grimba-story-distribution__bar button[aria-pressed="true"] {
                box-shadow:
                    inset 0 0 0 2px rgba(255, 255, 255, .86),
                    0 0 0 2px color-mix(in srgb, var(--dot) 45%, transparent);
            }

            .grimba-story-distribution__bar button:focus-visible {
                outline: 2px solid var(--gsd-ink);
                outline-offset: -3px;
            }

            .grimba-story-distribution__axis {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
                margin-top: 10px;
                color: var(--gsd-muted);
                font-size: 11px;
                font-weight: 800;
            }

            .grimba-story-distribution__axis span:nth-child(2) {
                text-align: center;
            }

            .grimba-story-distribution__axis span:nth-child(3) {
                text-align: right;
            }

            .grimba-story-distribution__dominant {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: 8px;
                align-items: center;
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid var(--gsd-line);
            }

            .grimba-story-distribution__dominant strong {
                display: block;
                margin-top: 2px;
                color: var(--gsd-ink);
                font: 850 20px/1.05 "Fraunces", Georgia, serif;
            }

            .grimba-story-distribution__dominant em {
                color: var(--gsd-muted);
                font-size: 12px;
                font-style: normal;
                font-weight: 750;
            }

            .grimba-story-distribution__origin {
                padding: 12px;
                border: 1px solid var(--gsd-line);
                border-radius: 16px;
                background: color-mix(in srgb, #7c3aed 7%, var(--gsd-card));
                margin-bottom: 10px;
            }

            .grimba-story-distribution__origin-bar {
                display: flex;
                height: 12px;
                overflow: hidden;
                border-radius: 999px;
                background: var(--gsd-track);
                margin: 8px 0 10px;
            }

            .grimba-story-distribution__origin-bar span {
                display: block;
                width: var(--w);
                min-width: 4px;
                background: linear-gradient(90deg, color-mix(in srgb, var(--dot) 64%, #fff), var(--dot));
            }

            .grimba-story-distribution__origin-chips,
            .grimba-story-distribution__country-chips {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }

            .grimba-story-distribution__chip {
                display: inline-flex;
                max-width: 100%;
                align-items: center;
                gap: 5px;
                border: 1px solid var(--gsd-line);
                border-radius: 999px;
                padding: 4px 8px;
                background: color-mix(in srgb, var(--dot) 10%, rgba(255, 255, 255, .62));
                color: var(--gsd-ink);
                font-size: 11px;
                font-weight: 800;
                line-height: 1.1;
                overflow-wrap: anywhere;
            }

            [data-bs-theme="dark"] .grimba-story-distribution__chip,
            body[data-theme="dark"] .grimba-story-distribution__chip {
                background: color-mix(in srgb, var(--dot) 14%, rgba(20, 18, 14, .72));
            }

            .grimba-story-distribution__columns {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
            }

            .grimba-story-distribution__column {
                min-width: 0;
                display: grid;
                gap: 8px;
                align-content: start;
                padding: 10px;
                border: 1px solid var(--gsd-line);
                border-radius: 14px;
                background: color-mix(in srgb, var(--dot) 7%, var(--gsd-card));
            }

            .grimba-story-distribution__column-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 6px;
            }

            .grimba-story-distribution__column-head strong {
                color: var(--gsd-ink);
                font: 850 18px/1 "Fraunces", Georgia, serif;
            }

            .grimba-story-distribution__logos {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                min-height: 34px;
            }

            .grimba-story-distribution__empty {
                color: var(--gsd-muted);
                font-size: 12px;
                font-weight: 750;
            }

            .grimba-story-distribution__more {
                display: inline-flex;
                width: 30px;
                height: 30px;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--gsd-line);
                border-radius: 50%;
                background: var(--gsd-card);
                color: var(--gsd-muted);
                font-size: 11px;
                font-weight: 850;
            }

            .grimba-story-distribution__unknown {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px dashed var(--gsd-line);
                color: var(--gsd-muted);
                font-size: 12px;
                font-weight: 750;
            }

            @media (max-width: 991.98px) {
                .grimba-story-distribution__columns {
                    grid-template-columns: 1fr;
                }

                .grimba-story-distribution__column {
                    grid-template-columns: minmax(0, 1fr) auto;
                    align-items: center;
                }

                .grimba-story-distribution__logos,
                .grimba-story-distribution__country-chips {
                    grid-column: 1 / -1;
                }
            }
        </style>

        <header class="grimba-story-distribution__top">
            <div>
                <span class="grimba-story-distribution__kicker">{{ __('Sources classées') }}</span>
                <h2 class="grimba-story-distribution__title">{{ __('Distribution des biais') }}</h2>
            </div>
            <div class="grimba-story-distribution__score">
                <strong>{{ $balanceScore }}</strong>
                <span>{{ __('Signal') }}</span>
            </div>
        </header>

        <section class="grimba-story-distribution__signal" aria-label="{{ __('Distribution des biais') }}">
            <div class="grimba-story-distribution__bar" role="group" aria-label="{{ __('Filtrer la liste par camp') }}">
                @foreach(['left', 'center', 'right'] as $biasKey)
                    @if($pct[$biasKey] > 0)
                        <button type="button"
                            data-grimba-bar-side="{{ $biasKey }}"
                            aria-pressed="false"
                            title="{{ __('Filtrer la liste : :side', ['side' => $biasMeta[$biasKey]['label']]) }} · {{ $pct[$biasKey] }}%"
                            aria-label="{{ __('Filtrer la liste : :side', ['side' => $biasMeta[$biasKey]['label']]) }} ({{ $pct[$biasKey] }}%)"
                            style="--dot: {{ $biasMeta[$biasKey]['color'] }}; --w: {{ $pct[$biasKey] }}%;"></button>
                    @endif
                @endforeach
            </div>

            <div class="grimba-story-distribution__axis">
                @foreach(['left', 'center', 'right'] as $biasKey)
                    <span>{{ $biasMeta[$biasKey]['label'] }} {{ $pct[$biasKey] }}%</span>
                @endforeach
            </div>

            <div class="grimba-story-distribution__dominant">
                <div>
                    <span class="grimba-story-distribution__label">{{ __('Camp majoritaire') }}</span>
                    <strong>{{ $biasMeta[$dominant]['label'] ?? __('Non classé') }}</strong>
                </div>
                <em>{{ $dominantPct }}% · {{ trans_choice(':count source classée|:count sources classées', $known, ['count' => $known]) }}</em>
            </div>
        </section>

        @if($originBuckets->isNotEmpty())
            <section class="grimba-story-distribution__origin" aria-label="{{ __('Origine des sources classées') }}">
                <span class="grimba-story-distribution__label">{{ __('Origines éditoriales') }}</span>
                <div class="grimba-story-distribution__origin-bar">
                    @foreach($originBuckets as $bucket)
                        @php
                            $originPct = (int) round($bucket->count * 100 / max(1, $known));
                        @endphp
                        <span title="{{ $bucket->label }} · {{ $originPct }}%" style="--dot: {{ $bucket->color }}; --w: {{ max(1, $originPct) }}%;"></span>
                    @endforeach
                </div>
                <div class="grimba-story-distribution__origin-chips">
                    @foreach($originBuckets->take(4) as $bucket)
                        @php
                            $originPct = (int) round($bucket->count * 100 / max(1, $known));
                        @endphp
                        <span class="grimba-story-distribution__chip" style="--dot: {{ $bucket->color }};">
                            {{ $bucket->label }} {{ $originPct }}%
                        </span>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grimba-story-distribution__columns">
            @foreach(['left', 'center', 'right'] as $biasKey)
                @php
                    $countries = collect($sourcesByBias[$biasKey])->pluck('country')->filter()->unique()->take(3);
                @endphp
                <article class="grimba-story-distribution__column" style="--dot: {{ $biasMeta[$biasKey]['color'] }};">
                    <div class="grimba-story-distribution__column-head">
                        <span>{{ $biasMeta[$biasKey]['label'] }}</span>
                        <strong>{{ $pct[$biasKey] }}%</strong>
                    </div>
                    <div class="grimba-story-distribution__logos">
                        @forelse(array_slice($sourcesByBias[$biasKey], 0, 6) as $entry)
                            {!! Theme::partial('source-logo', [
                                'source_id' => $entry['id'] ?? 0,
                                'name' => $entry['name'],
                                'website' => $entry['website'] ?? null,
                                'logo_url' => $entry['logo']->logo_url ?? null,
                                'logo_status' => $entry['logo']->logo_status ?? 'unknown',
                                'logo_checked_at' => $entry['logo']->logo_checked_at ?? null,
                                'size' => 30,
                                'color' => $biasMeta[$biasKey]['color'],
                            ]) !!}
                        @empty
                            <span class="grimba-story-distribution__empty">{{ __('Aucune') }}</span>
                        @endforelse
                        @if(count($sourcesByBias[$biasKey]) > 6)
                            <span class="grimba-story-distribution__more">+{{ count($sourcesByBias[$biasKey]) - 6 }}</span>
                        @endif
                    </div>
                    <div class="grimba-story-distribution__country-chips">
                        @foreach($countries as $country)
                            <span class="grimba-story-distribution__chip" style="--dot: {{ $biasMeta[$biasKey]['color'] }};">{{ $country }}</span>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>

        @if(! empty($sourcesByBias['unknown']))
            <div class="grimba-story-distribution__unknown">
                {{ __('Biais non classé') }}:
                {{ trans_choice(':count source|:count sources', count($sourcesByBias['unknown']), ['count' => count($sourcesByBias['unknown'])]) }}
            </div>
        @endif
    </aside>

    <script>
        (function () {
            const segments = document.querySelectorAll('[data-grimba-bar-side]');
            if (! segments.length) return;
            function sync(side) {
                segments.forEach(segment => {
                    segment.setAttribute('aria-pressed', String(segment.dataset.grimbaBarSide === side));
                });
            }
            segments.forEach((segment) => {
                segment.addEventListener('click', () => {
                    const side = segment.dataset.grimbaBarSide;
                    sync(side);
                    document.dispatchEvent(new CustomEvent('grimba:cluster-filter', {
                        detail: { side, scroll: true }
                    }));
                });
            });
            document.addEventListener('grimba:cluster-filtered', event => {
                sync(event.detail?.side || 'all');
            });
        })();
    </script>
@endif
