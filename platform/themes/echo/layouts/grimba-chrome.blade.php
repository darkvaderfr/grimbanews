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

    {!! BaseHelper::googleFonts('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700;9..144,800&family=Public+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap') !!}

    {!! Theme::partial('css-variable-declare') !!}

    {{-- S118 — favicon stack: SVG for modern browsers, ICO for legacy,
         apple-touch-icon for iOS, theme-color matches the paper bg. --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#f6f1e8">

    <link rel="alternate" type="application/rss+xml" title="GrimbaNews — Flux RSS" href="{{ url('/feed.xml') }}">
    <meta property="og:image" content="{{ url('/og/home.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ url('/og/home.png') }}">
    {!! Theme::header() !!}
</head>
<body class="grimba-home grimba-subpage" {!! Theme::bodyAttributes() !!}>
    {!! apply_filters(THEME_FRONT_BODY, null) !!}

    @include(Theme::getThemeNamespace('partials.home.urgency-banner'))
    @include(Theme::getThemeNamespace('partials.home.main-header'))
    @include(Theme::getThemeNamespace('partials.home.topic-chips'))

    <main class="grimba-sub-main">
        <div class="container-xxl py-4">
            @hasSection('content')
                @yield('content')
            @else
                {!! Theme::content() !!}
            @endif
        </div>
    </main>

    @include(Theme::getThemeNamespace('partials.home.footer-dark'))

    <script>'use strict'; window.siteConfig = {};</script>
    {!! Theme::footer() !!}
</body>
</html>
