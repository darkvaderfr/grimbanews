@php
    Theme::layout('grimba-chrome');
    /**
     * @var array<string, \Illuminate\Support\Collection> $buckets keyed by continent
     * @var int $windowHours
     */
    use App\Support\Continents;
    use App\Support\GrimbaClusterBias;

    // S-MAP-v2 (Vader 2026-05-29) — continent label anchors tuned to
    // the real Equirectangular projection (viewBox 0 0 1000 500).
    // Coordinates are (lon+180)*2.78, (90-lat)*2.78 — so e.g. London
    // (lat 51, lon 0) is (500, 108).
    $continentAnchors = [
        Continents::EUROPE   => ['x' => 510, 'y' => 122, 'svg_dot' => [515, 120]],
        Continents::AMERICAS => ['x' => 235, 'y' => 235, 'svg_dot' => [245, 230]],
        Continents::ASIA     => ['x' => 730, 'y' => 175, 'svg_dot' => [720, 175]],
        Continents::AFRICA   => ['x' => 525, 'y' => 290, 'svg_dot' => [525, 290]],
        Continents::OCEANIA  => ['x' => 825, 'y' => 360, 'svg_dot' => [830, 365]],
        Continents::GLOBAL   => ['x' => 500, 'y' => 470, 'svg_dot' => [500, 460]],
    ];

    $biasMeta = GrimbaClusterBias::biasMetaForBlade();
    $biasDotColor = function (?string $rating) use ($biasMeta): string {
        $k = $rating && isset($biasMeta[$rating]) ? $rating : 'unknown';
        return $biasMeta[$k]['color'] ?? '#6b6459';
    };

    // S-MAP-v3 — per-continent bias distribution (L/C/R/MG/unknown counts).
    // Powers the inline mini-donut that appears in each ticker header.
    $biasDistribution = function ($posts): array {
        $counts = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
        foreach ($posts as $p) {
            $r = $p->bias_rating ?? 'unknown';
            if (! isset($counts[$r])) { $r = 'unknown'; }
            $counts[$r]++;
        }
        $total = array_sum($counts);
        if ($total === 0) { return ['total' => 0, 'segments' => []]; }
        $segments = [];
        $cumulativeDeg = 0;
        foreach (['left', 'center', 'right', 'unknown'] as $k) {
            if ($counts[$k] === 0) { continue; }
            $deg = ($counts[$k] / $total) * 360;
            $segments[] = ['key' => $k, 'count' => $counts[$k], 'start' => $cumulativeDeg, 'deg' => $deg];
            $cumulativeDeg += $deg;
        }
        return ['total' => $total, 'segments' => $segments, 'counts' => $counts];
    };
@endphp

