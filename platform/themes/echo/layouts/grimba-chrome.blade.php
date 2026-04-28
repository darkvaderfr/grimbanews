@php
    /**
     * GrimbaNews — shared chrome (tan banner, glass header, chip strip,
     * dark footer) wrapping any view via @yield('content').
     *
     * Used by post detail, listings, comparison, blindspot, sources.
     * The homepage gets layouts/grimba-home.blade.php which renders
     * its own full-bleed body instead of a @yield('content') trunk.
     */

    // Locale override from grimba_lang cookie (wins over Botble's Language plugin).
    $__grimbaLang = (string) request()->cookie('grimba_lang', '');
    if ($__grimbaLang === 'en' || $__grimbaLang === 'fr') {
        app()->setLocale($__grimbaLang);
    }
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ request()->cookie('grimba_theme', 'auto') }}" class="grimba-home-html">
<script>
    (function () {
        const pref = document.cookie.match(/(?:^|; )grimba_theme=([^;]+)/)?.[1] || 'auto';
        const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
        const effective = pref === 'auto' ? (prefersDark ? 'dark' : 'light') : pref;
        document.documentElement.setAttribute('data-bs-theme', effective);
        document.documentElement.setAttribute('data-grimba-theme-pref', pref);
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

    {{-- S118 — favicon stack: SVG for modern browsers, ICO for legacy,
         apple-touch-icon for iOS, theme-color matches the paper bg. --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @include(Theme::getThemeNamespace('partials.pwa-head'))
    @include(Theme::getThemeNamespace('partials.home.ad-styles'))
    @include(Theme::getThemeNamespace('partials.home.contrast-styles'))

    @php($__grimbaOgImage = Theme::get('grimba_og_image') ?: url('/og/home.png'))
    <link rel="alternate" type="application/rss+xml" title="GrimbaNews — Flux RSS" href="{{ url('/feed.xml') }}">
    <meta property="og:image" content="{{ $__grimbaOgImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $__grimbaOgImage }}">
    {!! Theme::header() !!}
</head>
<body class="grimba-home grimba-subpage" {!! Theme::bodyAttributes() !!}>
    {!! apply_filters(THEME_FRONT_BODY, null) !!}
    <a class="grimba-skip-link" href="#grimba-main-content">Aller au contenu principal</a>

    @include(Theme::getThemeNamespace('partials.home.urgency-banner'))
    @include(Theme::getThemeNamespace('partials.home.main-header'))
    @include(Theme::getThemeNamespace('partials.home.topic-chips'))
    @include(Theme::getThemeNamespace('partials.home.translation-note'))
    @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
        'location' => 'grimba_chrome_top',
        'class' => 'grimba-ad-slot--billboard container-xxl',
    ])

    <main class="grimba-sub-main" id="grimba-main-content" tabindex="-1">
        <div class="container-xxl py-4">
            @hasSection('content')
                @yield('content')
            @else
                {!! Theme::content() !!}
            @endif
        </div>
    </main>
    @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
        'location' => 'grimba_chrome_bottom',
        'class' => 'grimba-ad-slot--leaderboard container-xxl',
    ])

    @include(Theme::getThemeNamespace('partials.home.vault-fab'))
    @include(Theme::getThemeNamespace('partials.home.mobile-bottom-nav'))

    {{-- S173/S185 — shared vault toggle handler for all layouts. --}}
    @include(Theme::getThemeNamespace('partials.home.vault-script'))
    @include(Theme::getThemeNamespace('partials.pwa-register'))

    @include(Theme::getThemeNamespace('partials.home.footer-dark'))

    {{-- S145 — site-wide cookie consent overlay (sticky bottom-right
         until accept/reject; gated on admin setting). --}}
    @include(Theme::getThemeNamespace('partials.cookie-consent'))

    <script>'use strict'; window.siteConfig = {};</script>
    {!! Theme::footer() !!}
</body>
</html>
