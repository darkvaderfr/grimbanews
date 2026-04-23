@if ($items = Theme::getSocialLinks())
    @switch($sidebar)
        @case('header_sidebar')
            @include(Theme::getThemeNamespace('widgets.social-links.templates.partials.sidebars.header'))
            @break
        @case('header_top_sidebar')
            @include(Theme::getThemeNamespace('widgets.social-links.templates.partials.sidebars.header-top'))
            @break
        @case('menu_sidebar')
            @include(Theme::getThemeNamespace('widgets.social-links.templates.partials.sidebars.menu'))
            @break
        @default
            @include(Theme::getThemeNamespace('widgets.social-links.templates.partials.sidebars.header-top'))
    @endswitch
@endif
