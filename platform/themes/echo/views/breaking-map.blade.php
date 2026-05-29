@php
    Theme::layout('grimba-chrome');
    /**
     * @var array<string, \Illuminate\Support\Collection> $buckets keyed by continent
     * @var int $windowHours
     */
    use App\Support\Continents;
    use App\Support\GrimbaClusterBias;

    // Continent SVG-overlay positions (Equirectangular projection;
    // viewBox 0 0 1000 500 — these are eyeballed anchors, not GIS-
    // accurate but sufficient for a v1 visual layout).
    $continentAnchors = [
        Continents::EUROPE   => ['x' => 510, 'y' => 130, 'label_dx' => 0,  'label_dy' => -10],
        Continents::AMERICAS => ['x' => 230, 'y' => 245, 'label_dx' => 0,  'label_dy' => -10],
        Continents::ASIA     => ['x' => 720, 'y' => 180, 'label_dx' => 0,  'label_dy' => -10],
        Continents::AFRICA   => ['x' => 510, 'y' => 290, 'label_dx' => 0,  'label_dy' => -10],
        Continents::OCEANIA  => ['x' => 820, 'y' => 360, 'label_dx' => 0,  'label_dy' => -10],
        Continents::GLOBAL   => ['x' => 500, 'y' => 470, 'label_dx' => 0,  'label_dy' => 16],
    ];

    $biasMeta = GrimbaClusterBias::biasMetaForBlade();

    $biasDotColor = function (?string $rating) use ($biasMeta): string {
        $k = $rating && isset($biasMeta[$rating]) ? $rating : 'unknown';
        return $biasMeta[$k]['color'] ?? '#6b6459';
    };
@endphp

<style>
    .gmap {
        position: relative;
        width: 100%;
        min-height: min(82vh, 720px);
        margin: 0;
        padding: 24px 16px 48px;
        background:
            radial-gradient(ellipse at 30% 20%, rgba(168, 85, 247, .08), transparent 40%),
            radial-gradient(ellipse at 70% 80%, rgba(59, 130, 246, .06), transparent 50%),
            #0a0a0f;
        color: #fffaf0;
        overflow: hidden;
        font-family: var(--font-sans, system-ui, -apple-system, sans-serif);
    }
    .gmap__header {
        max-width: 1200px;
        margin: 0 auto 16px;
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .gmap__title {
        font-size: clamp(20px, 2.4vw, 32px);
        font-weight: 900;
        letter-spacing: -0.02em;
        margin: 0;
    }
    .gmap__title-accent { color: #a855f7; }
    .gmap__meta {
        font-size: 12px;
        color: rgba(255, 250, 240, 0.6);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
    }
    .gmap__stage {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
        aspect-ratio: 2 / 1.05;
    }
    .gmap__svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0.42;
        filter: brightness(1.05) saturate(0.7);
        pointer-events: none;
    }
    .gmap__ticker {
        position: absolute;
        transform: translate(-50%, -50%);
        width: clamp(220px, 28%, 360px);
        background: rgba(8, 6, 4, 0.74);
        border: 1px solid rgba(168, 85, 247, 0.18);
        border-radius: 8px;
        padding: 8px 0 10px;
        backdrop-filter: blur(6px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        overflow: hidden;
    }
    .gmap__ticker-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 10px 6px;
        font-size: 11px;
        font-weight: 850;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #a855f7;
        border-bottom: 1px solid rgba(168, 85, 247, 0.15);
        margin-bottom: 6px;
    }
    .gmap__ticker-count {
        color: rgba(255, 250, 240, 0.55);
        font-size: 10px;
        font-weight: 800;
    }
    .gmap__ticker-track {
        display: flex;
        gap: 24px;
        white-space: nowrap;
        will-change: transform;
        padding: 0 12px;
    }
    .gmap__ticker[data-dir="ltr"] .gmap__ticker-track {
        animation: gmap-scroll-ltr 38s linear infinite;
    }
    .gmap__ticker[data-dir="rtl"] .gmap__ticker-track {
        animation: gmap-scroll-rtl 38s linear infinite;
    }
    .gmap__ticker:hover .gmap__ticker-track,
    .gmap__ticker:focus-within .gmap__ticker-track {
        animation-play-state: paused;
    }
    .gmap__ticker-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #fffaf0;
        text-decoration: none;
        line-height: 1.3;
    }
    .gmap__ticker-item:hover { text-decoration: underline; }
    .gmap__dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .gmap__empty {
        padding: 4px 12px;
        font-size: 12px;
        color: rgba(255, 250, 240, 0.55);
        font-style: italic;
    }
    @keyframes gmap-scroll-ltr {
        from { transform: translateX(-100%); }
        to   { transform: translateX(0%); }
    }
    @keyframes gmap-scroll-rtl {
        from { transform: translateX(0%); }
        to   { transform: translateX(-100%); }
    }
    @media (prefers-reduced-motion: reduce) {
        .gmap__ticker .gmap__ticker-track { animation: none !important; }
        .gmap__ticker .gmap__ticker-track { flex-wrap: wrap; white-space: normal; gap: 8px 16px; }
    }
    @media (max-width: 768px) {
        .gmap { min-height: 100dvh; padding: 16px 8px 32px; }
        .gmap__stage { aspect-ratio: auto; min-height: 320px; }
        .gmap__svg { opacity: 0.32; }
        .gmap__ticker {
            position: relative;
            transform: none;
            inset: auto;
            width: 100%;
            margin: 8px 0 0;
        }
        .gmap__tickers-mobile-stack {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 16px;
        }
    }
