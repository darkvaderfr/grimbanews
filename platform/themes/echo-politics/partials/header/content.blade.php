<div class="echo-home-1-menu header-three">
    <div class="echo-site-main-logo-menu-social">
        <div class="hm-7-container">
            <div class="d-flex align-items-center justify-content-between">
                <div class="logo-header-sidebar">
                    {!! Theme::partial('header.partials.logo', ['onlyLogoLight' => true]) !!}
                </div>

                <div class="echo-home-1-menu d-none d-lg-flex justify-content-center">
                    {!!
                       Menu::renderMenuLocation('main-menu', [
                           'options' => ['class' => 'list-unstyled echo-desktop-menu'],
                           'view' => 'main-menu',
                       ])
                    !!}
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    @if (is_plugin_active('language') && theme_option('language_switcher_enabled', true))
                        {!! Theme::partial('language-switcher') !!}
                    @endif

                    {!! Theme::partial('account') !!}

                    {!! dynamic_sidebar('header_sidebar') !!}
                </div>
            </div>
        </div>
    </div>
</div>
