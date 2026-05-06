@php
    /**
     * GrimbaNews — Homepage layout (GroundNews-inspired, Steve cinematic glass).
     * Standalone shell: bypasses the stock Echo header/footer chrome.
     */

    // Locale override from grimba_lang cookie (wins over Botble's Language plugin).
    $__grimbaLang = (string) request()->cookie('grimba_lang', '');
    if ($__grimbaLang === 'en' || $__grimbaLang === 'fr') {
        app()->setLocale($__grimbaLang);
    }
@endphp
<!doctype html>
@php
    // Auto/light/dark — read user's saved preference (default auto). SSR
    // can't know `prefers-color-scheme`, so when the user is in auto we
    // paint light first and let the boot script swap to dark before the
    // page becomes visible (no flash for explicit-dark users).
    $__grimbaPref = (string) request()->cookie('grimba_theme', 'auto');
    if (! in_array($__grimbaPref, ['light', 'dark', 'auto'], true)) {
        $__grimbaPref = 'auto';
    }
    $__grimbaInitialTheme = $__grimbaPref === 'dark' ? 'dark' : 'light';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ $__grimbaInitialTheme }}" data-theme="light" data-grimba-theme-pref="{{ $__grimbaPref }}" class="grimba-home-html">
<script>
    // Resolve the saved theme preference before paint. We keep
    // data-theme="light" hard-set so stock Echo's data-theme="dark"
    // CSS never bleeds in; our own dark palette keys off data-bs-theme.
    (function () {
        const m = document.cookie.match(/(?:^|; )grimba_theme=([^;]+)/);
        let pref = 'auto';
        if (m) {
            try { pref = decodeURIComponent(m[1]); } catch (_) { pref = m[1]; }
        }
        if (pref !== 'light' && pref !== 'dark' && pref !== 'auto') pref = 'auto';
        let effective = pref;
        if (pref === 'auto') {
            effective = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-bs-theme', effective);
        document.documentElement.setAttribute('data-grimba-theme-pref', pref);

        // S344 — edition-aware bias color flip. Default convention is
        // FR (blue=left, red=right) on every edition. Reader can opt
        // into US convention (red=left, blue=right) via ?fr_convention=0
        // — choice persists in localStorage so subsequent paints stay
        // consistent. Doc + toggle UI live on /comprendre-le-barometre.
        try {
            const params = new URLSearchParams(window.location.search);
            if (params.has('fr_convention')) {
                const v = params.get('fr_convention');
                if (v === '0' || v === 'us' || v === 'no') {
                    window.localStorage.setItem('grimba_bias_convention', 'us');
                } else if (v === '1' || v === 'fr' || v === 'yes') {
                    window.localStorage.removeItem('grimba_bias_convention');
                }
            }
            const conv = window.localStorage.getItem('grimba_bias_convention');
            if (conv === 'us') {
                document.documentElement.setAttribute('data-bias-convention', 'us');
            }
        } catch (_) {}
    })();
</script>
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=1" name="viewport">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include(Theme::getThemeNamespace('partials.font-preloads'))
    {!! BaseHelper::googleFonts('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700;9..144,800&family=Public+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap') !!}

    {!! Theme::partial('css-variable-declare') !!}

    {{-- S118 — favicon stack (mirrors grimba-chrome). --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @include(Theme::getThemeNamespace('partials.pwa-head'))
    @include(Theme::getThemeNamespace('partials.home.ad-styles'))

    <link rel="alternate" type="application/rss+xml" title="GrimbaNews — Flux RSS" href="{{ url('/feed.xml') }}">
    <meta property="og:image" content="{{ url('/og/home.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ url('/og/home.png') }}">
    {!! Theme::header() !!}
    @include(Theme::getThemeNamespace('partials.home.contrast-styles'))
</head>
<body class="grimba-home" {!! Theme::bodyAttributes() !!}>
    @include(Theme::getThemeNamespace('partials.home.front-body-hooks'))
    <a class="grimba-skip-link" href="#grimba-main-content">Aller au contenu principal</a>
    @include(Theme::getThemeNamespace('partials.focus-manager'))

    @include(Theme::getThemeNamespace('partials.home.urgency-banner'))
    @include(Theme::getThemeNamespace('partials.home.main-header'))
    @include(Theme::getThemeNamespace('partials.home.topic-chips'))
    @include(Theme::getThemeNamespace('partials.home.translation-note'))
    @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
        'location' => 'grimba_home_top',
        'class' => 'grimba-ad-slot--billboard container-xxl',
    ])

    <main class="grimba-home-main" id="grimba-main-content" tabindex="-1">
        <div class="container-xxl">
            {{-- S154 — multi-bias hero rail. Shows only when ≥1 cluster
                 has cross-spectrum coverage; hides itself otherwise. --}}
            @include(Theme::getThemeNamespace('partials.home.daily-briefing'))
            @include(Theme::getThemeNamespace('partials.home.all-sides-rail'))
            @include(Theme::getThemeNamespace('partials.home.hero-grid'))
            @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                'location' => 'grimba_home_mid',
                'class' => 'grimba-ad-slot--leaderboard my-4',
            ])
            @include(Theme::getThemeNamespace('partials.home.most-read-by-bias'))
            @include(Theme::getThemeNamespace('partials.home.top-news'))
            @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                'location' => 'grimba_home_native',
                'class' => 'grimba-ad-slot--native my-4',
            ])
            @include(Theme::getThemeNamespace('partials.home.section-blocks'))
            @include(Theme::getThemeNamespace('partials.home.latest-plus-topics'))
        </div>
    </main>

    @include(Theme::getThemeNamespace('partials.home.vault-fab'))
    @include(Theme::getThemeNamespace('partials.home.mobile-bottom-nav'))
    @include(Theme::getThemeNamespace('partials.command-palette'))

    @include(Theme::getThemeNamespace('partials.home.footer-dark'))

    {{-- S145 — cookie consent overlay (mirrors grimba-chrome). --}}
    @include(Theme::getThemeNamespace('partials.cookie-consent'))
    @include(Theme::getThemeNamespace('partials.home.vault-script'))
    @include(Theme::getThemeNamespace('partials.pwa-register'))

    <script>'use strict'; window.siteConfig = {};</script>
    {!! Theme::footer() !!}
</body>
</html>