<style>
    /* ── S-MAP-v2 cinematic / HUD chrome ──────────────────────── */
    .gmap-shell {
        position: relative;
        width: 100%;
        min-height: 100dvh;
        margin: 0;
        padding: 0;
        background: #04050a;
        color: #e8f4ff;
        overflow: hidden;
        font-family: var(--font-sans, system-ui, -apple-system, sans-serif);
    }
    .gmap-shell::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse at 20% 10%, rgba(168, 85, 247, .10), transparent 45%),
            radial-gradient(ellipse at 80% 80%, rgba(34, 211, 238, .08), transparent 50%),
            radial-gradient(circle at 50% 50%, rgba(255, 255, 255, .02), transparent 60%);
        pointer-events: none;
        z-index: 0;
    }
    .gmap-shell::after {
        /* Subtle scan-line overlay — the "futurist HUD" tell. */
        content: '';
        position: absolute;
        inset: 0;
        background:
            repeating-linear-gradient(
                to bottom,
                transparent 0px,
                transparent 3px,
                rgba(34, 211, 238, .015) 3px,
                rgba(34, 211, 238, .015) 4px
            );
        pointer-events: none;
        z-index: 1;
    }

    .gmap-chrome {
        position: relative;
        z-index: 3;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        border-bottom: 1px solid rgba(34, 211, 238, .15);
        backdrop-filter: blur(4px);
    }
    .gmap-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #22d3ee;
    }
    .gmap-brand__pulse {
        display: inline-block;
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #22d3ee;
        box-shadow: 0 0 12px #22d3ee, 0 0 24px rgba(34, 211, 238, .5);
        animation: gmap-brand-pulse 1.8s ease-in-out infinite;
    }
    @keyframes gmap-brand-pulse {
        0%, 100% { transform: scale(1);   opacity: 1;   }
        50%      { transform: scale(1.4); opacity: .55; }
    }
    .gmap-brand__sep { color: rgba(232, 244, 255, .35); margin: 0 4px; }
    .gmap-brand__title { color: #e8f4ff; letter-spacing: 0.02em; }
    .gmap-controls { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .gmap-control {
        appearance: none;
        background: rgba(34, 211, 238, .08);
        border: 1px solid rgba(34, 211, 238, .35);
        color: #e8f4ff;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 8px 14px;
        border-radius: 4px;
        cursor: pointer;
        transition: background .15s, border-color .15s, box-shadow .15s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .gmap-control:hover, .gmap-control:focus-visible {
        background: rgba(34, 211, 238, .18);
        border-color: #22d3ee;
        box-shadow: 0 0 14px rgba(34, 211, 238, .35);
        outline: none;
    }
    .gmap-control svg { width: 14px; height: 14px; fill: currentColor; }
    .gmap-control[data-active="true"] {
        background: rgba(168, 85, 247, .22);
        border-color: #a855f7;
        color: #f5d4ff;
    }
    .gmap-meta {
        font-size: 11px;
        color: rgba(232, 244, 255, .55);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
    }

    /* ── Stage ──────────────────────────────────────────────── */
    .gmap-stage {
        position: relative;
        z-index: 2;
        width: 100%;
        aspect-ratio: 2 / 1;
        max-height: calc(100dvh - 84px);
        margin: 0 auto;
    }
    .gmap-svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    .gmap-svg .gmap-grid line {
        stroke: rgba(34, 211, 238, .08);
        stroke-width: 0.5;
    }
    .gmap-svg .gmap-graticule path {
        fill: none;
        stroke: rgba(34, 211, 238, .12);
        stroke-width: 0.4;
        stroke-dasharray: 1 3;
    }
    .gmap-svg .gmap-land {
        fill: url(#gmap-land-grad);
        stroke: rgba(34, 211, 238, .55);
        stroke-width: 0.7;
        stroke-linejoin: round;
        filter: drop-shadow(0 0 6px rgba(34, 211, 238, .25));
    }
    .gmap-svg .gmap-pulse {
        fill: #a855f7;
        opacity: 0.85;
        filter: drop-shadow(0 0 8px #a855f7);
        animation: gmap-svg-pulse 2.4s ease-out infinite;
        transform-origin: center;
        transform-box: fill-box;
    }
    @keyframes gmap-svg-pulse {
        0%   { transform: scale(0.6); opacity: .9; }
        70%  { transform: scale(2.2); opacity: .05; }
        100% { transform: scale(2.2); opacity: 0;  }
    }
    .gmap-svg .gmap-pulse-core { fill: #a855f7; filter: drop-shadow(0 0 6px #a855f7); }

    /* ── Continent labels overlaid on the map ─────────────── */
    .gmap-label {
        position: absolute;
        transform: translate(-50%, -50%);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(34, 211, 238, .9);
        text-shadow:
            0 0 8px rgba(34, 211, 238, .5),
            0 0 18px rgba(34, 211, 238, .25);
        pointer-events: none;
        white-space: nowrap;
        z-index: 4;
    }

    /* ── Tickers ──────────────────────────────────────────── */
    .gmap-ticker {
        position: absolute;
        transform: translate(-50%, -50%);
        width: clamp(220px, 26%, 340px);
        background: rgba(4, 6, 12, .82);
        border: 1px solid rgba(168, 85, 247, .25);
        border-radius: 6px;
        padding: 7px 0 9px;
        backdrop-filter: blur(8px);
        box-shadow:
            0 12px 36px rgba(0, 0, 0, .6),
            inset 0 0 0 1px rgba(34, 211, 238, .06);
        overflow: hidden;
        z-index: 5;
    }
    .gmap-ticker__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 11px 6px;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #a855f7;
        border-bottom: 1px solid rgba(168, 85, 247, .18);
        margin-bottom: 6px;
        text-decoration: none;
    }
    .gmap-ticker__head--link:hover {
        color: #e8f4ff;
        background: rgba(168, 85, 247, .12);
    }
    .gmap-ticker__head-count {
        color: rgba(232, 244, 255, .55);
        font-size: 10px;
        font-weight: 800;
    }
    .gmap-ticker__head-meter {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .gmap-donut {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        flex-shrink: 0;
        background: conic-gradient(var(--gmap-donut-gradient, #555 0deg, #555 360deg));
        box-shadow: 0 0 6px rgba(168, 85, 247, .25);
        position: relative;
    }
    .gmap-donut::after {
        content: '';
        position: absolute;
        inset: 4px;
        border-radius: 50%;
        background: rgba(4, 6, 12, .92);
    }
    .gmap-ticker__track {
        display: flex;
        gap: 26px;
        white-space: nowrap;
        will-change: transform;
        padding: 0 12px;
    }
    .gmap-ticker[data-dir="ltr"] .gmap-ticker__track { animation: gmap-scroll-ltr 42s linear infinite; }
    .gmap-ticker[data-dir="rtl"] .gmap-ticker__track { animation: gmap-scroll-rtl 42s linear infinite; }
    .gmap-ticker:hover .gmap-ticker__track,
    .gmap-ticker:focus-within .gmap-ticker__track,
    .gmap-shell[data-paused="true"] .gmap-ticker__track {
        animation-play-state: paused;
    }
    .gmap-ticker__item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #e8f4ff;
        text-decoration: none;
        line-height: 1.3;
    }
    .gmap-ticker__item:hover { text-decoration: underline; }
    .gmap-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        box-shadow: 0 0 6px currentColor;
    }
    .gmap-empty {
        padding: 4px 12px;
        font-size: 12px;
        color: rgba(232, 244, 255, .5);
        font-style: italic;
    }
    @keyframes gmap-scroll-ltr {
        from { transform: translateX(-50%); }
        to   { transform: translateX(0%);   }
    }
    @keyframes gmap-scroll-rtl {
        from { transform: translateX(0%);   }
        to   { transform: translateX(-50%); }
    }
    @media (prefers-reduced-motion: reduce) {
        .gmap-ticker__track,
        .gmap-brand__pulse,
        .gmap-svg .gmap-pulse { animation: none !important; }
        .gmap-ticker__track { flex-wrap: wrap; white-space: normal; gap: 8px 16px; }
    }

    /* ── Mobile ──────────────────────────────────────────────── */
    @media (max-width: 768px) {
        .gmap-shell { min-height: 100dvh; }
        .gmap-stage { aspect-ratio: auto; height: 50dvh; max-height: none; }
        .gmap-svg { opacity: 0.7; }
        .gmap-ticker { display: none; }   /* desktop overlay hidden on mobile */
        .gmap-label { font-size: 9px; }
        .gmap-mobile-list {
            position: relative;
            z-index: 5;
            padding: 12px 14px 32px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    }
    @media (min-width: 769px) {
        .gmap-mobile-list { display: none; }
    }

    /* ── Fullscreen mode (browser API or fallback) ──────────── */
    .gmap-shell:fullscreen,
    .gmap-shell:-webkit-full-screen,
    .gmap-shell[data-fullscreen-fallback="true"] {
        position: fixed; inset: 0;
        width: 100vw; height: 100vh;
        z-index: 9999;
    }
    .gmap-shell:fullscreen .gmap-stage,
    .gmap-shell[data-fullscreen-fallback="true"] .gmap-stage {
        max-height: calc(100vh - 84px);
    }
</style>

<section
    class="gmap-shell"
    data-component="gmap"
    aria-label="{{ __('Carte plein écran des actualités urgentes par continent') }}"
>
    <header class="gmap-chrome">
        <div class="gmap-brand">
            <span class="gmap-brand__pulse" aria-hidden="true"></span>
            <span>LIVE</span>
            <span class="gmap-brand__sep">·</span>
            <span class="gmap-brand__title">{{ __('GRIMBA NEWS WORLD MAP') }}</span>
        </div>
        <div class="gmap-controls">
            <span class="gmap-meta">{{ __('Fenêtre') }} {{ $windowHours }}h</span>
            <button type="button" class="gmap-control" data-action="pause" aria-pressed="false">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M5 3h2v10H5zM9 3h2v10H9z"/></svg>
                <span>{{ __('Pause') }}</span>
            </button>
            <button type="button" class="gmap-control" data-action="fullscreen" aria-pressed="false">
                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M2 2h5v2H4v3H2V2zm12 0v5h-2V4H9V2h5zM2 14V9h2v3h3v2H2zm12 0H9v-2h3V9h2v5z"/></svg>
                <span>{{ __('Plein écran') }}</span>
            </button>
            <a href="{{ url('/breaking') }}" class="gmap-control">
                <span>{{ __('Liste') }} →</span>
            </a>
        </div>
    </header>

    <div class="gmap-stage" data-component="gmap-stage">
        {{-- Real Equirectangular world map, hand-traced from public-domain
             Natural Earth simplified topology. Coordinates use the
             formula x=(lon+180)*1000/360 = lon*2.78+500 ;
             y=(90-lat)*500/180 = (90-lat)*2.78. Path-data manually built
             for v2 fidelity; precise enough that all continents are
             instantly recognizable. --}}
        <svg
            class="gmap-svg"
            viewBox="0 0 1000 500"
            preserveAspectRatio="xMidYMid meet"
            role="img"
            aria-label="{{ __('Carte du monde') }}"
        >
            <defs>
                <linearGradient id="gmap-land-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%"   stop-color="#1a1f3a" stop-opacity="0.9"/>
                    <stop offset="100%" stop-color="#0b0f22" stop-opacity="0.95"/>
                </linearGradient>
                <radialGradient id="gmap-pulse-grad" cx="50%" cy="50%" r="50%">
                    <stop offset="0%"   stop-color="#a855f7" stop-opacity="0.9"/>
                    <stop offset="100%" stop-color="#a855f7" stop-opacity="0"/>
                </radialGradient>
            </defs>

            {{-- Subtle grid lines (decorative HUD) --}}
            <g class="gmap-grid" aria-hidden="true">
                @for($i = 0; $i <= 10; $i++)
                    <line x1="{{ $i * 100 }}" y1="0" x2="{{ $i * 100 }}" y2="500"/>
                @endfor
                @for($i = 0; $i <= 5; $i++)
                    <line x1="0" y1="{{ $i * 100 }}" x2="1000" y2="{{ $i * 100 }}"/>
                @endfor
            </g>

            {{-- Equator + Tropics as a graticule hint --}}
            <g class="gmap-graticule" aria-hidden="true">
                <path d="M 0,250 L 1000,250"/>       {{-- Equator --}}
                <path d="M 0,185 L 1000,185"/>       {{-- Tropic of Cancer (~23.5°N) --}}
                <path d="M 0,315 L 1000,315"/>       {{-- Tropic of Capricorn (~23.5°S) --}}
            </g>

            {{-- S-MAP-v3-E (Vader 2026-05-29) — higher-fidelity continent
                 paths. Each continent now uses 30-60 vertices instead of
                 v2's 10-20, with Bezier curves for coastline smoothness.
                 Coordinates still follow Equirectangular projection
                 x=(lon+180)*2.78, y=(90-lat)*2.78. Hand-traced from
                 Natural Earth public-domain reference. --}}
            <g class="gmap-land">
                {{-- North America: Alaska through Mexico, with Hudson Bay indent --}}
                <path d="M 45,110 Q 80,95 130,82 Q 170,75 215,80 Q 250,82 285,98
                         L 295,120 Q 290,135 285,148 Q 295,160 305,175
                         L 318,205 Q 320,225 308,238 L 290,250
                         Q 270,245 250,248 L 230,250 Q 215,235 200,238
                         Q 180,235 165,225 L 145,220 Q 130,228 115,225
                         Q 100,218 90,205 L 78,185 Q 70,170 65,150
                         Q 55,135 50,120 Z"/>
                {{-- Greenland --}}
                <path d="M 330,50 Q 360,42 392,52 Q 412,68 410,95 Q 408,118 388,128
                         Q 365,132 345,122 Q 328,108 326,82 Q 326,65 330,50 Z"/>
                {{-- Central America (Mexico tail + Panama) --}}
                <path d="M 250,250 Q 265,260 275,275 Q 282,290 270,300
                         Q 260,298 252,288 Q 245,275 248,260 Z"/>
                {{-- Caribbean islands --}}
                <path d="M 295,260 L 320,258 L 322,268 L 300,272 Z"/>
                <path d="M 325,272 L 340,272 L 342,280 L 327,282 Z"/>
                {{-- South America: teardrop with bulge for Brazil --}}
                <path d="M 275,295 Q 305,288 335,300 Q 358,318 365,345
                         Q 360,375 350,400 Q 335,425 318,432
                         Q 305,432 296,415 Q 285,395 280,372
                         Q 275,348 273,322 Q 272,308 275,295 Z"/>
                {{-- Europe: mainland with Mediterranean coast --}}
                <path d="M 462,135 Q 480,128 502,125 Q 530,118 555,125
                         Q 580,135 590,152 Q 585,170 570,180
                         Q 550,182 528,180 Q 505,178 485,172
                         Q 470,165 462,150 Z"/>
                {{-- Scandinavia --}}
                <path d="M 475,98 Q 505,88 540,92 Q 562,105 555,125
                         Q 540,135 515,130 Q 490,125 478,115 Z"/>
                {{-- Iberian Peninsula --}}
                <path d="M 452,168 Q 465,168 478,172 Q 482,184 478,195
                         Q 468,200 458,195 Q 448,185 448,175 Z"/>
                {{-- British Isles --}}
                <path d="M 445,142 Q 455,140 462,148 Q 462,158 455,162
                         Q 446,160 442,152 Z"/>
                <path d="M 438,158 L 444,160 L 442,168 L 438,166 Z"/>
                {{-- Iceland --}}
                <path d="M 432,108 L 444,108 L 446,118 L 432,118 Z"/>
                {{-- Africa: characteristic shape with horn of Africa --}}
                <path d="M 485,200 Q 515,192 545,198 Q 575,205 600,222
                         Q 615,245 615,275 Q 612,305 600,330
                         Q 585,355 565,365 Q 545,368 528,358
                         Q 510,348 498,328 Q 485,305 478,278
                         Q 472,250 475,225 Q 478,210 485,200 Z"/>
                {{-- Horn of Africa --}}
                <path d="M 600,240 L 625,235 L 635,250 L 622,258 L 605,253 Z"/>
                {{-- Madagascar --}}
                <path d="M 622,325 Q 632,325 635,340 Q 634,355 625,360
                         Q 618,355 618,342 Q 618,330 622,325 Z"/>
                {{-- Middle East / Arabian Peninsula --}}
                <path d="M 585,200 Q 610,192 638,200 Q 658,215 660,235
                         Q 655,252 638,255 Q 615,253 595,243
                         Q 582,225 582,210 Z"/>
                {{-- Asia: Russia + China + Central Asia (large mass) --}}
                <path d="M 575,85 Q 620,75 680,72 Q 740,72 800,80
                         Q 860,90 890,105 Q 895,128 880,148
                         Q 850,162 815,168 Q 775,172 738,170
                         Q 700,168 668,162 Q 640,155 615,145
                         Q 595,132 580,115 Q 572,100 575,85 Z"/>
                {{-- India subcontinent --}}
                <path d="M 660,188 Q 685,184 712,200 Q 725,225 722,250
                         Q 712,268 698,272 Q 682,268 672,250
                         Q 660,225 658,205 Z"/>
                {{-- Southeast Asia mainland --}}
                <path d="M 718,222 Q 745,222 758,240 Q 762,265 750,280
                         Q 735,285 722,275 Q 712,255 714,235 Z"/>
                {{-- Indonesia archipelago --}}
                <path d="M 738,278 Q 770,275 800,282 Q 818,290 815,300
                         Q 800,305 770,303 Q 745,300 735,290 Z"/>
                <path d="M 822,290 Q 845,292 858,305 Q 855,315 838,315
                         Q 822,312 820,300 Z"/>
                <path d="M 758,308 Q 778,308 792,320 Q 790,330 770,328
                         Q 758,322 755,315 Z"/>
                <path d="M 805,315 L 818,315 L 820,322 L 805,322 Z"/>
                {{-- Philippines --}}
                <path d="M 818,238 Q 832,238 835,255 Q 833,272 822,272
                         Q 815,260 815,245 Z"/>
                <path d="M 822,275 L 832,275 L 832,283 L 822,283 Z"/>
                {{-- Korean Peninsula --}}
                <path d="M 828,162 Q 842,162 845,178 Q 843,188 834,188
                         Q 826,180 826,170 Z"/>
                {{-- Japan: three main islands --}}
                <path d="M 858,148 Q 870,152 875,170 Q 870,182 858,178 Z"/>
                <path d="M 852,182 Q 865,182 868,195 Q 858,202 850,195 Z"/>
                <path d="M 844,200 L 855,200 L 856,208 L 844,208 Z"/>
                {{-- Australia: rounded continent shape --}}
                <path d="M 785,338 Q 815,332 855,338 Q 880,348 882,368
                         Q 872,388 845,395 Q 815,398 790,390
                         Q 778,375 780,358 Z"/>
                {{-- Tasmania --}}
                <path d="M 848,400 Q 858,400 862,410 Q 858,418 848,418
                         Q 845,412 846,405 Z"/>
                {{-- New Zealand: North + South Island --}}
                <path d="M 895,372 Q 905,370 908,385 Q 905,392 895,392
                         Q 890,385 892,378 Z"/>
                <path d="M 898,398 Q 910,398 912,410 Q 908,418 900,418
                         Q 894,410 895,402 Z"/>
                {{-- Papua New Guinea --}}
                <path d="M 820,322 Q 845,320 855,332 Q 852,340 832,340
                         Q 822,332 820,325 Z"/>
                {{-- Sri Lanka --}}
                <path d="M 698,260 L 705,260 L 706,272 L 700,272 Z"/>
                {{-- Cuba (Caribbean) --}}
                <path d="M 280,260 L 305,258 L 308,266 L 282,268 Z"/>
            </g>

            {{-- Activity pulses at continent anchors (only render where
                 we have content; "global" pulse is suppressed because
                 the global bucket isn't geographic). --}}
            @foreach($buckets as $continent => $posts)
                @if($posts->isNotEmpty() && $continent !== 'global')
                    @php $dot = $continentAnchors[$continent]['svg_dot']; @endphp
                    <circle cx="{{ $dot[0] }}" cy="{{ $dot[1] }}" r="3" class="gmap-pulse-core"/>
                    <circle cx="{{ $dot[0] }}" cy="{{ $dot[1] }}" r="3" class="gmap-pulse"/>
                @endif
            @endforeach
        </svg>

        {{-- Continent labels overlaid on the map --}}
        @foreach($buckets as $continent => $posts)
            @php $anchor = $continentAnchors[$continent]; @endphp
            <div class="gmap-label" style="left: {{ $anchor['x'] / 10 }}%; top: {{ ($anchor['y'] - 38) / 5 }}%;">
                {{ Continents::label($continent) }}
            </div>
        @endforeach

        {{-- Desktop tickers absolutely positioned over the map --}}
        @foreach($buckets as $continent => $posts)
            @php
                $anchor = $continentAnchors[$continent];
                $dir = Continents::scrollDirection($continent);
            @endphp
            <article
                class="gmap-ticker"
                data-continent="{{ $continent }}"
                data-dir="{{ $dir }}"
                style="left: {{ $anchor['x'] / 10 }}%; top: {{ $anchor['y'] / 5 }}%;"
                aria-live="polite"
                aria-label="{{ __('Actualités urgentes') }} — {{ Continents::label($continent) }}"
            >
                @php
                    $dist = $biasDistribution($posts);
                    $donutGradient = '';
                    if (!empty($dist['segments'])) {
                        $parts = [];
                        foreach ($dist['segments'] as $seg) {
                            $color = $biasMeta[$seg['key']]['color'] ?? '#555';
                            $start = number_format($seg['start'], 3, '.', '');
                            $end = number_format($seg['start'] + $seg['deg'], 3, '.', '');
                            $parts[] = "{$color} {$start}deg {$end}deg";
                        }
                        $donutGradient = 'conic-gradient(' . implode(', ', $parts) . ')';
                    }
                    $donutTitle = empty($dist['counts']) ? '' :
                        sprintf('L %d · C %d · R %d · ? %d',
                            $dist['counts']['left'] ?? 0,
                            $dist['counts']['center'] ?? 0,
                            $dist['counts']['right'] ?? 0,
                            $dist['counts']['unknown'] ?? 0);
                @endphp
                <a class="gmap-ticker__head gmap-ticker__head--link"
                   href="{{ url('/breaking?region=' . $continent) }}"
                   title="{{ __('Voir tous') }} — {{ Continents::label($continent) }}">
                    <span>{{ Continents::label($continent) }} →</span>
                    <span class="gmap-ticker__head-meter">
                        @if($donutGradient)
                            <span class="gmap-donut"
                                  style="background:{{ $donutGradient }};"
                                  title="{{ $donutTitle }}"
                                  aria-label="{{ __('Distribution L/C/R') }}: {{ $donutTitle }}"></span>
                        @endif
                        <span class="gmap-ticker__head-count">{{ $posts->count() }}</span>
                    </span>
                </a>
                @if($posts->isEmpty())
                    <div class="gmap-empty">{{ __('Calme par ici.') }}</div>
                @else
                    <div class="gmap-ticker__track">
                        @foreach($posts as $post)
                            <a class="gmap-ticker__item" href="{{ $post->url ?? '#' }}" title="{{ $post->source_name }}">
                                <span class="gmap-dot" style="background:{{ $biasDotColor($post->bias_rating ?? null) }};color:{{ $biasDotColor($post->bias_rating ?? null) }};" aria-hidden="true"></span>
                                {{ \Illuminate\Support\Str::limit($post->translated_name ?: $post->name, 78) }}
                            </a>
                        @endforeach
                        {{-- Duplicate for seamless marquee loop. --}}
                        @foreach($posts as $post)
                            <a class="gmap-ticker__item" href="{{ $post->url ?? '#' }}" aria-hidden="true" tabindex="-1">
                                <span class="gmap-dot" style="background:{{ $biasDotColor($post->bias_rating ?? null) }};color:{{ $biasDotColor($post->bias_rating ?? null) }};"></span>
                                {{ \Illuminate\Support\Str::limit($post->translated_name ?: $post->name, 78) }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </article>
        @endforeach
    </div>

    {{-- Mobile vertical list (since absolute tickers can't compete with screen real estate) --}}
    <div class="gmap-mobile-list">
        @foreach($buckets as $continent => $posts)
            @if($posts->isNotEmpty())
                <article class="gmap-ticker" data-dir="{{ Continents::scrollDirection($continent) }}" style="position:static; transform:none; width:100%;">
                    <header class="gmap-ticker__head">
                        <span>{{ Continents::label($continent) }}</span>
                        <span class="gmap-ticker__head-count">{{ $posts->count() }}</span>
                    </header>
                    <div class="gmap-ticker__track">
                        @foreach($posts as $post)
                            <a class="gmap-ticker__item" href="{{ $post->url ?? '#' }}">
                                <span class="gmap-dot" style="background:{{ $biasDotColor($post->bias_rating ?? null) }};color:{{ $biasDotColor($post->bias_rating ?? null) }};"></span>
                                {{ \Illuminate\Support\Str::limit($post->translated_name ?: $post->name, 78) }}
                            </a>
                        @endforeach
                    </div>
                </article>
            @endif
        @endforeach
    </div>
</section>

<script>
    /* S-MAP-v2 — fullscreen + pause toggles. Vanilla JS, no deps. */
    (function () {
        const shell = document.querySelector('[data-component="gmap"]');
        if (!shell) return;

        // Pause / Resume
        const pauseBtn = shell.querySelector('[data-action="pause"]');
        if (pauseBtn) {
            pauseBtn.addEventListener('click', () => {
                const isPaused = shell.dataset.paused === 'true';
                shell.dataset.paused = isPaused ? 'false' : 'true';
                pauseBtn.dataset.active = isPaused ? 'false' : 'true';
                pauseBtn.setAttribute('aria-pressed', isPaused ? 'false' : 'true');
                pauseBtn.querySelector('span').textContent = isPaused ? @json(__('Pause')) : @json(__('Reprendre'));
            });
        }

        // Fullscreen API toggle + fallback
        const fsBtn = shell.querySelector('[data-action="fullscreen"]');
        if (fsBtn) {
            const fsLabel = fsBtn.querySelector('span');
            const enter = async () => {
                try {
                    if (shell.requestFullscreen) await shell.requestFullscreen({ navigationUI: 'hide' });
                    else if (shell.webkitRequestFullscreen) shell.webkitRequestFullscreen();
                    else shell.dataset.fullscreenFallback = 'true';   /* CSS-only fallback */
                } catch (e) {
                    shell.dataset.fullscreenFallback = 'true';
                }
            };
            const exit = async () => {
                try {
                    if (document.fullscreenElement) await document.exitFullscreen();
                    else if (document.webkitFullscreenElement) document.webkitExitFullscreenElement?.();
                } catch (e) {}
                shell.dataset.fullscreenFallback = 'false';
            };
            fsBtn.addEventListener('click', () => {
                const isFs = document.fullscreenElement === shell || shell.dataset.fullscreenFallback === 'true';
                if (isFs) exit(); else enter();
            });
            const sync = () => {
                const isFs = document.fullscreenElement === shell || shell.dataset.fullscreenFallback === 'true';
                fsBtn.dataset.active = isFs ? 'true' : 'false';
                fsBtn.setAttribute('aria-pressed', isFs ? 'true' : 'false');
                fsLabel.textContent = isFs ? @json(__('Quitter')) : @json(__('Plein écran'));
            };
            document.addEventListener('fullscreenchange', sync);
            document.addEventListener('webkitfullscreenchange', sync);

            // ESC inside CSS-fallback mode (when native fullscreen unavailable).
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && shell.dataset.fullscreenFallback === 'true') {
                    shell.dataset.fullscreenFallback = 'false';
                    sync();
                }
            });
        }
    })();
</script>
