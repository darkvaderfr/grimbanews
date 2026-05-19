@php
    /**
     * GrimbaNews — shared chrome (tan banner, glass header, chip strip,
     * dark footer) wrapping any view via @yield('content').
     *
     * Used by post detail, listings, comparison, blindspot, sources.
     * The homepage gets layouts/grimba-home.blade.php which renders
     * its own full-bleed body instead of a @yield('content') trunk.
     */

    // Locale override — S-LANG-06 (Vader 2026-05-16) adds query-param
    // support so search engines can crawl explicit per-locale URLs.
    // Precedence: ?lang=… (request-scoped, lets bots index FR + EN) >
    // cookie (sticky for human visitors) > Botble's default.
    $__grimbaQueryLang = (string) request()->query('lang', '');
    $__grimbaCookieLang = (string) request()->cookie('grimba_lang', '');
    $__grimbaLang = '';
    if ($__grimbaQueryLang === 'fr' || $__grimbaQueryLang === 'en') {
        $__grimbaLang = $__grimbaQueryLang;
    } elseif ($__grimbaCookieLang === 'fr' || $__grimbaCookieLang === 'en') {
        $__grimbaLang = $__grimbaCookieLang;
    }
    if ($__grimbaLang !== '') {
        app()->setLocale($__grimbaLang);
    }

    // hreflang URLs — same path, ?lang param swapped per locale.
    $__grimbaHreflang = function (?string $locale = null): string {
        $params = request()->query();
        if ($locale === null) {
            unset($params['lang']);
        } else {
            $params['lang'] = $locale;
        }
        $qs = $params ? '?' . http_build_query($params) : '';
        return url(request()->path()) . $qs;
    };
@endphp
<!doctype html>
@php
    // Mirror grimba-home: explicit light/dark only. data-theme="light"
    // stays hard-set to keep stock Echo's [data-theme="dark"] body-bg
    // rule from leaking a near-black canvas onto our subpages.
    $__grimbaPref = (string) request()->cookie('grimba_theme', 'light');
    if (! in_array($__grimbaPref, ['light', 'dark'], true)) {
        $__grimbaPref = 'light';
    }
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ $__grimbaPref }}" data-theme="light" data-grimba-theme-pref="{{ $__grimbaPref }}" class="grimba-home-html">
<script>
    (function () {
        const m = document.cookie.match(/(?:^|; )grimba_theme=([^;]+)/);
        let pref = 'light';
        if (m) {
            try { pref = decodeURIComponent(m[1]); } catch (_) { pref = m[1]; }
        }
        if (pref !== 'light' && pref !== 'dark') pref = 'light';
        document.documentElement.setAttribute('data-bs-theme', pref);
        document.documentElement.setAttribute('data-grimba-theme-pref', pref);

        // S344 — edition-aware bias color flip (mirrors grimba-home).
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

    {{-- S-MODE-09 (Vader 2026-05-18) — FOUC guard MUST run before
         any body element parses so dark-mode users never see a
         white flash on nav. Inline script, no network round-trip. --}}
    @include(Theme::getThemeNamespace('partials.theme-fouc-guard'))

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

    {{-- Wave AAAAAA — fold the OG-image var-set AND the SeoHelper
         push into one @php block. The Blade `@php(expr)` shorthand
         miscompiles when the expression contains an `=` assignment
         + the result is used later in the file — we fix that by
         switching to the explicit @php @endphp block form. --}}
    @php
        $__grimbaOgImage = Theme::get('grimba_og_image') ?: url('/og/home.png');
        \Botble\SeoHelper\Facades\SeoHelper::setImage($__grimbaOgImage);
    @endphp
    {{-- S-LANG-06 — explicit per-locale URLs so search engines index
         FR and EN versions distinctly. x-default falls back to FR
         (GrimbaNews's primary locale per editorial policy). --}}
    <link rel="alternate" hreflang="fr"        href="{{ $__grimbaHreflang('fr') }}">
    <link rel="alternate" hreflang="en"        href="{{ $__grimbaHreflang('en') }}">
    <link rel="alternate" hreflang="x-default" href="{{ $__grimbaHreflang('fr') }}">
    {{-- Botble's SeoHelper emits the canonical further down in
         Theme::header() — emitting one here would create a duplicate. --}}
    <link rel="alternate" type="application/rss+xml" title="{{ __('GrimbaNews — Flux RSS') }}" href="{{ url('/feed.xml') }}">
    <link rel="alternate" type="application/rss+xml" title="{{ __('GrimbaNews — Breaking news') }}" href="{{ url('/feed.breaking.xml') }}">
    <link rel="alternate" type="application/rss+xml" title="{{ __('GrimbaNews — Latest') }}" href="{{ url('/feed.latest.xml') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $__grimbaOgImage }}">
    @if($jsonLd = Theme::get('grimbaJsonLd'))
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif
    {!! Theme::header() !!}
    @include(Theme::getThemeNamespace('partials.ads.head'))
    @include(Theme::getThemeNamespace('partials.home.contrast-styles'))
</head>
{{-- Wave UUUU (Vader 2026-05-18) — merge layout-specific class into
     Theme::addBodyAttributes() so the renderer emits a single
     class= attribute (browsers silently drop the second one). --}}
@php(Theme::addBodyAttributes(['class' => trim('grimba-home grimba-subpage ' . (Theme::getBodyAttribute('class') ?? ''))]))
<body {!! Theme::bodyAttributes() !!}>
    @include(Theme::getThemeNamespace('partials.home.front-body-hooks'))
    <a class="grimba-skip-link" href="#grimba-main-content">{{ __('Aller au contenu principal') }}</a>
    @include(Theme::getThemeNamespace('partials.focus-manager'))

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
    @include(Theme::getThemeNamespace('partials.command-palette'))

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
