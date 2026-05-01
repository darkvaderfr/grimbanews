@php
    /**
     * GroundNews-style story breakdown.
     *
     * Computes coverage from the actual posts/sources inside a story
     * cluster: political bias, source factuality, and ownership.
     *
     * @var \Illuminate\Support\Collection $posts
     */

    $uid = 'gbd-' . substr(md5((string) ($posts->pluck('id')->join('-') ?: uniqid('', true))), 0, 10);

    $breakdown = \App\Support\GrimbaSourceBreakdown::fromPosts($posts);
    $sources = $breakdown['sources'];
    $total = $breakdown['total'];
    $biasBuckets = $breakdown['biasBuckets'];
    $knownBiasBuckets = $breakdown['knownBiasBuckets'];
    $weakestBias = $breakdown['weakestBias'];
    $weakestPct = $breakdown['weakestPct'];
    $factBuckets = $breakdown['factBuckets'];
    $ownershipBuckets = $breakdown['ownershipBuckets'];
    $donutGradient = $breakdown['donutGradient'];
    $topOwner = $breakdown['topOwner'];
    $topOwnerPct = $breakdown['topOwnerPct'];
@endphp

<section class="grimba-breakdown glass-panel p-3 p-md-4 mb-4" id="{{ $uid }}">
    <style>
        #{{ $uid }} {
            --gbd-ink: var(--gn-ink, #171717);
            --gbd-muted: rgba(23, 23, 23, .64);
            --gbd-line: rgba(23, 23, 23, .12);
            --gbd-paper: rgba(255, 255, 255, .86);
            --gbd-surface: rgba(255, 255, 255, .62);
            --gbd-card: rgba(255, 255, 255, .76);
            --gbd-track: rgba(23, 23, 23, .10);
            --gbd-tab: #15130f;
            --gbd-shadow: 0 24px 70px rgba(22, 18, 12, .10);
            color: var(--gbd-ink);
            position: relative;
            overflow: hidden;
            max-width: 1120px;
            margin-inline: auto;
            padding: 14px 16px !important;
        }

        [data-bs-theme="dark"] #{{ $uid }} {
            --gbd-ink: #f8f3ea;
            --gbd-muted: rgba(248, 243, 234, .72);
            --gbd-line: rgba(248, 243, 234, .16);
            --gbd-paper: rgba(15, 14, 11, .88);
            --gbd-surface: rgba(24, 22, 17, .78);
            --gbd-card: rgba(31, 28, 23, .88);
            --gbd-track: rgba(248, 243, 234, .12);
            --gbd-tab: #f8f3ea;
            --gbd-shadow: 0 24px 70px rgba(0, 0, 0, .36);
        }

        #{{ $uid }}::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 18% 8%, rgba(59, 130, 246, .12), transparent 28%),
                radial-gradient(circle at 84% 18%, rgba(209, 40, 84, .10), transparent 32%),
                linear-gradient(135deg, rgba(255, 255, 255, .18), transparent 42%);
        }

        [data-bs-theme="dark"] #{{ $uid }}::before {
            background:
                radial-gradient(circle at 18% 8%, rgba(70, 126, 255, .16), transparent 30%),
                radial-gradient(circle at 84% 18%, rgba(239, 68, 68, .13), transparent 32%),
                linear-gradient(135deg, rgba(255, 255, 255, .05), transparent 46%);
        }

        #{{ $uid }} > * {
            position: relative;
            z-index: 1;
        }

        @keyframes gbd-panel-in {
            from { opacity: 0; transform: translateY(10px) scale(.985); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes gbd-rise {
            from { opacity: 0; transform: translateY(16px) scale(.94); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes gbd-fill {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        @keyframes gbd-donut {
            from { opacity: 0; transform: rotate(-24deg) scale(.88); filter: saturate(.75); }
            to { opacity: 1; transform: rotate(-90deg) scale(1); filter: saturate(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            #{{ $uid }} *,
            #{{ $uid }} *::before,
            #{{ $uid }} *::after {
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .001ms !important;
            }
        }

        #{{ $uid }} .grimba-breakdown__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 12px;
        }

        #{{ $uid }} .grimba-breakdown__title {
            margin: 0;
            font: 700 clamp(18px, 1.6vw, 22px)/1.05 "Fraunces", Georgia, serif;
            letter-spacing: -.02em;
        }

        #{{ $uid }} .grimba-breakdown__tabs {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            position: relative;
            padding: 3px;
            border: 1px solid var(--gbd-line);
            border-radius: 12px;
            background: linear-gradient(180deg, var(--gbd-paper), var(--gbd-surface));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .12);
            overflow: hidden;
        }

        #{{ $uid }} .grimba-breakdown__tabs::before {
            content: "";
            position: absolute;
            top: 4px;
            bottom: 4px;
            left: 4px;
            width: calc((100% - 8px) / 3);
            border-radius: 9px;
            background: var(--gbd-tab);
            box-shadow: 0 8px 18px rgba(0, 0, 0, .18);
            transform: translateX(var(--tab-x, 0));
            transition: transform .24s cubic-bezier(.2,.8,.2,1);
        }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs { --tab-x: 0; }
        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs { --tab-x: 100%; }
        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs { --tab-x: 200%; }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #111827, #2f6fe9);
        }

        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #111827, #18a058);
        }

        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #111827, #a06a00);
        }

        [data-bs-theme="dark"] #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #f8f3ea, #4778ff);
        }

        [data-bs-theme="dark"] #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #f8f3ea, #22c55e);
        }

        [data-bs-theme="dark"] #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #f8f3ea, #d69a00);
        }

        #{{ $uid }} input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        #{{ $uid }} .grimba-breakdown__tab {
            margin: 0;
            padding: 6px 10px;
            border-radius: 10px;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            color: var(--gbd-muted);
            cursor: pointer;
            position: relative;
            z-index: 1;
            transition: background .16s ease, color .16s ease, box-shadow .16s ease;
        }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-bias"],
        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-fact"],
        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-owner"] {
            color: #fff;
            text-shadow: 0 1px 10px rgba(0, 0, 0, .42);
        }

        [data-bs-theme="dark"] #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-bias"],
        [data-bs-theme="dark"] #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-fact"],
        [data-bs-theme="dark"] #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-owner"] {
            color: #15130f;
            text-shadow: none;
        }

        #{{ $uid }} .grimba-breakdown__panel {
            display: none;
            padding-top: 10px;
        }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__panels [data-panel="bias"],
        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__panels [data-panel="fact"],
        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__panels [data-panel="owner"] {
            display: block;
            animation: gbd-panel-in .26s ease both;
        }

        #{{ $uid }} .grimba-breakdown__callout {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: var(--gbd-muted);
            font-size: clamp(13px, 1.2vw, 15px);
            line-height: 1.3;
        }

        #{{ $uid }} .grimba-breakdown__callout strong {
            color: var(--gbd-ink);
        }

        #{{ $uid }} .grimba-breakdown__icon {
            display: inline-flex;
            width: 26px;
            height: 26px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--gbd-tab);
            color: #fff;
            flex: 0 0 auto;
            font-size: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, .14);
        }

        [data-bs-theme="dark"] #{{ $uid }} .grimba-breakdown__icon {
            color: #15130f;
        }

        #{{ $uid }} .grimba-breakdown__bias-lanes {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 6px;
            align-items: stretch;
            margin: 8px 0;
        }

        #{{ $uid }} .grimba-breakdown__lane {
            min-height: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: stretch;
            gap: 6px;
            padding: 8px 10px;
            border: 1px solid var(--gbd-line);
            border-radius: 12px;
            background:
                linear-gradient(180deg, color-mix(in srgb, var(--lane-color) 8%, transparent), transparent),
                linear-gradient(180deg, var(--gbd-card), var(--gbd-surface));
            box-shadow: inset 0 -8px 16px color-mix(in srgb, var(--lane-color) 7%, transparent);
        }

        #{{ $uid }} .grimba-breakdown__lane-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 6px;
            color: var(--gbd-muted);
            font-size: 11px;
            font-weight: 800;
            line-height: 1.1;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        #{{ $uid }} .grimba-breakdown__lane-head strong {
            color: var(--gbd-ink);
            font: 800 15px/1 "Fraunces", Georgia, serif;
        }

        #{{ $uid }} .grimba-breakdown__lane-sources {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            align-items: center;
            min-height: 24px;
        }

        #{{ $uid }} .grimba-breakdown__logo-pop {
            display: inline-flex;
            animation: gbd-rise .34s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--delay, 0ms);
        }

        #{{ $uid }} .grimba-breakdown__more {
            display: inline-flex;
            width: 32px;
            height: 32px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1px solid var(--gbd-line);
            background: var(--gbd-paper);
            color: var(--gbd-muted);
            font-weight: 800;
        }

        #{{ $uid }} .grimba-breakdown__bias-bar {
            display: flex;
            height: 14px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--gbd-track);
            box-shadow: inset 0 0 0 1px var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__bias-bar span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: var(--w);
            color: #fff;
            font-weight: 800;
            font-size: 10px;
            line-height: 1;
            text-shadow: 0 1px 4px rgba(0, 0, 0, .42);
            transform-origin: left;
            animation: gbd-fill .7s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--delay, 0ms);
        }

        #{{ $uid }} .grimba-breakdown__rows {
            display: grid;
            gap: 4px;
        }

        #{{ $uid }} .grimba-breakdown__row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid var(--gbd-line);
            font-size: 13px;
        }

        #{{ $uid }} .grimba-breakdown__legend {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            color: var(--gbd-muted);
            font-weight: 700;
        }

        #{{ $uid }} .grimba-breakdown__dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--dot);
            flex: 0 0 auto;
            box-shadow: 0 0 0 6px color-mix(in srgb, var(--dot) 12%, transparent);
        }

        #{{ $uid }} .grimba-breakdown__logos {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 6px;
        }

        #{{ $uid }} .grimba-breakdown__metric {
            display: grid;
            grid-template-columns: minmax(110px, 220px) auto;
            gap: 10px;
            align-items: center;
        }

        #{{ $uid }} .grimba-breakdown__mini-track {
            height: 9px;
            border-radius: 999px;
            background: var(--gbd-track);
            overflow: hidden;
            box-shadow: inset 0 0 0 1px var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__mini-fill {
            display: block;
            width: var(--w);
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, color-mix(in srgb, var(--dot) 62%, #fff), var(--dot));
            transform-origin: left;
            animation: gbd-fill .72s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--delay, 0ms);
        }

        #{{ $uid }} .grimba-breakdown__donut {
            width: min(180px, 100%);
            aspect-ratio: 1;
            margin: 0 auto;
            border-radius: 50%;
            background: conic-gradient({{ $donutGradient }});
            position: relative;
            transform: rotate(-90deg);
            animation: gbd-donut .72s cubic-bezier(.2,.8,.2,1) both;
            box-shadow:
                inset 0 0 0 14px rgba(255, 255, 255, .9),
                0 0 0 1px var(--gbd-line),
                0 14px 36px rgba(0, 0, 0, .12);
        }

        [data-bs-theme="dark"] #{{ $uid }} .grimba-breakdown__donut {
            box-shadow: inset 0 0 0 14px rgba(15, 14, 11, .9), 0 14px 30px rgba(0, 0, 0, .42);
        }

        #{{ $uid }} .grimba-breakdown__donut::after {
            content: "";
            position: absolute;
            inset: 31%;
            border-radius: 50%;
            background: var(--gbd-paper);
            box-shadow: 0 0 0 1px var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__donut-center {
            position: absolute;
            inset: 34%;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--gbd-ink);
            transform: rotate(90deg);
        }

        #{{ $uid }} .grimba-breakdown__donut-center strong {
            display: block;
            font-size: clamp(20px, 3.4vw, 30px);
            line-height: 1;
        }

        #{{ $uid }} .grimba-breakdown__owner-grid {
            display: grid;
            grid-template-columns: minmax(220px, 320px) 1fr;
            gap: 24px;
            align-items: center;
        }

        #{{ $uid }} .grimba-breakdown__insight-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 16px;
        }

        #{{ $uid }} .grimba-breakdown__stat {
            padding: 12px;
            border: 1px solid var(--gbd-line);
            border-radius: 18px;
            background: var(--gbd-card);
        }

        #{{ $uid }} .grimba-breakdown__stat strong {
            display: block;
            font: 800 22px/1 "Fraunces", Georgia, serif;
        }

        #{{ $uid }} .grimba-breakdown__stat span {
            color: var(--gbd-muted);
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            #{{ $uid }} .grimba-breakdown__top {
                align-items: flex-start;
                flex-direction: column;
            }

            #{{ $uid }} .grimba-breakdown__bias-lanes {
                grid-template-columns: repeat(2, 1fr);
            }

            #{{ $uid }} .grimba-breakdown__lane {
            min-height: 0;
            }

            #{{ $uid }} .grimba-breakdown__donut {
                width: min(320px, 100%);
            }

            #{{ $uid }} .grimba-breakdown__row,
            #{{ $uid }} .grimba-breakdown__metric {
                grid-template-columns: 1fr;
            }

            #{{ $uid }} .grimba-breakdown__owner-grid {
                grid-template-columns: 1fr;
            }

            #{{ $uid }} .grimba-breakdown__logos {
                justify-content: flex-start;
            }

            #{{ $uid }} .grimba-breakdown__insight-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="grimba-breakdown__top">
        <div>
            <span class="grimba-methodology__kicker">{{ __('Analyse des sources') }}</span>
            <h2 class="grimba-breakdown__title">{{ __('Breakdown') }}</h2>
        </div>
        <span class="small opacity-75">
            {{ trans_choice(':count source analysée|:count sources analysées', $sources->count(), ['count' => $sources->count()]) }}
        </span>
    </div>

    <input type="radio" id="{{ $uid }}-bias" name="{{ $uid }}-tab" checked>
    <input type="radio" id="{{ $uid }}-fact" name="{{ $uid }}-tab">
    <input type="radio" id="{{ $uid }}-owner" name="{{ $uid }}-tab">

    <div class="grimba-breakdown__tabs" role="tablist" aria-label="{{ __('Analyse du dossier') }}">
        <label class="grimba-breakdown__tab" for="{{ $uid }}-bias" role="tab">{{ __('Biais') }}</label>
        <label class="grimba-breakdown__tab" for="{{ $uid }}-fact" role="tab">{{ __('Factualité') }}</label>
        <label class="grimba-breakdown__tab" for="{{ $uid }}-owner" role="tab">{{ __('Propriété') }}</label>
    </div>

    <div class="grimba-breakdown__panels">
        <div class="grimba-breakdown__panel" data-panel="bias">
            <div class="grimba-breakdown__callout">
                <span class="grimba-breakdown__icon">◎</span>
                <span>
                    {{ __('Cette histoire n’a que') }}
                    <strong>{{ $weakestPct }}% {{ $weakestBias?->label }}</strong>
                    {{ __('de couverture politique.') }}
                </span>
            </div>

            <div class="grimba-breakdown__bias-lanes">
                @foreach($biasBuckets as $bucket)
                    <div class="grimba-breakdown__lane" style="--lane-color: {{ $bucket->color }};">
                        <div class="grimba-breakdown__lane-head">
                            <span>{{ $bucket->label }}</span>
                            <strong>{{ (int) round($bucket->count * 100 / $total) }}%</strong>
                        </div>
                        <div class="grimba-breakdown__lane-sources">
                            @foreach($bucket->items->take(4) as $source)
                                <span class="grimba-breakdown__logo-pop" style="--delay: {{ $loop->index * 55 }}ms;">
                                    {!! Theme::partial('source-logo', [
                                        'source_id' => $source->key,
                                        'name' => $source->name,
                                        'website' => $source->website,
                                        'logo_url' => $source->logo_url ?? null,
                                        'logo_status' => $source->logo_status ?? 'unknown',
                                        'logo_checked_at' => $source->logo_checked_at ?? null,
                                        'size' => 22,
                                        'color' => $bucket->color,
                                    ]) !!}
                                </span>
                            @endforeach
                            @if($bucket->count > 4)
                                <span class="grimba-breakdown__more">+{{ $bucket->count - 4 }}</span>
                            @elseif($bucket->count === 0)
                                <span class="small opacity-65">{{ __('Aucune') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- S323: removed the redundant __insight-grid stat block — it
                  repeated the same percentages already shown in the 4 lane
                  cards above and the stacked bar below, tripling the
                  vertical footprint without adding information. --}}
            <div class="grimba-breakdown__bias-bar">
                <span style="--w: {{ max(1, (int) round(($biasBuckets->firstWhere('key', 'left')?->count ?? 0) * 100 / $total)) }}%; --delay: 60ms; background:#3b82f6;">L {{ (int) round(($biasBuckets->firstWhere('key', 'left')?->count ?? 0) * 100 / $total) }}%</span>
                <span style="--w: {{ max(1, (int) round(($biasBuckets->firstWhere('key', 'center')?->count ?? 0) * 100 / $total)) }}%; --delay: 140ms; background:#9ca3af;">C {{ (int) round(($biasBuckets->firstWhere('key', 'center')?->count ?? 0) * 100 / $total) }}%</span>
                <span style="--w: {{ max(1, (int) round(($biasBuckets->firstWhere('key', 'right')?->count ?? 0) * 100 / $total)) }}%; --delay: 220ms; background:#ef4444;">R {{ (int) round(($biasBuckets->firstWhere('key', 'right')?->count ?? 0) * 100 / $total) }}%</span>
            </div>
        </div>

        <div class="grimba-breakdown__panel" data-panel="fact">
            <div class="grimba-breakdown__callout">
                <span class="grimba-breakdown__icon">✓</span>
                <span>{{ __('Factualité estimée depuis le score de crédibilité de chaque source.') }}</span>
            </div>

            <div class="grimba-breakdown__rows">
                @foreach($factBuckets as $bucket)
                    @php($pct = (int) round($bucket->items->count() * 100 / $total))
                    <div class="grimba-breakdown__row" style="--dot: {{ $bucket->color }};">
                        <div class="grimba-breakdown__legend">
                            <span class="grimba-breakdown__dot" style="--dot: {{ $bucket->color }};"></span>
                            <span>{{ $bucket->label }}</span>
                            <span class="opacity-65">{{ $bucket->range }}</span>
                        </div>
                        <div class="grimba-breakdown__metric">
                            <div class="grimba-breakdown__mini-track">
                                <span class="grimba-breakdown__mini-fill" style="--dot: {{ $bucket->color }}; --w: {{ $pct }}%; --delay: {{ $loop->index * 70 }}ms;"></span>
                            </div>
                            <strong>{{ $pct }}%</strong>
                            <div class="grimba-breakdown__logos">
                                @foreach($bucket->items->take(6) as $source)
                                    {!! Theme::partial('source-logo', [
                                        'source_id' => $source->key,
                                        'name' => $source->name,
                                        'website' => $source->website,
                                        'logo_url' => $source->logo_url ?? null,
                                        'logo_status' => $source->logo_status ?? 'unknown',
                                        'logo_checked_at' => $source->logo_checked_at ?? null,
                                        'size' => 22,
                                        'color' => $bucket->color,
                                    ]) !!}
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grimba-breakdown__panel" data-panel="owner">
            <div class="grimba-breakdown__owner-grid">
                <div class="grimba-breakdown__donut" aria-label="{{ __('Répartition par type de propriété') }}">
                    <div class="grimba-breakdown__donut-center">
                        <strong>{{ $topOwnerPct }}%</strong>
                        <span>{{ $topOwner?->label ?? __('Non classé') }}</span>
                    </div>
                </div>

                <div class="grimba-breakdown__rows">
                    @foreach($ownershipBuckets as $bucket)
                        @php($pct = (int) round($bucket->count * 100 / $total))
                        <div class="grimba-breakdown__row" style="--dot: {{ $bucket->color }};">
                            <div class="grimba-breakdown__legend">
                                <span class="grimba-breakdown__dot" style="--dot: {{ $bucket->color }};"></span>
                                <span>{{ $bucket->label }}</span>
                            </div>
                            <div class="grimba-breakdown__metric">
                                <div class="grimba-breakdown__mini-track">
                                    <span class="grimba-breakdown__mini-fill" style="--dot: {{ $bucket->color }}; --w: {{ $pct }}%; --delay: {{ $loop->index * 70 }}ms;"></span>
                                </div>
                                <strong>{{ $pct }}%</strong>
                                <div class="grimba-breakdown__logos">
                                    @foreach($bucket->items->take(6) as $source)
                                        {!! Theme::partial('source-logo', [
                                            'source_id' => $source->key,
                                            'name' => $source->name,
                                            'website' => $source->website,
                                            'logo_url' => $source->logo_url ?? null,
                                            'logo_status' => $source->logo_status ?? 'unknown',
                                            'logo_checked_at' => $source->logo_checked_at ?? null,
                                            'size' => 22,
                                            'color' => $bucket->color,
                                        ]) !!}
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