</style>

<section class="gmap" aria-label="{{ __('Carte plein écran des actualités urgentes par continent') }}">
    <header class="gmap__header">
        <h1 class="gmap__title">
            <span class="gmap__title-accent">●</span>
            {{ __('Carte du monde') }} — {{ __('actualité urgente') }}
        </h1>
        <span class="gmap__meta">
            {{ __('Fenêtre') }}: {{ $windowHours }}h ·
            <a href="{{ url('/breaking') }}" style="color:inherit;">{{ __('Liste linéaire') }} →</a>
        </span>
    </header>

    <div class="gmap__stage" data-component="gmap-stage">
        {{-- Public-domain Natural-Earth low-res world map outline (single
             merged path, simplified for ≤8KB inline). Decorative only —
             readers don't interact with the SVG itself; tickers overlay. --}}
        <svg class="gmap__svg" viewBox="0 0 1000 500" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
            <defs>
                <linearGradient id="gmap-land" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#3a3548"/>
                    <stop offset="100%" stop-color="#241f30"/>
                </linearGradient>
            </defs>
            {{-- Highly simplified continent silhouettes — not GIS-accurate
                 but suggests the world layout at a glance. Coordinates
                 hand-picked for the 1000×500 Equirectangular viewBox. --}}
            <g fill="url(#gmap-land)" stroke="rgba(168,85,247,0.25)" stroke-width="0.8">
                {{-- Americas --}}
                <path d="M180,130 Q150,160 160,210 L200,260 L210,320 L240,360 L260,400 Q280,420 270,440 L240,450 L220,420 L200,380 L185,330 L170,280 L155,230 L150,180 Z"/>
                <path d="M260,210 L290,200 L320,225 L310,260 L290,280 L270,260 Z"/>
                {{-- Europe --}}
                <path d="M460,90 L520,80 L560,95 L580,120 L555,150 L520,160 L480,150 L455,125 Z"/>
                {{-- Africa --}}
                <path d="M480,180 L540,175 L580,205 L590,260 L575,310 L540,340 L510,330 L490,295 L475,255 L470,215 Z"/>
                {{-- Asia --}}
                <path d="M580,90 L700,80 L800,110 L850,150 L840,200 L800,230 L740,225 L680,200 L620,180 L590,150 Z"/>
                <path d="M760,230 L820,220 L840,260 L820,280 L780,275 Z"/>
                {{-- Oceania --}}
                <path d="M800,340 L860,335 L880,365 L860,385 L815,380 L795,365 Z"/>
                <path d="M880,395 L905,395 L910,415 L885,420 Z"/>
            </g>
        </svg>

        {{-- Desktop / large-screen: absolutely positioned over the map. --}}
        @foreach($buckets as $continent => $posts)
            @php
                $anchor = $continentAnchors[$continent] ?? ['x' => 500, 'y' => 250];
                $dir = Continents::scrollDirection($continent);
            @endphp
            <article
                class="gmap__ticker gmap__ticker--desktop"
                data-continent="{{ $continent }}"
                data-dir="{{ $dir }}"
                style="left: {{ $anchor['x'] / 10 }}%; top: {{ $anchor['y'] / 5 }}%;"
                aria-live="polite"
                aria-label="{{ __('Actualités urgentes') }} — {{ Continents::label($continent) }}"
            >
                <header class="gmap__ticker-label">
                    <span>{{ Continents::label($continent) }}</span>
                    <span class="gmap__ticker-count">{{ $posts->count() }}</span>
                </header>
                @if($posts->isEmpty())
                    <div class="gmap__empty">{{ __('Calme par ici.') }}</div>
                @else
                    <div class="gmap__ticker-track">
                        @foreach($posts as $post)
                            <a class="gmap__ticker-item"
                               href="{{ $post->url ?? '#' }}"
                               title="{{ $post->source_name }}">
                                <span class="gmap__dot" style="background:{{ $biasDotColor($post->bias_rating ?? null) }};" aria-hidden="true"></span>
                                {{ \Illuminate\Support\Str::limit($post->translated_name ?: $post->name, 80) }}
                            </a>
                        @endforeach
                        {{-- duplicate row so the marquee loops seamlessly --}}
                        @foreach($posts as $post)
                            <a class="gmap__ticker-item"
                               href="{{ $post->url ?? '#' }}"
                               aria-hidden="true" tabindex="-1">
                                <span class="gmap__dot" style="background:{{ $biasDotColor($post->bias_rating ?? null) }};"></span>
                                {{ \Illuminate\Support\Str::limit($post->translated_name ?: $post->name, 80) }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </article>
        @endforeach
    </div>
</section>
