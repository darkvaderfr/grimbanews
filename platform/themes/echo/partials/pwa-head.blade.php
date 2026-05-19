<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="application-name" content="GrimbaNews">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="GrimbaNews">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="mobile-web-app-capable" content="yes">
{{-- Wave BBBBBB (Vader 2026-05-19) — emit TWO theme-color meta tags
     with `prefers-color-scheme` media queries so the browser chrome
     adapts to the user's OS-level preference WITHOUT requiring our
     own cookie. Previously the value was server-rendered based on
     `grimba_theme` cookie — that worked for returning users but
     first-time dark-OS visitors got the cream chrome until they
     toggled. Two-media-query pattern is the W3C recommendation. --}}
<meta name="theme-color" content="#f6f1e8" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#121007" media="(prefers-color-scheme: dark)">
