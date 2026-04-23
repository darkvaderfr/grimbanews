@php
    $hasLanguageSwitcher = is_plugin_active('language') && theme_option('language_switcher_enabled', true);
    $menuSidebar = app()->has('themes.menu_sidebar') ? app('themes.menu_sidebar') : dynamic_sidebar('menu_sidebar');
@endphp

@if ($menuSidebar || $hasLanguageSwitcher)
    <div class="mobile-menu d-block">
        <div class="inner-main-wrapper-desk">
            <div class="inner-content">
                {!! $menuSidebar !!}
            </div>
        </div>

        @if ($hasLanguageSwitcher)
            {!! Theme::partial('language-switcher', ['location' => 'sidebar']) !!}
        @endif
    </div>
@endif
