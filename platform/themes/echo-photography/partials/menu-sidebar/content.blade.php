<div class="inner">
    <button class="close-icon-menu" type="button">
        <span class="sr-only">{{ __('Close') }}</span>
        <i class="far fa-times"></i>
    </button>
    @if ($logo = theme_option('logo_dark'))
        <div class="thumbnail">
            {{ RvMedia::image($logo, theme_option('site_title')) }}
        </div>
    @endif
</div>

<div class="mobile-menu d-block d-lg-none">
    <nav class="nav-main mainmenu-nav mt--30 d-flex flex-column justify-content-center">
        {!!
           Menu::renderMenuLocation('main-menu', [
               'options' => ['class' => 'mainmenu', 'id' => 'mobile-menu-active'],
               'view' => 'main-menu-mobile',
           ])
        !!}
    </nav>
</div>

<div class="mobile-menu d-block">
    <div class="inner-main-wrapper-desk">
        <div class="inner-content">
            {!! tap(dynamic_sidebar('menu_sidebar'), fn ($menuSidebar) => app()->instance('themes.menu_sidebar', $menuSidebar)) !!}
        </div>
    </div>

    @if (is_plugin_active('language') && theme_option('language_switcher_enabled', true))
        {!! Theme::partial('language-switcher', ['location' => 'sidebar']) !!}
    @endif
</div>
