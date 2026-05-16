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

    // Spectrum-field: plot each source on a continuous 0-100 bias axis
    // instead of bucketing them into three columns. Within a bucket we
    // spread sources across the bucket's range so logos don't pile up
    // on a single point. This is the cinematic upgrade that replaces
    // the static 3-column grid as the visual centrepiece.
    $bucketRange = [
        'left' => [6.0, 30.0],
        'center' => [38.0, 62.0],
        'right' => [70.0, 94.0],
    ];

    $spectrumChips = [];
    foreach (['left', 'center', 'right'] as $bucketKey) {
        $bucketEntries = $sourcesByBias[$bucketKey];
        $n = count($bucketEntries);
        if ($n === 0) {
            continue;
        }
        [$lo, $hi] = $bucketRange[$bucketKey];
        $span = $hi - $lo;
        foreach (array_values($bucketEntries) as $idx => $entry) {
            $offset = $n === 1 ? ($lo + $span / 2) : ($lo + ($idx / max(1, $n - 1)) * $span);
            $spectrumChips[] = [
                'bias' => $bucketKey,
                'name' => $entry['name'],
                'website' => $entry['website'] ?? null,
                'logo' => $entry['logo'] ?? null,
                'id' => $entry['id'] ?? 0,
                'country' => $entry['country'] ?? null,
                'origin_label' => $entry['origin_label'] ?? null,
                'x' => round($offset, 2),
                'color' => $biasMeta[$bucketKey]['color'],
            ];
        }
    }
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

            /* Spectrum field — continuous bias plot. */
            .grimba-story-spectrum {
                position: relative;
                padding: 18px 6px 14px;
                margin-top: 4px;
                border: 1px solid var(--gsd-line);
                border-radius: 18px;
                background:
                    radial-gradient(120% 100% at 0% 50%, rgba(59, 130, 246, .14), transparent 48%),
                    radial-gradient(120% 100% at 100% 50%, rgba(232, 76, 61, .14), transparent 48%),
                    radial-gradient(70% 100% at 50% 50%, rgba(255, 255, 255, .14), transparent 60%),
                    var(--gsd-card);
                overflow: hidden;
                isolation: isolate;
            }

            [data-bs-theme="dark"] .grimba-story-spectrum,
            body[data-theme="dark"] .grimba-story-spectrum {
                background:
                    radial-gradient(120% 100% at 0% 50%, rgba(76, 117, 255, .22), transparent 48%),
                    radial-gradient(120% 100% at 100% 50%, rgba(232, 76, 61, .22), transparent 48%),
                    radial-gradient(70% 100% at 50% 50%, rgba(255, 255, 255, .04), transparent 60%),
                    rgba(20, 18, 14, .82);
            }

            .grimba-story-spectrum__aurora {
                position: absolute;
                inset: 0;
                z-index: 0;
                pointer-events: none;
                background: linear-gradient(
                    90deg,
                    transparent 0%,
                    rgba(59, 130, 246, .12) 18%,
                    rgba(168, 168, 168, .08) 50%,
                    rgba(232, 76, 61, .12) 82%,
                    transparent 100%
                );
                background-size: 220% 100%;
                animation: grimbaSpectrumAurora 14s ease-in-out infinite alternate;
                mix-blend-mode: screen;
            }

            @keyframes grimbaSpectrumAurora {
                0% { background-position: 0% 0; }
                100% { background-position: 100% 0; }
            }

            .grimba-story-spectrum__axis {
                position: absolute;
                left: 14px;
                right: 14px;
                top: 50%;
                height: 1px;
                background: linear-gradient(
                    90deg,
                    rgba(59, 130, 246, .42),
                    rgba(168, 168, 168, .32) 50%,
                    rgba(232, 76, 61, .42)
                );
                z-index: 1;
                pointer-events: none;
            }

            .grimba-story-spectrum__tick {
                position: absolute;
                top: -3px;
                width: 1px;
                height: 7px;
                background: var(--gsd-line);
                transform: translateX(-50%);
            }

            .grimba-story-spectrum__field {
                position: relative;
                z-index: 2;
                height: 86px;
                margin: 0 14px;
            }

            .grimba-story-spectrum__chip {
                position: absolute;
                top: 50%;
                left: var(--x, 50%);
                transform: translate(-50%, -50%) scale(.6);
                width: 36px;
                height: 36px;
                padding: 0;
                border: none;
                background: transparent;
                cursor: pointer;
                opacity: 0;
                animation: grimbaSpectrumPop .56s cubic-bezier(.22, 1, .36, 1) forwards;
                animation-delay: var(--delay, 0ms);
                transition: transform .22s ease, filter .22s ease;
            }

            @keyframes grimbaSpectrumPop {
                from { opacity: 0; transform: translate(-50%, calc(-50% + 8px)) scale(.4); }
                to   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
            }

            .grimba-story-spectrum__chip:hover,
            .grimba-story-spectrum__chip:focus-visible {
                transform: translate(-50%, calc(-50% - 4px)) scale(1.16);
                filter: drop-shadow(0 6px 18px rgba(0, 0, 0, .22));
                outline: none;
                z-index: 5;
            }

            .grimba-story-spectrum__chip[aria-pressed="true"] {
                transform: translate(-50%, -50%) scale(1.24);
                filter: drop-shadow(0 6px 18px color-mix(in srgb, var(--dot) 60%, transparent));
                z-index: 6;
            }

            .grimba-story-spectrum__chip:not([data-bias-active="true"]) {
                opacity: 0.22;
                filter: grayscale(.7);
            }

            .grimba-story-spectrum__halo {
                position: absolute;
                inset: -4px;
                border-radius: 50%;
                background: radial-gradient(circle, color-mix(in srgb, var(--dot) 80%, transparent), transparent 70%);
                opacity: 0;
                transition: opacity .22s ease;
                pointer-events: none;
            }

            .grimba-story-spectrum__chip:hover .grimba-story-spectrum__halo,
            .grimba-story-spectrum__chip:focus-visible .grimba-story-spectrum__halo,
            .grimba-story-spectrum__chip[aria-pressed="true"] .grimba-story-spectrum__halo {
                opacity: 1;
            }

            .grimba-story-spectrum__avatar {
                position: relative;
                display: grid;
                place-items: center;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: rgba(255, 255, 255, .94);
                box-shadow: 0 4px 14px rgba(0, 0, 0, .12), inset 0 0 0 2px color-mix(in srgb, var(--dot) 70%, transparent);
                overflow: hidden;
            }

            [data-bs-theme="dark"] .grimba-story-spectrum__avatar,
            body[data-theme="dark"] .grimba-story-spectrum__avatar {
                background: rgba(28, 24, 17, .94);
                box-shadow: 0 6px 18px rgba(0, 0, 0, .42), inset 0 0 0 2px color-mix(in srgb, var(--dot) 80%, transparent);
            }

            .grimba-story-spectrum__legend {
                position: relative;
                z-index: 2;
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                margin-top: 8px;
                padding: 0 8px;
                color: var(--gsd-muted);
                font-family: 'JetBrains Mono', ui-monospace, monospace;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .16em;
                text-transform: uppercase;
            }

            .grimba-story-spectrum__legend span:nth-child(2) { text-align: center; }
            .grimba-story-spectrum__legend span:nth-child(3) { text-align: right; }

            @media (prefers-reduced-motion: reduce) {
                .grimba-story-spectrum__aurora { animation: none; }
                .grimba-story-spectrum__chip {
                    opacity: 1;
                    transform: translate(-50%, -50%) scale(1);
                    animation: none;
                }
            }

            @media (max-width: 575.98px) {
                .grimba-story-spectrum__field { height: 76px; }
                .grimba-story-spectrum__chip { width: 30px; height: 30px; }
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
                @include(Theme::getThemeNamespace('partials.info-pill'), [
                    'size' => 'sm',
                    'body' => __('Le score Signal va de 0 à 100. 0 = un seul camp couvre. 100 = équilibre parfait Gauche / Centre / Droite. Plus le score est haut, plus le dossier est multi-perspectives.'),
                ])
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

        @if(! empty($spectrumChips))
            <section class="grimba-story-spectrum"
                     aria-label="{{ __('Distribution des sources sur le spectre politique') }}"
                     data-grimba-spectrum-field>
                <div class="grimba-story-spectrum__axis" aria-hidden="true">
                    <span class="grimba-story-spectrum__tick" style="left: 6%;"></span>
                    <span class="grimba-story-spectrum__tick" style="left: 25%;"></span>
                    <span class="grimba-story-spectrum__tick" style="left: 50%;"></span>
                    <span class="grimba-story-spectrum__tick" style="left: 75%;"></span>
                    <span class="grimba-story-spectrum__tick" style="left: 94%;"></span>
                </div>
                <div class="grimba-story-spectrum__aurora" aria-hidden="true"></div>
                <div class="grimba-story-spectrum__field">
                    @foreach($spectrumChips as $i => $chip)
                        <button type="button"
                                class="grimba-story-spectrum__chip"
                                data-grimba-spectrum-chip
                                data-bias="{{ $chip['bias'] }}"
                                data-bias-active="true"
                                style="--x: {{ $chip['x'] }}%; --dot: {{ $chip['color'] }}; --delay: {{ $i * 38 }}ms;"
                                title="{{ $chip['name'] }}{{ $chip['country'] ? ' · ' . $chip['country'] : '' }}{{ $chip['origin_label'] ? ' · ' . $chip['origin_label'] : '' }}"
                                aria-label="{{ $chip['name'] }} — {{ $biasMeta[$chip['bias']]['label'] }}">
                            <span class="grimba-story-spectrum__halo" aria-hidden="true"></span>
                            <span class="grimba-story-spectrum__avatar">
                                {!! Theme::partial('source-logo', [
                                    'source_id' => $chip['id'] ?? 0,
                                    'name' => $chip['name'],
                                    'website' => $chip['website'] ?? null,
                                    'logo_url' => $chip['logo']->logo_url ?? null,
                                    'logo_status' => $chip['logo']->logo_status ?? 'unknown',
                                    'logo_checked_at' => $chip['logo']->logo_checked_at ?? null,
                                    'size' => 26,
                                    'color' => $chip['color'],
                                ]) !!}
                            </span>
                        </button>
                    @endforeach
                </div>
                <footer class="grimba-story-spectrum__legend" aria-hidden="true">
                    <span>{{ __('Gauche') }}</span>
                    <span>{{ __('Centre') }}</span>
                    <span>{{ __('Droite') }}</span>
                </footer>
            </section>
        @endif

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
            const chips = document.querySelectorAll('[data-grimba-spectrum-chip]');

            // All chips start active (no filter applied).
            chips.forEach(chip => chip.setAttribute('data-bias-active', 'true'));

            function syncBar(side) {
                segments.forEach(segment => {
                    segment.setAttribute('aria-pressed', String(segment.dataset.grimbaBarSide === side));
                });
            }

            function syncChips(side) {
                const active = side && side !== 'all';
                chips.forEach(chip => {
                    const matches = !active || chip.dataset.bias === side;
                    chip.setAttribute('data-bias-active', String(matches));
                    chip.setAttribute('aria-pressed', String(active && chip.dataset.bias === side));
                });
            }

            segments.forEach(segment => {
                segment.addEventListener('click', () => {
                    const side = segment.dataset.grimbaBarSide;
                    syncBar(side);
                    syncChips(side);
                    document.dispatchEvent(new CustomEvent('grimba:cluster-filter', {
                        detail: { side, scroll: true }
                    }));
                });
            });

            chips.forEach(chip => {
                chip.addEventListener('click', () => {
                    const side = chip.dataset.bias;
                    syncBar(side);
                    syncChips(side);
                    document.dispatchEvent(new CustomEvent('grimba:cluster-filter', {
                        detail: { side, scroll: true }
                    }));
                });
            });

            document.addEventListener('grimba:cluster-filtered', event => {
                const side = event.detail?.side || 'all';
                syncBar(side);
                syncChips(side);
            });
        })();
    </script>
@endif
