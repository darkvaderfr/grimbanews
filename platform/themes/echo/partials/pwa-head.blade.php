@php
    $__grimbaPwaTheme = (string) request()->cookie('grimba_theme', 'light');
    if (! in_array($__grimbaPwaTheme, ['light', 'dark'], true)) {
        $__grimbaPwaTheme = 'light';
    }
    $__grimbaThemeColor = $__grimbaPwaTheme === 'dark' ? '#121007' : '#f6f1e8';
@endphp
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="application-name" content="GrimbaNews">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="GrimbaNews">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="{{ $__grimbaThemeColor }}">
