@php
    /**
     * GrimbaNews story breakdown.
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
    $knownBiasTotal = $breakdown['knownBiasTotal'];
    $knownBiasPct = $breakdown['knownBiasPct'];
    $weakestBias = $breakdown['weakestBias'];
    $weakestPct = $breakdown['weakestPct'];
    $dominantBias = $breakdown['dominantBias'];
    $dominantBiasPct = $breakdown['dominantBiasPct'];
    $biasBalanceScore = $breakdown['biasBalanceScore'];
    $factBuckets = $breakdown['factBuckets'];
    $ownershipBuckets = $breakdown['ownershipBuckets'];
    $donutGradient = $breakdown['donutGradient'];
    $topOwner = $breakdown['topOwner'];
    $topOwnerPct = $breakdown['topOwnerPct'];
    $originBuckets = $breakdown['originBuckets'];
    $countryBuckets = $breakdown['countryBuckets'];
    $originBiasBuckets = $breakdown['originBiasBuckets'];
    $countryBiasBuckets = $breakdown['countryBiasBuckets'];
    $topOrigin = $breakdown['topOrigin'];
    $topOriginPct = $breakdown['topOriginPct'];
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
            width: 100%;
            max-width: 1120px;
            margin-inline: auto;
            padding: 14px 16px !important;
            box-sizing: border-box;
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
                linear-gradient(90deg, rgba(59, 130, 246, .10), transparent 26%, transparent 74%, rgba(209, 40, 84, .08)),
                repeating-linear-gradient(90deg, rgba(23, 23, 23, .035) 0 1px, transparent 1px 74px),
                repeating-linear-gradient(180deg, rgba(23, 23, 23, .026) 0 1px, transparent 1px 54px),
                linear-gradient(135deg, rgba(255, 255, 255, .18), transparent 42%);
            opacity: .88;
        }

        [data-bs-theme="dark"] #{{ $uid }}::before {
            background:
                linear-gradient(90deg, rgba(70, 126, 255, .16), transparent 26%, transparent 74%, rgba(239, 68, 68, .12)),
                repeating-linear-gradient(90deg, rgba(248, 243, 234, .045) 0 1px, transparent 1px 74px),
                repeating-linear-gradient(180deg, rgba(248, 243, 234, .032) 0 1px, transparent 1px 54px),
                linear-gradient(135deg, rgba(255, 255, 255, .05), transparent 46%);
            opacity: .78;
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
            font: 700 22px/1.05 "Fraunces", Georgia, serif;
            letter-spacing: 0;
        }

        #{{ $uid }} .grimba-breakdown__tabs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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
            width: calc((100% - 8px) / 4);
            border-radius: 9px;
            background: var(--gbd-tab);
            box-shadow: 0 8px 18px rgba(0, 0, 0, .18);
            transform: translateX(var(--tab-x, 0));
            transition: transform .24s cubic-bezier(.2,.8,.2,1);
        }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs { --tab-x: 0; }
        #{{ $uid }} #{{ $uid }}-origin:checked ~ .grimba-breakdown__tabs { --tab-x: 100%; }
        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs { --tab-x: 200%; }
        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs { --tab-x: 300%; }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #111827, #2f6fe9);
        }

        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #111827, #18a058);
        }

        #{{ $uid }} #{{ $uid }}-origin:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #111827, #7c3aed);
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

        [data-bs-theme="dark"] #{{ $uid }} #{{ $uid }}-origin:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #f8f3ea, #a78bfa);
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
        #{{ $uid }} #{{ $uid }}-origin:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-origin"],
        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-fact"],
        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-owner"] {
            color: #fff;
            text-shadow: 0 1px 10px rgba(0, 0, 0, .42);
        }

        [data-bs-theme="dark"] #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-bias"],
        [data-bs-theme="dark"] #{{ $uid }} #{{ $uid }}-origin:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-origin"],
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
        #{{ $uid }} #{{ $uid }}-origin:checked ~ .grimba-breakdown__panels [data-panel="origin"],
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
            font-size: 15px;
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

        #{{ $uid }} .grimba-breakdown__bias-intelligence {
            display: grid;
            grid-template-columns: minmax(0, 1.18fr) minmax(210px, .82fr);
            gap: 10px;
            margin-bottom: 10px;
        }

        #{{ $uid }} .grimba-breakdown__bias-console,
        #{{ $uid }} .grimba-breakdown__bias-stat {
            min-width: 0;
            border: 1px solid var(--gbd-line);
            border-radius: 14px;
            background:
                linear-gradient(135deg, rgba(59, 130, 246, .08), transparent 38%),
                linear-gradient(180deg, var(--gbd-card), var(--gbd-surface));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .10), var(--gbd-shadow);
        }

        #{{ $uid }} .grimba-breakdown__bias-console {
            padding: 12px;
        }

        #{{ $uid }} .grimba-breakdown__bias-console-head,
        #{{ $uid }} .grimba-breakdown__bias-stat span,
        #{{ $uid }} .grimba-breakdown__owner-summary span {
            color: var(--gbd-muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        #{{ $uid }} .grimba-breakdown__bias-console-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }

        #{{ $uid }} .grimba-breakdown__bias-console-head strong {
            color: var(--gbd-ink);
            font: 800 22px/1 "Fraunces", Georgia, serif;
        }

        #{{ $uid }} .grimba-breakdown__bias-spectrum {
            display: flex;
            min-height: 18px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--gbd-track);
            box-shadow: inset 0 0 0 1px var(--gbd-line), 0 14px 28px rgba(0, 0, 0, .08);
        }

        #{{ $uid }} .grimba-breakdown__bias-spectrum span {
            width: var(--w);
            min-width: 4px;
            background:
                linear-gradient(90deg, color-mix(in srgb, var(--dot) 72%, #fff), var(--dot));
            transform-origin: left;
            animation: gbd-fill .72s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--delay, 0ms);
        }

        #{{ $uid }} .grimba-breakdown__bias-spectrum-labels {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 8px;
            color: var(--gbd-muted);
            font-size: 11px;
            font-weight: 800;
        }

        #{{ $uid }} .grimba-breakdown__bias-spectrum-labels span:nth-child(2) {
            text-align: center;
        }

        #{{ $uid }} .grimba-breakdown__bias-spectrum-labels span:nth-child(3) {
            text-align: right;
        }

        #{{ $uid }} .grimba-breakdown__bias-stats {
            display: grid;
            gap: 8px;
        }

        #{{ $uid }} .grimba-breakdown__bias-stat {
            padding: 10px 12px;
        }

        #{{ $uid }} .grimba-breakdown__bias-stat strong {
            display: block;
            margin-top: 4px;
            color: var(--gbd-ink);
            font: 800 25px/1.02 "Fraunces", Georgia, serif;
            overflow-wrap: anywhere;
        }

        #{{ $uid }} .grimba-breakdown__bias-stat em {
            display: block;
            margin-top: 3px;
            color: var(--gbd-muted);
            font-size: 12px;
            font-style: normal;
            font-weight: 700;
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
            letter-spacing: 0;
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

        #{{ $uid }} .grimba-breakdown__lane-foot {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            min-height: 20px;
        }

        #{{ $uid }} .grimba-breakdown__origin-pill {
            display: inline-flex;
            max-width: 100%;
            align-items: center;
            gap: 4px;
            border: 1px solid var(--gbd-line);
            border-radius: 999px;
            padding: 3px 7px;
            background: color-mix(in srgb, var(--lane-color) 9%, var(--gbd-paper));
            color: var(--gbd-muted);
            font-size: 10px;
            font-weight: 800;
            line-height: 1.1;
            overflow-wrap: anywhere;
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
            --gbd-donut-size: clamp(124px, 22vw, 152px);
            width: min(100%, var(--gbd-donut-size));
            max-width: 100%;
            aspect-ratio: 1;
            margin: 0 auto;
            border-radius: 50%;
            background: conic-gradient({{ $donutGradient }});
            position: relative;
            transform: rotate(-90deg);
            animation: gbd-donut .72s cubic-bezier(.2,.8,.2,1) both;
            box-shadow:
                inset 0 0 0 10px rgba(255, 255, 255, .9),
                0 0 0 1px var(--gbd-line),
                0 14px 36px rgba(0, 0, 0, .12);
        }

        [data-bs-theme="dark"] #{{ $uid }} .grimba-breakdown__donut {
            box-shadow: inset 0 0 0 10px rgba(15, 14, 11, .9), 0 14px 30px rgba(0, 0, 0, .42);
        }

        #{{ $uid }} .grimba-breakdown__donut::after {
            content: "";
            position: absolute;
            inset: 28%;
            border-radius: 50%;
            background: var(--gbd-paper);
            box-shadow: 0 0 0 1px var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__donut-center {
            position: absolute;
            inset: 28%;
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
            color: var(--gbd-ink);
            font-size: clamp(18px, calc(var(--gbd-donut-size) * .16), 24px);
            line-height: 1;
        }

        #{{ $uid }} .grimba-breakdown__donut-center span {
            display: block;
            margin-top: 4px;
            max-width: 72px;
            color: var(--gbd-muted);
            font-size: 9px;
            font-weight: 900;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        #{{ $uid }} .grimba-breakdown__owner-grid {
            display: grid;
            grid-template-columns: minmax(190px, 240px) minmax(0, 1fr);
            gap: 14px;
            align-items: stretch;
            min-width: 0;
        }

        #{{ $uid }} .grimba-breakdown__owner-summary-card {
            min-width: 0;
            display: grid;
            place-items: center;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--gbd-line);
            border-radius: 16px;
            background:
                linear-gradient(135deg, rgba(160, 106, 0, .10), transparent 42%),
                linear-gradient(180deg, var(--gbd-card), var(--gbd-surface));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .10), var(--gbd-shadow);
        }

        #{{ $uid }} .grimba-breakdown__owner-summary {
            min-width: 0;
            width: 100%;
            text-align: center;
        }

        #{{ $uid }} .grimba-breakdown__owner-summary strong {
            display: block;
            margin-top: 4px;
            color: var(--gbd-ink);
            font: 800 26px/1.05 "Fraunces", Georgia, serif;
            overflow-wrap: anywhere;
        }

        #{{ $uid }} .grimba-breakdown__owner-summary em {
            display: block;
            margin-top: 4px;
            color: var(--gbd-muted);
            font-size: 12px;
            font-style: normal;
            font-weight: 750;
        }

        #{{ $uid }} .grimba-breakdown__owner-rows {
            display: grid;
            gap: 8px;
            min-width: 0;
        }

        #{{ $uid }} .grimba-breakdown__owner-row {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(96px, .52fr) minmax(48px, auto) minmax(72px, 136px);
            gap: 10px;
            align-items: center;
            min-width: 0;
            overflow: hidden;
            padding: 9px 10px;
            border: 1px solid var(--gbd-line);
            border-radius: 14px;
            background: color-mix(in srgb, var(--dot) 7%, var(--gbd-paper));
        }

        #{{ $uid }} .grimba-breakdown__owner-label {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        #{{ $uid }} .grimba-breakdown__owner-label-text {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        #{{ $uid }} .grimba-breakdown__owner-label-text strong {
            color: var(--gbd-ink);
            font-weight: 850;
            line-height: 1.1;
            overflow-wrap: anywhere;
        }

        #{{ $uid }} .grimba-breakdown__owner-label-text small {
            color: var(--gbd-muted);
            font-size: 11px;
            font-weight: 750;
        }

        #{{ $uid }} .grimba-breakdown__owner-track {
            min-width: 0;
            width: 100%;
            height: 10px;
            border-radius: 999px;
            background: var(--gbd-track);
            overflow: hidden;
            box-shadow: inset 0 0 0 1px var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__owner-fill {
            display: block;
            width: var(--w);
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, color-mix(in srgb, var(--dot) 64%, #fff), var(--dot));
            transform-origin: left;
            animation: gbd-fill .72s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--delay, 0ms);
        }

        #{{ $uid }} .grimba-breakdown__owner-percent {
            min-width: 38px;
            color: var(--gbd-ink);
            font: 850 17px/1 "Fraunces", Georgia, serif;
            text-align: right;
        }

        #{{ $uid }} .grimba-breakdown__owner-logos {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 5px;
            max-width: min(100%, 136px);
            min-width: 0;
            overflow: hidden;
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

        #{{ $uid }} .grimba-breakdown__origin-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.04fr) minmax(240px, .96fr);
            gap: 12px;
            align-items: stretch;
        }

        #{{ $uid }} .grimba-breakdown__origin-card {
            border: 1px solid var(--gbd-line);
            border-radius: 18px;
            padding: 12px;
            background:
                linear-gradient(135deg, rgba(124, 58, 237, .08), transparent 40%),
                linear-gradient(180deg, var(--gbd-card), var(--gbd-surface));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .10);
        }

        #{{ $uid }} .grimba-breakdown__origin-card h3 {
            margin: 0 0 10px;
            font: 800 12px/1.1 "Public Sans", system-ui, sans-serif;
            text-transform: uppercase;
            letter-spacing: 0;
            color: var(--gbd-muted);
        }

        #{{ $uid }} .grimba-breakdown__origin-bar {
            display: flex;
            height: 18px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--gbd-track);
            box-shadow: inset 0 0 0 1px var(--gbd-line), 0 12px 24px rgba(0, 0, 0, .08);
            margin-bottom: 10px;
        }

        #{{ $uid }} .grimba-breakdown__origin-bar span {
            display: block;
            width: var(--w);
            min-width: 4px;
            background: linear-gradient(90deg, color-mix(in srgb, var(--dot) 65%, #fff), var(--dot));
            transform-origin: left;
            animation: gbd-fill .72s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--delay, 0ms);
        }

        #{{ $uid }} .grimba-breakdown__origin-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__origin-row:last-child {
            border-bottom: 0;
        }

        #{{ $uid }} .grimba-breakdown__origin-meta {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-weight: 800;
        }

        #{{ $uid }} .grimba-breakdown__origin-meta span:last-child {
            color: var(--gbd-muted);
            font-size: 11px;
            font-weight: 700;
        }

        #{{ $uid }} .grimba-breakdown__origin-score {
            display: inline-flex;
            min-width: 46px;
            justify-content: flex-end;
            color: var(--gbd-ink);
            font: 800 18px/1 "Fraunces", Georgia, serif;
        }

        #{{ $uid }} .grimba-breakdown__origin-matrix {
            display: grid;
            gap: 8px;
        }

        #{{ $uid }} .grimba-breakdown__origin-card-title-spaced {
            margin-top: 16px !important;
            padding-top: 14px;
            border-top: 1px solid var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__origin-bias {
            display: grid;
            gap: 6px;
            padding: 10px;
            border: 1px solid var(--gbd-line);
            border-radius: 14px;
            background: color-mix(in srgb, var(--dot) 8%, var(--gbd-paper));
        }

        #{{ $uid }} .grimba-breakdown__country-bias-grid {
            display: grid;
            gap: 8px;
        }

        #{{ $uid }} .grimba-breakdown__country-bias-card {
            min-width: 0;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(96px, .62fr);
            gap: 10px;
            align-items: center;
            padding: 10px;
            border: 1px solid var(--gbd-line);
            border-radius: 14px;
            background:
                radial-gradient(circle at 12% 12%, color-mix(in srgb, var(--dot) 15%, transparent), transparent 34%),
                color-mix(in srgb, var(--dot) 7%, var(--gbd-paper));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .08);
        }

        #{{ $uid }} .grimba-breakdown__country-bias-copy {
            min-width: 0;
            display: grid;
            gap: 3px;
        }

        #{{ $uid }} .grimba-breakdown__country-bias-copy strong {
            color: var(--gbd-ink);
            font-weight: 850;
            line-height: 1.15;
            overflow-wrap: anywhere;
        }

        #{{ $uid }} .grimba-breakdown__country-bias-copy span,
        #{{ $uid }} .grimba-breakdown__country-bias-copy em {
            color: var(--gbd-muted);
            font-size: 11px;
            font-style: normal;
            font-weight: 750;
            line-height: 1.2;
        }

        #{{ $uid }} .grimba-breakdown__country-bias-viz {
            min-width: 0;
            display: grid;
            gap: 6px;
        }

        #{{ $uid }} .grimba-breakdown__country-bias-sources {
            display: flex;
            justify-content: flex-end;
            gap: 4px;
            min-width: 0;
            overflow: hidden;
        }

        #{{ $uid }} .grimba-breakdown__origin-bias-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: var(--gbd-muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        #{{ $uid }} .grimba-breakdown__origin-bias-head strong {
            color: var(--gbd-ink);
            text-transform: none;
            letter-spacing: 0;
        }

        #{{ $uid }} .grimba-breakdown__origin-triptych {
            display: flex;
            height: 12px;
            overflow: hidden;
            border-radius: 999px;
            background: var(--gbd-track);
        }

        #{{ $uid }} .grimba-breakdown__origin-triptych span {
            width: var(--w);
            min-width: 3px;
            background: var(--dot);
        }

        #{{ $uid }} .grimba-breakdown__country-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }

        #{{ $uid }} .grimba-breakdown__country-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            max-width: 100%;
            min-height: 30px;
            border: 1px solid var(--gbd-line);
            border-radius: 999px;
            padding: 5px 10px;
            background: color-mix(in srgb, var(--dot) 9%, var(--gbd-paper));
            color: var(--gbd-ink);
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        #{{ $uid }} .grimba-breakdown__country-chip em {
            color: var(--gbd-muted);
            font-style: normal;
            font-weight: 700;
        }

        @media (max-width: 900px) {
            #{{ $uid }} .grimba-breakdown__owner-grid {
                grid-template-columns: 1fr;
            }

            #{{ $uid }} .grimba-breakdown__owner-summary-card {
                grid-template-columns: minmax(124px, 152px) minmax(0, 1fr);
                column-gap: 12px;
                place-items: center stretch;
                text-align: left;
            }

            #{{ $uid }} .grimba-breakdown__owner-summary {
                text-align: left;
                overflow: hidden;
            }

            #{{ $uid }} .grimba-breakdown__owner-row {
                grid-template-columns: minmax(0, 1fr) minmax(42px, auto);
            }

            #{{ $uid }} .grimba-breakdown__owner-track,
            #{{ $uid }} .grimba-breakdown__owner-logos {
                grid-column: 1 / -1;
            }

            #{{ $uid }} .grimba-breakdown__owner-logos {
                justify-content: flex-start;
                max-width: 100%;
            }

            #{{ $uid }} .grimba-breakdown__origin-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            #{{ $uid }} {
                padding: 12px !important;
            }

            #{{ $uid }} .grimba-breakdown__top {
                align-items: flex-start;
                flex-direction: column;
            }

            #{{ $uid }} .grimba-breakdown__title {
                font-size: 20px;
            }

            #{{ $uid }} .grimba-breakdown__callout {
                font-size: 13px;
            }

            #{{ $uid }} .grimba-breakdown__bias-intelligence {
                grid-template-columns: 1fr;
            }

            #{{ $uid }} .grimba-breakdown__bias-lanes {
                grid-template-columns: repeat(2, 1fr);
            }

            #{{ $uid }} .grimba-breakdown__lane {
                min-height: 0;
            }

            #{{ $uid }} .grimba-breakdown__donut {
                --gbd-donut-size: 116px;
                max-width: 100%;
            }

            #{{ $uid }} .grimba-breakdown__donut-center strong {
                font-size: 18px;
            }

            #{{ $uid }} .grimba-breakdown__row,
            #{{ $uid }} .grimba-breakdown__metric {
                grid-template-columns: 1fr;
            }

            #{{ $uid }} .grimba-breakdown__owner-summary-card {
                grid-template-columns: minmax(104px, 116px) minmax(0, 1fr);
                column-gap: 10px;
            }

            #{{ $uid }} .grimba-breakdown__owner-summary strong {
                font-size: 20px;
                line-height: 1.08;
            }

            #{{ $uid }} .grimba-breakdown__logos {
                justify-content: flex-start;
            }

            #{{ $uid }} .grimba-breakdown__insight-grid {
                grid-template-columns: 1fr;
            }

            #{{ $uid }} .grimba-breakdown__tabs {
                width: 100%;
            }

            #{{ $uid }} .grimba-breakdown__tab {
                padding-inline: 6px;
                font-size: 11px;
            }

            #{{ $uid }} .grimba-breakdown__country-chip {
                white-space: normal;
            }

            #{{ $uid }} .grimba-breakdown__country-bias-card {
                grid-template-columns: 1fr;
            }

            #{{ $uid }} .grimba-breakdown__country-bias-sources {
                justify-content: flex-start;
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
    <input type="radio" id="{{ $uid }}-origin" name="{{ $uid }}-tab">
    <input type="radio" id="{{ $uid }}-fact" name="{{ $uid }}-tab">
    <input type="radio" id="{{ $uid }}-owner" name="{{ $uid }}-tab">

    <div class="grimba-breakdown__tabs" role="tablist" aria-label="{{ __('Analyse du dossier') }}">
        <label class="grimba-breakdown__tab" for="{{ $uid }}-bias" role="tab">{{ __('Biais') }}</label>
        <label class="grimba-breakdown__tab" for="{{ $uid }}-origin" role="tab">{{ __('Origines') }}</label>
        <label class="grimba-breakdown__tab" for="{{ $uid }}-fact" role="tab">{{ __('Factualité') }}</label>
        <label class="grimba-breakdown__tab" for="{{ $uid }}-owner" role="tab">{{ __('Propriété') }}</label>
    </div>

    <div class="grimba-breakdown__panels">
        <div class="grimba-breakdown__panel" data-panel="bias">
            @php
                $biasRailBuckets = $knownBiasBuckets->filter(fn ($bucket) => $bucket->count > 0);
                $knownBiasDenominator = max(1, $knownBiasTotal);
            @endphp

            <div class="grimba-breakdown__callout">
                <span class="grimba-breakdown__icon">◎</span>
                <span>
                    {{ __('Cette histoire n’a que') }}
                    <strong>{{ $weakestPct }}% {{ $weakestBias?->label }}</strong>
                    {{ __('de couverture politique.') }}
                </span>
            </div>

            <div class="grimba-breakdown__bias-intelligence">
                <article class="grimba-breakdown__bias-console" aria-label="{{ __('Cartographie des biais') }}">
                    <div class="grimba-breakdown__bias-console-head">
                        <span>{{ __('Signal de couverture') }}</span>
                        <strong>{{ $biasBalanceScore }}/100</strong>
                    </div>
                    <div class="grimba-breakdown__bias-spectrum">
                        @forelse($biasRailBuckets as $bucket)
                            @php
                                $pct = (int) round($bucket->count * 100 / $knownBiasDenominator);
                            @endphp
                            <span title="{{ $bucket->label }} · {{ $pct }}%" style="--dot: {{ $bucket->color }}; --w: {{ $pct }}%; --delay: {{ $loop->index * 70 }}ms;"></span>
                        @empty
                            <span title="{{ __('Non classé') }}" style="--dot: #6b7280; --w: 100%;"></span>
                        @endforelse
                    </div>
                    <div class="grimba-breakdown__bias-spectrum-labels">
                        @foreach(['left', 'center', 'right'] as $biasKey)
                            @php
                                $bucket = $knownBiasBuckets->firstWhere('key', $biasKey);
                                $pct = $bucket ? (int) round($bucket->count * 100 / $knownBiasDenominator) : 0;
                            @endphp
                            <span>{{ $bucket?->label ?? __('Non classé') }} {{ $pct }}%</span>
                        @endforeach
                    </div>
                </article>

                <div class="grimba-breakdown__bias-stats">
                    <article class="grimba-breakdown__bias-stat">
                        <span>{{ __('Camp dominant') }}</span>
                        <strong>{{ $dominantBias?->label ?? __('Non classé') }}</strong>
                        <em>{{ $dominantBiasPct }}% {{ __('des sources classées') }}</em>
                    </article>
                    <article class="grimba-breakdown__bias-stat">
                        <span>{{ __('Couverture connue') }}</span>
                        <strong>{{ $knownBiasPct }}%</strong>
                        <em>{{ trans_choice(':count source classée|:count sources classées', $knownBiasTotal, ['count' => $knownBiasTotal]) }}</em>
                    </article>
                </div>
            </div>

            <div class="grimba-breakdown__bias-lanes">
                @foreach($biasBuckets as $bucket)
                    @php
                        $lanePct = (int) round($bucket->count * 100 / $total);
                        $laneOrigins = $bucket->items
                            ->pluck('country_label')
                            ->filter()
                            ->unique()
                            ->take(3);
                    @endphp
                    <div class="grimba-breakdown__lane" style="--lane-color: {{ $bucket->color }};">
                        <div class="grimba-breakdown__lane-head">
                            <span>{{ $bucket->label }}</span>
                            <strong>{{ $lanePct }}%</strong>
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
                        <div class="grimba-breakdown__lane-foot">
                            @forelse($laneOrigins as $originLabel)
                                <span class="grimba-breakdown__origin-pill">{{ $originLabel }}</span>
                            @empty
                                <span class="small opacity-65">{{ __('Origine inconnue') }}</span>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grimba-breakdown__bias-bar" aria-label="{{ __('Distribution des biais') }}">
                @forelse($biasBuckets->filter(fn ($bucket) => $bucket->count > 0) as $bucket)
                    @php
                        $pct = (int) round($bucket->count * 100 / $total);
                    @endphp
                    <span title="{{ $bucket->label }} · {{ $pct }}%" style="--w: {{ $pct }}%; --delay: {{ 60 + ($loop->index * 80) }}ms; background: {{ $bucket->color }};">{{ mb_substr($bucket->label, 0, 1) }} {{ $pct }}%</span>
                @empty
                    <span style="--w: 100%; background:#6b7280;">{{ __('N/A') }}</span>
                @endforelse
            </div>
        </div>

        <div class="grimba-breakdown__panel" data-panel="origin">
            <div class="grimba-breakdown__callout">
                <span class="grimba-breakdown__icon">◇</span>
                <span>
                    <strong>{{ $topOriginPct }}% {{ $topOrigin?->label ?? __('Non renseigné') }}</strong>
                    {{ __('des sources proviennent du même bassin éditorial.') }}
                </span>
            </div>

            <div class="grimba-breakdown__origin-grid">
                <div class="grimba-breakdown__origin-card">
                    <h3>{{ __('Pays d’origine des sources') }}</h3>
                    <div class="grimba-breakdown__origin-bar" aria-label="{{ __('Répartition géographique des sources') }}">
                        @foreach($originBuckets->filter(fn ($bucket) => $bucket->count > 0) as $bucket)
                            @php
                                $pct = (int) round($bucket->count * 100 / $total);
                            @endphp
                            <span title="{{ $bucket->label }} · {{ $pct }}%" style="--dot: {{ $bucket->color }}; --w: {{ max(1, $pct) }}%; --delay: {{ $loop->index * 70 }}ms;"></span>
                        @endforeach
                    </div>

                    @foreach($originBuckets->filter(fn ($bucket) => $bucket->count > 0) as $bucket)
                        @php
                            $pct = (int) round($bucket->count * 100 / $total);
                        @endphp
                        <div class="grimba-breakdown__origin-row" style="--dot: {{ $bucket->color }};">
                            <div class="grimba-breakdown__legend">
                                <span class="grimba-breakdown__dot" style="--dot: {{ $bucket->color }};"></span>
                                <span class="grimba-breakdown__origin-meta">
                                    <span>{{ $bucket->label }}</span>
                                    <span>{{ trans_choice(':count source|:count sources', $bucket->count, ['count' => $bucket->count]) }}</span>
                                </span>
                            </div>
                            <span class="grimba-breakdown__origin-score">{{ $pct }}%</span>
                        </div>
                    @endforeach

                    <div class="grimba-breakdown__country-cloud" aria-label="{{ __('Pays représentés') }}">
                        @foreach($countryBuckets->take(8) as $country)
                            <span class="grimba-breakdown__country-chip" style="--dot: {{ $country->color }};">
                                {{ $country->label }}
                                <em>{{ $country->count }}</em>
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="grimba-breakdown__origin-card">
                    <h3>{{ __('Biais par origine') }}</h3>
                    <div class="grimba-breakdown__origin-matrix">
                        @foreach($originBiasBuckets as $bucket)
                            <article class="grimba-breakdown__origin-bias" style="--dot: {{ $bucket->color }};">
                                <div class="grimba-breakdown__origin-bias-head">
                                    <strong>{{ $bucket->label }}</strong>
                                    <span>{{ trans_choice(':count source|:count sources', $bucket->count, ['count' => $bucket->count]) }}</span>
                                </div>
                                <div class="grimba-breakdown__origin-triptych" aria-label="{{ __('Répartition des biais pour :origin', ['origin' => $bucket->label]) }}">
                                    @foreach(['left', 'center', 'right'] as $biasKey)
                                        @php
                                            $bias = $bucket->bias[$biasKey];
                                        @endphp
                                        <span title="{{ $bias->label }} · {{ $bias->pct }}%" style="--dot: {{ $bias->color }}; --w: {{ max(1, $bias->pct) }}%;"></span>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-between small" style="color: var(--gbd-muted); font-weight:800;">
                                    @foreach(['left', 'center', 'right'] as $biasKey)
                                        @php
                                            $bias = $bucket->bias[$biasKey];
                                        @endphp
                                        <span>{{ mb_substr($bias->label, 0, 1) }} {{ $bias->pct }}%</span>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if($countryBiasBuckets->isNotEmpty())
                        <h3 class="grimba-breakdown__origin-card-title-spaced">{{ __('Biais par pays source') }}</h3>
                        <div class="grimba-breakdown__country-bias-grid">
                            @foreach($countryBiasBuckets->take(6) as $bucket)
                                <article class="grimba-breakdown__country-bias-card" style="--dot: {{ $bucket->color }};">
                                    <div class="grimba-breakdown__country-bias-copy">
                                        <strong>{{ $bucket->label }}</strong>
                                        <span>{{ $bucket->origin_label }} · {{ trans_choice(':count source|:count sources', $bucket->count, ['count' => $bucket->count]) }}</span>
                                        <em>{{ __('Dominant') }}: {{ $bucket->dominant_bias }} {{ $bucket->dominant_pct }}%</em>
                                    </div>
                                    <div class="grimba-breakdown__country-bias-viz">
                                        <div class="grimba-breakdown__origin-triptych" aria-label="{{ __('Répartition des biais pour :origin', ['origin' => $bucket->label]) }}">
                                            @foreach(['left', 'center', 'right'] as $biasKey)
                                                @php
                                                    $bias = $bucket->bias[$biasKey];
                                                @endphp
                                                <span title="{{ $bias->label }} · {{ $bias->pct }}%" style="--dot: {{ $bias->color }}; --w: {{ max(1, $bias->pct) }}%;"></span>
                                            @endforeach
                                        </div>
                                        <div class="grimba-breakdown__country-bias-sources" aria-label="{{ __('Sources') }}">
                                            @foreach($bucket->items->take(3) as $source)
                                                {!! Theme::partial('source-logo', [
                                                    'source_id' => $source->key,
                                                    'name' => $source->name,
                                                    'website' => $source->website,
                                                    'logo_url' => $source->logo_url ?? null,
                                                    'logo_status' => $source->logo_status ?? 'unknown',
                                                    'logo_checked_at' => $source->logo_checked_at ?? null,
                                                    'size' => 20,
                                                    'color' => $bucket->color,
                                                ]) !!}
                                            @endforeach
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grimba-breakdown__panel" data-panel="fact">
            <div class="grimba-breakdown__callout">
                <span class="grimba-breakdown__icon">✓</span>
                <span>{{ __('Factualité estimée depuis le score de crédibilité de chaque source.') }}</span>
            </div>

            <div class="grimba-breakdown__rows">
                @foreach($factBuckets as $bucket)
                    @php
                        $pct = (int) round($bucket->items->count() * 100 / $total);
                    @endphp
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
                <div class="grimba-breakdown__owner-summary-card">
                    <div class="grimba-breakdown__donut" aria-label="{{ __('Répartition par type de propriété') }}">
                        <div class="grimba-breakdown__donut-center">
                            <strong>{{ $topOwnerPct }}%</strong>
                            <span>{{ __('Dominant') }}</span>
                        </div>
                    </div>
                    <div class="grimba-breakdown__owner-summary">
                        <span>{{ __('Type dominant') }}</span>
                        <strong>{{ $topOwner?->label ?? __('Non classé') }}</strong>
                        <em>
                            {{ __(':count sur :total sources', ['count' => $topOwner?->count ?? 0, 'total' => $total]) }}
                        </em>
                    </div>
                </div>

                <div class="grimba-breakdown__owner-rows">
                    @foreach($ownershipBuckets as $bucket)
                        @php
                            $pct = (int) round($bucket->count * 100 / $total);
                        @endphp
                        <article class="grimba-breakdown__owner-row" style="--dot: {{ $bucket->color }};">
                            <div class="grimba-breakdown__owner-label">
                                <span class="grimba-breakdown__dot" style="--dot: {{ $bucket->color }};"></span>
                                <span class="grimba-breakdown__owner-label-text">
                                    <strong>{{ $bucket->label }}</strong>
                                    <small>{{ trans_choice(':count source|:count sources', $bucket->count, ['count' => $bucket->count]) }}</small>
                                </span>
                            </div>
                            <div class="grimba-breakdown__owner-track">
                                <span class="grimba-breakdown__owner-fill" style="--dot: {{ $bucket->color }}; --w: {{ $pct }}%; --delay: {{ $loop->index * 70 }}ms;"></span>
                            </div>
                            <strong class="grimba-breakdown__owner-percent">{{ $pct }}%</strong>
                            <div class="grimba-breakdown__owner-logos">
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
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
