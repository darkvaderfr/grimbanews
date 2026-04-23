@if ($items = Theme::getSocialLinks())
    @switch($sidebar)
        @case('header_sidebar')
            @include(Theme::getThemeNamespace('widgets.social-links.sidebars.templates.partials.header'))
            @break
        @case('header_top_sidebar')
            @include(Theme::getThemeNamespace('widgets.social-links.sidebars.templates.partials.header-top'))
            @break
        @case('menu_sidebar')
            @include(Theme::getThemeNamespace('widgets.social-links.sidebars.templates.partials.menu'))
            @break
        @case('footer_sidebar')
            @include(Theme::getThemeNamespace('widgets.social-links.sidebars.templates.partials.footer'))
            @break
        @default
            @include(Theme::getThemeNamespace('widgets.social-links.sidebars.templates.partials.header-top'))
    @endswitch
@endif
