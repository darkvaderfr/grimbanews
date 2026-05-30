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

    /* ── S-MAP-V4-10 bias pin markers + cluster bias-mix donuts ─────── */
    /* Reset Leaflet's default white divIcon box for our custom icons. */
    .gmap-pin-wrap, .gmap-cluster-wrap { background: transparent; border: 0; }

    .gmap-pin {
        position: relative; width: 34px; height: 34px;
        display: grid; place-items: center;
        filter: drop-shadow(0 0 6px var(--glow, #22d3ee));
        cursor: pointer;
    }
    .gmap-pin::before {            /* pulse ring */
        content: ''; position: absolute; inset: 0; border-radius: 50%;
        border: 1px solid var(--glow, #22d3ee);
        animation: gmap-pin-pulse 2.6s ease-out infinite;
    }
    @keyframes gmap-pin-pulse {
        0%   { transform: scale(.7); opacity: .8; }
        70%  { transform: scale(1.85); opacity: 0; }
        100% { opacity: 0; }
    }
    .gmap-pin__donut {            /* conic bias-mix ring */
        position: absolute; inset: 0; border-radius: 50%;
        box-shadow: 0 0 0 1px rgba(4, 6, 12, .85);
    }
    .gmap-pin__donut::after {
        content: ''; position: absolute; inset: 5px; border-radius: 50%;
        background: rgba(4, 6, 12, .94);
    }
    .gmap-pin__count {
        position: relative; z-index: 1;
        font: 800 11px/1 var(--font-sans, system-ui, sans-serif);
        color: #e8f4ff; letter-spacing: .02em;
    }

    .gmap-cluster {
        position: relative; width: 48px; height: 48px;
        display: grid; place-items: center;
        filter: drop-shadow(0 0 10px rgba(168, 85, 247, .6));
        cursor: pointer;
    }
    .gmap-cluster__donut { position: absolute; inset: 0; border-radius: 50%; }
    .gmap-cluster__donut::after {
        content: ''; position: absolute; inset: 6px; border-radius: 50%;
        background: rgba(8, 6, 18, .95);
        box-shadow: inset 0 0 10px rgba(168, 85, 247, .5);
    }
    .gmap-cluster__count {
        position: relative; z-index: 1;
        font: 800 13px/1 var(--font-sans, system-ui, sans-serif);
        color: #f5d4ff; letter-spacing: .02em;
    }
    .gmap-shell[data-paused="true"] .gmap-pin::before { animation-play-state: paused; }
    @media (prefers-reduced-motion: reduce) { .gmap-pin::before { animation: none; } }

    /* ── S-MAP-V4-11 per-pin popup (HUD-themed Leaflet popup) ───────── */
    .gmap-pop-wrap .leaflet-popup-content-wrapper {
        background: rgba(6, 8, 18, .96);
        color: #e8f4ff;
        border: 1px solid rgba(168, 85, 247, .35);
        border-radius: 8px;
        box-shadow: 0 14px 44px rgba(0, 0, 0, .65);
        backdrop-filter: blur(6px);
    }
    .gmap-pop-wrap .leaflet-popup-content { margin: 12px 14px; }
    .gmap-pop-wrap .leaflet-popup-tip {
        background: rgba(6, 8, 18, .96);
        border: 1px solid rgba(168, 85, 247, .35);
    }
    .gmap-pop-wrap a.leaflet-popup-close-button { color: rgba(232, 244, 255, .6); }
    .gmap-pop__head {
        display: flex; align-items: baseline; justify-content: space-between; gap: 10px;
        padding-bottom: 8px; margin-bottom: 8px;
        border-bottom: 1px solid rgba(34, 211, 238, .18);
    }
    .gmap-pop__head strong {
        font-size: 14px; color: #22d3ee; letter-spacing: .04em; text-transform: uppercase;
    }
    .gmap-pop__total {
        font-size: 10px; font-weight: 800; color: rgba(232, 244, 255, .55);
        text-transform: uppercase; letter-spacing: .06em; white-space: nowrap;
    }
    .gmap-pop__list {
        list-style: none; margin: 0; padding: 0;
        display: flex; flex-direction: column; gap: 9px;
        max-height: 260px; overflow-y: auto;
    }
    .gmap-pop__item { display: flex; gap: 9px; align-items: flex-start; text-decoration: none; color: #e8f4ff; }
    .gmap-pop__item:hover .gmap-pop__title { text-decoration: underline; color: #fff; }
    .gmap-pop__dot { width: 9px; height: 9px; border-radius: 50%; margin-top: 4px; flex-shrink: 0; box-shadow: 0 0 6px currentColor; }
    .gmap-pop__txt { display: flex; flex-direction: column; gap: 2px; }
    .gmap-pop__title { font-size: 13px; line-height: 1.3; font-weight: 600; }
    .gmap-pop__src { font-size: 10px; color: rgba(34, 211, 238, .75); text-transform: uppercase; letter-spacing: .04em; }
</style>

<section
    class="gmap-shell"
    data-component="gmap"
    data-api-url="{{ $apiUrl }}"
    data-geojson-url="{{ $geoJsonUrl }}"
    data-locale="{{ $readerLocale }}"
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

<script>
/* S-MAP-V4-10 — fetch /api/breaking-map.json and render one bias-mix-donut
   marker per country (total_at_country in the center), clustered into
   purple-glow bias-mix donut bubbles (Vader decision #4, on-brand with the
   v3-A per-ticker donut). */
(function () {
    const shell = document.querySelector('[data-component="gmap"]');
    if (!shell || !shell._gmap || typeof L === 'undefined' || typeof L.markerClusterGroup !== 'function') return;

    const { map, setStatus } = shell._gmap;
    const apiUrl = shell.dataset.apiUrl;
    if (!apiUrl) return;

    // Must mirror GrimbaClusterBias::biasMetaForBlade() (server palette).
    const BIAS_ORDER = ['left', 'center', 'right', 'unknown'];
    const BIAS_COLOR = { left: '#3b82f6', center: '#a8a8a8', right: '#e84c3d', unknown: '#6b6459' };
    const fmt = (n) => n >= 1000 ? (n / 1000).toFixed(n >= 10000 ? 0 : 1) + 'k' : String(n);

    const donutGradient = (counts) => {
        const total = BIAS_ORDER.reduce((s, k) => s + (counts[k] || 0), 0);
        if (!total) return 'conic-gradient(#3a3a3a 0deg 360deg)';
        let deg = 0; const stops = [];
        for (const k of BIAS_ORDER) {
            const c = counts[k] || 0; if (!c) continue;
            const end = deg + (c / total) * 360;
            stops.push(BIAS_COLOR[k] + ' ' + deg.toFixed(2) + 'deg ' + end.toFixed(2) + 'deg');
            deg = end;
        }
        return 'conic-gradient(' + stops.join(',') + ')';
    };
    const countsFromPosts = (posts) => {
        const c = { left: 0, center: 0, right: 0, unknown: 0 };
        (posts || []).forEach((p) => { const k = (c[p.bias_rating] !== undefined) ? p.bias_rating : 'unknown'; c[k]++; });
        return c;
    };
    const dominantColor = (counts) => {
        let best = 'unknown', n = -1;
        for (const k of BIAS_ORDER) if ((counts[k] || 0) > n) { n = counts[k] || 0; best = k; }
        return BIAS_COLOR[best];
    };

    // ── V4-11 popup helpers ──────────────────────────────────────────
    // Titles/source come from RSS feeds (untrusted) and go into popup
    // innerHTML, so escape rigorously to prevent XSS.
    const escapeHtml = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const locale = (shell.dataset.locale || document.documentElement.lang || 'fr').slice(0, 2);
    let regionNames = null;
    try { regionNames = new Intl.DisplayNames([locale], { type: 'region' }); } catch (e) { regionNames = null; }
    const countryLabel = (iso) => {
        if (!iso) return '';
        try { return (regionNames && regionNames.of(iso)) || iso; } catch (e) { return iso; }
    };
    const L_STORIES = @json(__('actualités'));
    const L_READ = @json(__("Lire l'article"));
    const buildPopup = (pin) => {
        const items = (pin.posts || []).map((p) =>
            '<li><a class="gmap-pop__item" href="' + escapeHtml(p.url || '#') + '">'
            + '<span class="gmap-pop__dot" style="background:' + escapeHtml(p.bias_color || '#6b6459') + '"></span>'
            + '<span class="gmap-pop__txt">'
            + '<span class="gmap-pop__title">' + escapeHtml(p.title) + '</span>'
            + '<span class="gmap-pop__src">' + escapeHtml(p.source_name) + ' · ' + escapeHtml(L_READ) + ' →</span>'
            + '</span></a></li>'
        ).join('');
        return '<div class="gmap-pop">'
            + '<div class="gmap-pop__head"><strong>' + escapeHtml(countryLabel(pin.country)) + '</strong>'
            + '<span class="gmap-pop__total">' + fmt(pin.total_at_country || 0) + ' ' + escapeHtml(L_STORIES) + '</span></div>'
            + '<ul class="gmap-pop__list">' + items + '</ul></div>';
    };

    const clusterGroup = L.markerClusterGroup({
        maxClusterRadius: 48,
        showCoverageOnHover: false,
        spiderfyOnMaxZoom: true,
        disableClusteringAtZoom: 6,
        iconCreateFunction: (cluster) => {
            const counts = { left: 0, center: 0, right: 0, unknown: 0 };
            let total = 0;
            cluster.getAllChildMarkers().forEach((m) => {
                const p = m._gmapPin; if (!p) return;
                total += p.total_at_country || 0;
                BIAS_ORDER.forEach((k) => { counts[k] += (p._counts[k] || 0); });
            });
            const html = '<div class="gmap-cluster" role="img" aria-label="' + total + '">'
                + '<span class="gmap-cluster__donut" style="background:' + donutGradient(counts) + '"></span>'
                + '<span class="gmap-cluster__count">' + fmt(total) + '</span></div>';
            return L.divIcon({ html, className: 'gmap-cluster-wrap', iconSize: [48, 48] });
        },
    });

    setStatus(@json(__('Chargement des actualités…')));
    fetch(apiUrl, { headers: { Accept: 'application/json' } })
        .then((r) => r.ok ? r.json() : Promise.reject(r.status))
        .then((data) => {
            const pins = (data && data.pins) || [];
            if (!pins.length) { setStatus(@json(__('Aucune actualité urgente sur la carte.'))); return; }

            pins.forEach((pin) => {
                if (typeof pin.lat !== 'number' || typeof pin.lng !== 'number') return;
                const counts = countsFromPosts(pin.posts);
                const html = '<div class="gmap-pin" style="--glow:' + dominantColor(counts) + '">'
                    + '<span class="gmap-pin__donut" style="background:' + donutGradient(counts) + '"></span>'
                    + '<span class="gmap-pin__count">' + fmt(pin.total_at_country || 0) + '</span></div>';
                const marker = L.marker([pin.lat, pin.lng], {
                    icon: L.divIcon({ html, className: 'gmap-pin-wrap', iconSize: [34, 34] }),
                    title: countryLabel(pin.country) + ' — ' + (pin.total_at_country || 0),
                });
                marker._gmapPin = Object.assign({ _counts: counts }, pin);
                marker.bindPopup(buildPopup(pin), {
                    className: 'gmap-pop-wrap',
                    maxWidth: 320,
                    minWidth: 240,
                    autoPanPadding: [24, 24],
                });
                clusterGroup.addLayer(marker);
            });

            map.addLayer(clusterGroup);
            shell._gmap.clusterGroup = clusterGroup;
            shell._gmap.pins = pins;

            try {
                const b = clusterGroup.getBounds();
                if (b && b.isValid()) map.fitBounds(b.pad(0.25), { maxZoom: 5 });
            } catch (e) { /* keep default view */ }

            setStatus(null);
        })
        .catch(() => setStatus(@json(__('Impossible de charger les actualités. Réessayez.'))));
})();
</script>
