@php
    Theme::layout('grimba-chrome');
    /**
     * S-MAP-V4-09 (Vader 2026-05-30) — the REAL map. Replaces the v1-v3
     * hand-rolled SVG (frozen at /breaking-map-legacy) with Leaflet + CARTO
     * Dark Matter tiles + Natural Earth country boundaries, with the v3
     * futurist-HUD chrome layered on top. Pins/clusters arrive in V4-10 from
     * /api/breaking-map.json.
     *
     * @var array<string, \Illuminate\Support\Collection> $buckets
     * @var int $windowHours
     */
    use App\Support\GrimbaHomeFeed;

    $readerLocale = GrimbaHomeFeed::resolveReaderLocale();
    // Client fetches the API with an explicit ?lang= so the shared-cache key
    // is URL-based (belt with the endpoint's Vary: Cookie suspenders).
    $apiUrl = url('/api/breaking-map.json') . '?window=' . $windowHours . '&lang=' . $readerLocale;
    $geoJsonUrl = asset('vendor/natural-earth/world.geojson');
@endphp

<link rel="preconnect" href="https://a.basemaps.cartocdn.com" crossorigin>
<link rel="preconnect" href="https://b.basemaps.cartocdn.com" crossorigin>
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/leaflet/MarkerCluster.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/leaflet/MarkerCluster.Default.css') }}">

