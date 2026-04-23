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

    {!! Theme::header() !!}
</head>

{!! Theme::partial('body.index') !!}
</html>
