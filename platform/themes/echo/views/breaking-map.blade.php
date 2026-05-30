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
    }
    .gmap-ticker__head-count {
        color: rgba(232, 244, 255, .55);
        font-size: 10px;
        font-weight: 800;
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

            {{-- Continents — real shapes, manually traced. --}}
            <g class="gmap-land">
                {{-- North America (Alaska + Canada + USA + Mexico) --}}
                <path d="M 40,120 L 110,90 L 180,80 L 230,90 L 290,110 L 300,140 L 290,170 L 305,195 L 320,215 L 290,240 L 250,250 L 230,230 L 200,235 L 170,225 L 145,235 L 125,225 L 105,205 L 90,180 L 80,160 L 60,140 Z"/>
                {{-- Greenland --}}
                <path d="M 330,55 L 380,50 L 405,75 L 400,110 L 375,125 L 345,115 L 330,95 Z"/>
                {{-- Central America + Caribbean stub --}}
                <path d="M 250,250 L 270,265 L 280,280 L 270,295 L 255,295 L 245,275 Z"/>
                {{-- South America --}}
                <path d="M 280,290 L 310,285 L 340,300 L 355,335 L 350,365 L 335,395 L 320,420 L 305,425 L 295,400 L 285,365 L 280,335 Z"/>
                {{-- Europe (UK + mainland + Iberia + Scandinavia) --}}
                <path d="M 470,135 L 505,120 L 540,115 L 565,130 L 585,150 L 570,170 L 540,180 L 510,175 L 485,170 L 470,160 Z"/>
                <path d="M 475,100 L 505,90 L 535,95 L 555,110 L 540,125 L 510,125 L 485,115 Z"/>  {{-- Scandinavia --}}
                <path d="M 460,170 L 475,170 L 478,185 L 465,190 L 455,180 Z"/>                   {{-- Iberia --}}
                <path d="M 450,150 L 460,148 L 462,160 L 452,160 Z"/>                              {{-- UK --}}
                {{-- Africa --}}
                <path d="M 490,205 L 535,195 L 575,205 L 600,225 L 610,260 L 600,295 L 580,325 L 555,345 L 530,360 L 510,355 L 495,335 L 480,305 L 475,275 L 478,240 Z"/>
                {{-- Madagascar --}}
                <path d="M 615,330 L 625,328 L 628,348 L 620,355 L 614,345 Z"/>
                {{-- Middle East --}}
                <path d="M 580,195 L 615,190 L 640,205 L 655,225 L 645,245 L 620,245 L 600,235 L 585,215 Z"/>
                {{-- Asia mainland (Russia + China + India peninsula + SE Asia) --}}
                <path d="M 575,90 L 640,85 L 700,80 L 760,85 L 820,95 L 870,110 L 880,140 L 855,160 L 820,175 L 780,180 L 740,180 L 700,175 L 665,170 L 640,160 L 615,150 L 590,135 L 580,115 Z"/>
                {{-- India --}}
                <path d="M 660,195 L 690,190 L 710,210 L 720,240 L 705,260 L 685,265 L 670,245 L 660,220 Z"/>
                {{-- SE Asia mainland --}}
                <path d="M 720,225 L 745,225 L 755,245 L 750,265 L 730,275 L 720,260 L 715,240 Z"/>
                {{-- Indonesia + Malaysia (broken islands) --}}
                <path d="M 745,275 L 775,272 L 800,280 L 815,290 L 805,300 L 770,300 L 745,290 Z"/>
                <path d="M 825,295 L 845,295 L 855,310 L 845,318 L 825,315 Z"/>
                <path d="M 765,310 L 785,310 L 795,322 L 780,328 L 765,322 Z"/>
                {{-- Japan --}}
                <path d="M 855,160 L 868,165 L 870,180 L 858,182 Z"/>
                <path d="M 850,185 L 862,188 L 860,200 L 850,198 Z"/>
                {{-- Philippines --}}
                <path d="M 820,240 L 832,245 L 830,265 L 820,265 Z"/>
                {{-- Korean Peninsula --}}
                <path d="M 830,165 L 842,165 L 844,180 L 832,182 Z"/>
                {{-- Australia --}}
                <path d="M 790,345 L 830,340 L 865,345 L 880,365 L 870,385 L 845,395 L 810,395 L 790,380 L 785,360 Z"/>
                {{-- Tasmania --}}
                <path d="M 850,400 L 862,400 L 862,410 L 850,410 Z"/>
                {{-- New Zealand --}}
                <path d="M 895,375 L 905,375 L 910,390 L 900,395 L 893,388 Z"/>
                <path d="M 900,400 L 910,400 L 912,412 L 902,415 Z"/>
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
                <header class="gmap-ticker__head">
                    <span>{{ Continents::label($continent) }}</span>
                    <span class="gmap-ticker__head-count">{{ $posts->count() }}</span>
                </header>
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