<style>
    /* ── S-MAP-V4 cinematic / HUD chrome (carried from v2/v3) ───────── */
    .gmap-shell {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        min-height: 100dvh;
        margin: 0;
        padding: 0;
        background: #04050a;
        color: #e8f4ff;
        overflow: hidden;
        font-family: var(--font-sans, system-ui, -apple-system, sans-serif);
    }

    .gmap-chrome {
        position: relative;
        z-index: 5;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        border-bottom: 1px solid rgba(34, 211, 238, .15);
        background: rgba(4, 6, 12, .72);
        backdrop-filter: blur(6px);
    }
    .gmap-brand {
        display: flex; align-items: center; gap: 12px;
        font-size: 14px; font-weight: 800; letter-spacing: .06em;
        text-transform: uppercase; color: #22d3ee;
    }
    .gmap-brand__pulse {
        display: inline-block; width: 9px; height: 9px; border-radius: 50%;
        background: #22d3ee;
        box-shadow: 0 0 12px #22d3ee, 0 0 24px rgba(34, 211, 238, .5);
        animation: gmap-brand-pulse 1.8s ease-in-out infinite;
    }
    @keyframes gmap-brand-pulse {
        0%, 100% { transform: scale(1);   opacity: 1;   }
        50%      { transform: scale(1.4); opacity: .55; }
    }
    .gmap-brand__sep { color: rgba(232, 244, 255, .35); margin: 0 4px; }
    .gmap-brand__title { color: #e8f4ff; letter-spacing: .02em; }
    .gmap-controls { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .gmap-control {
        appearance: none;
        background: rgba(34, 211, 238, .08);
        border: 1px solid rgba(34, 211, 238, .35);
        color: #e8f4ff; font: inherit; font-size: 12px; font-weight: 800;
        text-transform: uppercase; letter-spacing: .05em;
        padding: 8px 14px; border-radius: 4px; cursor: pointer;
        transition: background .15s, border-color .15s, box-shadow .15s;
        display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
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
        border-color: #a855f7; color: #f5d4ff;
    }
    .gmap-meta {
        font-size: 11px; color: rgba(232, 244, 255, .55);
        text-transform: uppercase; letter-spacing: .08em; font-weight: 700;
    }

    /* ── Stage + Leaflet map ────────────────────────────────────────── */
    .gmap-stage {
        position: relative;
        z-index: 2;
        flex: 1 1 auto;
        width: 100%;
        min-height: 60dvh;
    }
    .gmap-leaflet {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        background: #04050a;   /* shows through before tiles paint */
    }

    /* HUD overlay — scan-lines + cyan grid + radial glow, layered ABOVE the
       tiles but pointer-events:none so the map stays fully interactive and
       Leaflet controls below it stay clickable. */
    .gmap-hud {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 450;
        background:
            radial-gradient(ellipse at 18% 8%,  rgba(168, 85, 247, .12), transparent 45%),
            radial-gradient(ellipse at 82% 88%, rgba(34, 211, 238, .10), transparent 50%),
            repeating-linear-gradient(to bottom,
                transparent 0px, transparent 3px,
                rgba(34, 211, 238, .020) 3px, rgba(34, 211, 238, .020) 4px);
        mix-blend-mode: screen;
    }
    .gmap-hud::after {            /* corner vignette for the "viewport" feel */
        content: '';
        position: absolute; inset: 0;
        box-shadow: inset 0 0 120px 30px rgba(4, 6, 12, .65);
    }

    /* Leaflet control theming to match the HUD. */
    .gmap-leaflet .leaflet-control-zoom a {
        background: rgba(4, 6, 12, .85);
        color: #22d3ee;
        border: 1px solid rgba(34, 211, 238, .35);
        border-radius: 4px;
        transition: background .15s, box-shadow .15s;
    }
    .gmap-leaflet .leaflet-control-zoom a:hover {
        background: rgba(34, 211, 238, .18);
        box-shadow: 0 0 12px rgba(34, 211, 238, .4);
        color: #e8f4ff;
    }
    .gmap-leaflet .leaflet-bar { border: none; box-shadow: 0 0 0 1px rgba(34,211,238,.12); }
    .gmap-leaflet .leaflet-control-attribution {
        background: rgba(4, 6, 12, .78);
        color: rgba(232, 244, 255, .5);
        font-size: 10px;
        padding: 2px 8px;
        border-top-left-radius: 4px;
    }
    .gmap-leaflet .leaflet-control-attribution a { color: rgba(34, 211, 238, .8); }

    /* Skip-to-list (a11y) — visible on focus only. */
    .gmap-skip {
        position: absolute; left: 12px; top: 12px; z-index: 600;
        padding: 8px 14px; border-radius: 4px;
        background: #a855f7; color: #fff; font-weight: 800; font-size: 12px;
        text-transform: uppercase; letter-spacing: .05em; text-decoration: none;
        transform: translateY(-160%); transition: transform .18s;
    }
    .gmap-skip:focus { transform: translateY(0); outline: 2px solid #fff; }

    /* Loading / status line (ARIA live). */
    .gmap-status {
        position: absolute; left: 50%; top: 16px; transform: translateX(-50%);
        z-index: 500;
        padding: 6px 16px; border-radius: 999px;
        background: rgba(4, 6, 12, .82);
        border: 1px solid rgba(34, 211, 238, .3);
        color: rgba(232, 244, 255, .8);
        font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .5);
        transition: opacity .3s;
    }
    .gmap-status[hidden] { display: none; }

    /* Pause freezes the brand pulse (markers/tiles handled in V4-13 JS). */
    .gmap-shell[data-paused="true"] .gmap-brand__pulse { animation-play-state: paused; }

    @media (prefers-reduced-motion: reduce) {
        .gmap-brand__pulse { animation: none !important; }
    }

    /* Mobile: map fills 60dvh; the card-stack sidecar arrives in V4-16. */
    @media (max-width: 768px) {
        .gmap-stage { min-height: 60dvh; height: 60dvh; flex: 0 0 auto; }
    }

    /* Fullscreen (native API or CSS fallback). */
    .gmap-shell:fullscreen,
    .gmap-shell:-webkit-full-screen,
    .gmap-shell[data-fullscreen-fallback="true"] {
        position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 9999;
    }
</style>

<section
    class="gmap-shell"
    data-component="gmap"
    data-api-url="{{ $apiUrl }}"
    data-geojson-url="{{ $geoJsonUrl }}"
    aria-label="{{ __('Carte mondiale en direct des actualités urgentes') }}"
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
        <a class="gmap-skip" href="{{ url('/breaking') }}">{{ __('Aller à la liste des actualités') }}</a>
        <div id="gmap-leaflet" class="gmap-leaflet" role="application"
             aria-label="{{ __('Carte interactive des actualités par pays') }}"></div>
        <div class="gmap-hud" aria-hidden="true"></div>
        <div class="gmap-status" role="status" aria-live="polite">{{ __('Chargement de la carte…') }}</div>
    </div>

    <noscript>
        <div style="padding:24px;text-align:center;">
            <a href="{{ url('/breaking') }}" style="color:#22d3ee;">{{ __('Voir la liste des actualités urgentes') }} →</a>
        </div>
    </noscript>
</section>

<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script src="{{ asset('vendor/leaflet/leaflet.markercluster.js') }}"></script>
<script>
/* S-MAP-V4-09 — real-map init: CARTO Dark Matter tiles + Natural Earth
   country fill + the v3 HUD chrome controls. Pins/clusters land in V4-10. */
(function () {
    const shell = document.querySelector('[data-component="gmap"]');
    if (!shell || typeof L === 'undefined') return;

    const statusEl = shell.querySelector('.gmap-status');
    const setStatus = (msg) => {
        if (!statusEl) return;
        if (msg) { statusEl.textContent = msg; statusEl.hidden = false; }
        else { statusEl.hidden = true; }
    };

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── Map ──────────────────────────────────────────────────────────
    const map = L.map('gmap-leaflet', {
        center: [22, 12],
        zoom: 2,
        minZoom: 2,
        maxZoom: 8,
        zoomControl: true,
        worldCopyJump: true,
        maxBounds: [[-85, -200], [85, 200]],
        maxBoundsViscosity: 0.7,
        attributionControl: true,
    });

    // CARTO Dark Matter base tiles (open map data; OSM + CARTO attribution).
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        maxZoom: 19,
        detectRetina: true,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a> · Natural Earth',
    }).addTo(map);

    // ── Natural Earth country fill (cyan HUD echo of the v3 .gmap-land) ─
    const geoUrl = shell.dataset.geojsonUrl;
    if (geoUrl) {
        fetch(geoUrl)
            .then((r) => r.ok ? r.json() : Promise.reject(r.status))
            .then((gj) => {
                L.geoJSON(gj, {
                    interactive: false,
                    style: {
                        color: 'rgba(34, 211, 238, .32)',
                        weight: 0.6,
                        fillColor: '#0d1430',
                        fillOpacity: 0.18,
                        lineJoin: 'round',
                    },
                }).addTo(map);
            })
            .catch(() => { /* tiles still render; country fill is enhancement */ });
    }

    // Expose for V4-10 (markers/cluster layer attaches here).
    shell._gmap = { map, setStatus, reduceMotion };
    setStatus(null); // base map ready; V4-10 re-shows it while fetching pins.

    // ── Chrome: Pause + Fullscreen (v3 behavior; Leaflet-aware in V4-13/14) ─
    const pauseBtn = shell.querySelector('[data-action="pause"]');
    if (pauseBtn) {
        const pauseLabel = pauseBtn.querySelector('span');
        pauseBtn.addEventListener('click', () => {
            const paused = shell.dataset.paused === 'true';
            shell.dataset.paused = paused ? 'false' : 'true';
            pauseBtn.dataset.active = paused ? 'false' : 'true';
            pauseBtn.setAttribute('aria-pressed', paused ? 'false' : 'true');
            pauseLabel.textContent = paused ? @json(__('Pause')) : @json(__('Reprendre'));
        });
    }

    const fsBtn = shell.querySelector('[data-action="fullscreen"]');
    if (fsBtn) {
        const fsLabel = fsBtn.querySelector('span');
        const enter = async () => {
            try {
                if (shell.requestFullscreen) await shell.requestFullscreen({ navigationUI: 'hide' });
                else if (shell.webkitRequestFullscreen) shell.webkitRequestFullscreen();
                else shell.dataset.fullscreenFallback = 'true';
            } catch (e) { shell.dataset.fullscreenFallback = 'true'; }
        };
        const exit = async () => {
            try {
                if (document.fullscreenElement) await document.exitFullscreen();
                else if (document.webkitFullscreenElement) document.webkitExitFullscreen?.();
            } catch (e) {}
            shell.dataset.fullscreenFallback = 'false';
        };
        fsBtn.addEventListener('click', () => {
            const isFs = document.fullscreenElement === shell || shell.dataset.fullscreenFallback === 'true';
            isFs ? exit() : enter();
        });
        const sync = () => {
            const isFs = document.fullscreenElement === shell || shell.dataset.fullscreenFallback === 'true';
            fsBtn.dataset.active = isFs ? 'true' : 'false';
            fsBtn.setAttribute('aria-pressed', isFs ? 'true' : 'false');
            fsLabel.textContent = isFs ? @json(__('Quitter')) : @json(__('Plein écran'));
            // Leaflet must recompute its size after the container resizes.
            setTimeout(() => map.invalidateSize(), 120);
        };
        document.addEventListener('fullscreenchange', sync);
        document.addEventListener('webkitfullscreenchange', sync);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && shell.dataset.fullscreenFallback === 'true') {
                shell.dataset.fullscreenFallback = 'false';
                sync();
            }
        });
    }

    // Keep the map sized to its container on viewport changes.
    window.addEventListener('resize', () => map.invalidateSize());
})();
</script>
