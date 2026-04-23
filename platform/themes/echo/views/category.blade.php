@php
    Theme::set('pageTitle', $category->name);
    Theme::layout('grimba-chrome');
@endphp

@include(Theme::getThemeNamespace('views.loop'))
