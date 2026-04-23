@php
    /**
     * GrimbaNews — Homepage layout (GroundNews-inspired, Steve cinematic glass).
     * Standalone shell: bypasses the stock Echo header/footer chrome.
     */
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ theme_option('theme_style', 'auto') }}" class="grimba-home-html">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=1" name="viewport">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {!! BaseHelper::googleFonts('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600;9..144,700;9..144,800&family=Public+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap') !!}

    {!! Theme::partial('css-variable-declare') !!}
    {!! Theme::header() !!}
</head>
<body class="grimba-home" {!! Theme::bodyAttributes() !!}>
    {!! apply_filters(THEME_FRONT_BODY, null) !!}

    @include(Theme::getThemeNamespace('partials.home.urgency-banner'))
    @include(Theme::getThemeNamespace('partials.home.main-header'))
    @include(Theme::getThemeNamespace('partials.home.topic-chips'))

    <main class="grimba-home-main">
        <div class="container-xxl">
            @include(Theme::getThemeNamespace('partials.home.hero-grid'))
            @include(Theme::getThemeNamespace('partials.home.top-news'))
            @include(Theme::getThemeNamespace('partials.home.section-blocks'))
            @include(Theme::getThemeNamespace('partials.home.latest-plus-topics'))
        </div>
    </main>

    @include(Theme::getThemeNamespace('partials.home.footer-dark'))

    <script>'use strict'; window.siteConfig = {};</script>
    {!! Theme::footer() !!}
</body>
</html>
