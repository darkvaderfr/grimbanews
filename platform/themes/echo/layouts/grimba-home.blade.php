@php
    /**
     * GrimbaNews — Homepage layout (cinematic glass).
     * Standalone shell: bypasses the stock Echo header/footer chrome.
     */

    // Locale override — S-LANG-06: query param wins, then cookie.
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
    // Light/dark only. "auto" previously followed system preference and
    // caused inconsistent review sessions; stale/invalid cookies fall
    // back to light.
    $__grimbaPref = (string) request()->cookie('grimba_theme', 'light');
    if (! in_array($__grimbaPref, ['light', 'dark'], true)) {
        $__grimbaPref = 'light';
    }
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ $__grimbaPref }}" data-theme="light" data-grimba-theme-pref="{{ $__grimbaPref }}" class="grimba-home-html">
<script>
    // Resolve the saved theme before paint. We keep
    // data-theme="light" hard-set so stock Echo's data-theme="dark"
    // CSS never bleeds in; our own dark palette keys off data-bs-theme.
    (function () {
        const m = document.cookie.match(/(?:^|; )grimba_theme=([^;]+)/);
        let pref = 'light';
        if (m) {
            try { pref = decodeURIComponent(m[1]); } catch (_) { pref = m[1]; }
        }
        if (pref !== 'light' && pref !== 'dark') pref = 'light';
        document.documentElement.setAttribute('data-bs-theme', pref);
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

    {{-- S-MODE-09 (Vader 2026-05-18) — FOUC guard MUST run before
         any body element parses so dark-mode users never see a
         white flash on nav. Inline script, no network round-trip. --}}
    @include(Theme::getThemeNamespace('partials.theme-fouc-guard'))

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

    {{-- S-LANG-06 — explicit per-locale URLs so search engines index
         FR and EN versions distinctly. x-default falls back to FR. --}}
    <link rel="alternate" hreflang="fr"        href="{{ $__grimbaHreflang('fr') }}">
    <link rel="alternate" hreflang="en"        href="{{ $__grimbaHreflang('en') }}">
    <link rel="alternate" hreflang="x-default" href="{{ $__grimbaHreflang('fr') }}">
    {{-- Botble's SeoHelper emits the canonical further down in
         Theme::header() — emitting one here would create a duplicate. --}}
    <link rel="alternate" type="application/rss+xml" title="{{ __('GrimbaNews — Flux RSS') }}" href="{{ url('/feed.xml') }}">
    <link rel="alternate" type="application/rss+xml" title="{{ __('GrimbaNews — Breaking news') }}" href="{{ url('/feed.breaking.xml') }}">
    <link rel="alternate" type="application/rss+xml" title="{{ __('GrimbaNews — Latest') }}" href="{{ url('/feed.latest.xml') }}">
    {{-- Wave AAAAAA — push OG image into Botble SeoHelper before Theme::header() so the auto-emitted og:image points at our 1200×630 PNG, not the default SVG.
         Wave FFFFFF — drop the manual og:image:width/height pair here; Theme::header()
         emits its own paired dimensions tags adjacent to its og:image, and the
         orphan pair was creating an OG-spec adjacency mismatch. --}}
    @php
        \Botble\SeoHelper\Facades\SeoHelper::setImage(url('/og/home.png'));
        \Botble\SeoHelper\Facades\SeoHelper::openGraph()->addProperty('image:width', '1200');
        \Botble\SeoHelper\Facades\SeoHelper::openGraph()->addProperty('image:height', '630');
        // Wave HHHHHH — Botble's blog plugin sets og:type=article on the
        // home listing page (since it's the blog index). The OG spec
        // says the homepage type should be 'website', not 'article'.
        // Override explicitly here.
        \Botble\SeoHelper\Facades\SeoHelper::openGraph()->setType('website');
        // Wave IIIIII — emit og:locale + alternate so OG crawlers know
        // this page exists in FR and EN. Format is `<lang>_<region>`.
        $__grimbaCurLocale = app()->getLocale();
        $__grimbaOgLocale = $__grimbaCurLocale === 'en' ? 'en_US' : 'fr_FR';
        $__grimbaOgLocaleAlt = $__grimbaCurLocale === 'en' ? 'fr_FR' : 'en_US';
        \Botble\SeoHelper\Facades\SeoHelper::openGraph()->addProperty('locale', $__grimbaOgLocale);
        \Botble\SeoHelper\Facades\SeoHelper::openGraph()->addProperty('locale:alternate', $__grimbaOgLocaleAlt);
        // Wave GGGGGG — only set card type; SeoHelper does NOT auto-derive
        // twitter:image, so we emit it manually below to avoid the
        // singleton-accumulation pitfall of SeoHelper::twitter()->addImage().
        \Botble\SeoHelper\Facades\SeoHelper::twitter()->setType('summary_large_image');
    @endphp
    {!! Theme::header() !!}
    <meta name="twitter:image" content="{{ url('/og/home.png') }}">
    @include(Theme::getThemeNamespace('partials.ads.head'))
    @include(Theme::getThemeNamespace('partials.home.contrast-styles'))
    {{-- Wave KKKKK — home JSON-LD. Build in PHP so schema-org `at`-prefixed keys don't trip Blade directives. --}}
    @php
        $__grimbaHomeJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => url('/#website'),
                    'url' => url('/'),
                    'name' => 'GrimbaNews',
                    'description' => __("L'actualité internationale lue depuis chaque camp."),
                    'inLanguage' => app()->getLocale(),
                    'publisher' => ['@id' => url('/#organization')],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => url('/search?q={search_term_string}'),
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@type' => 'NewsMediaOrganization',
                    '@id' => url('/#organization'),
                    'name' => 'GrimbaNews',
                    'url' => url('/'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => url('/img/grimbanews-logo.png'),
                        'width' => 512,
                        'height' => 512,
                    ],
                    'sameAs' => [],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $__grimbaHomeJsonLd !!}</script>
</head>
{{-- Wave UUUU (Vader 2026-05-18) — merge layout-specific class into
     Theme::addBodyAttributes() so the renderer emits a single
     class= attribute (browsers silently drop the second one). --}}
@php(Theme::addBodyAttributes(['class' => trim('grimba-home ' . (Theme::getBodyAttribute('class') ?? ''))]))
<body {!! Theme::bodyAttributes() !!}>
    @include(Theme::getThemeNamespace('partials.home.front-body-hooks'))
    <a class="grimba-skip-link" href="#grimba-main-content">{{ __('Aller au contenu principal') }}</a>
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
            @include(Theme::getThemeNamespace('partials.home.regional-mix'))
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

            {{-- S-LSAT-05b (Vader 2026-05-18) — home tail expander.
                 Sits below the last rail so the reader sees it once
                 they've scanned the full page. 48h window so it
                 represents recent-enough coverage without padding. --}}
            @include(Theme::getThemeNamespace('partials.lang.tail-expander'), ['surface' => 'home', 'hours' => 48])
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
