<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ theme_option('theme_style', 'auto') }}">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=1" name="viewport" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {!! BaseHelper::googleFonts('https://fonts.googleapis.com/' . sprintf(
        'css2?family=%s:wght@400;500;600;700;800&family=%s:wght@400;500;600;700;800&display=swap',
        urlencode(theme_option('primary_font', 'Inter')),
        urlencode(theme_option('heading_font', 'Bona Nova'))
    )) !!}

    {!! Theme::partial('css-variable-declare') !!}

    {{-- S118 — favicon stack (mirrors grimba-chrome / grimba-home). --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @include(Theme::getThemeNamespace('partials.pwa-head'))

    {!! Theme::header() !!}
</head>

{!! Theme::partial('body.index') !!}
@include(Theme::getThemeNamespace('partials.pwa-register'))
</html>
