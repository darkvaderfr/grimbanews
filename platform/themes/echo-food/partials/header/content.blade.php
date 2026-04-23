{!! Theme::partial('header-top') !!}
<div class="echo-home-2-menu header-two">
    <div class="echo-site-main-logo-menu-social">
        <div class="d-flex align-items-center justify-content-between echo-home-2-menu-full-contetn">
            <div class="logo-header-sidebar d-block d-md-none">
                {!! Theme::partial('header.partials.logo') !!}
            </div>

            <div class="echo-home-1-menu d-none d-lg-flex justify-content-center">
                {!!
                   Menu::renderMenuLocation('main-menu', [
                       'options' => ['class' => 'list-unstyled echo-desktop-menu'],
                       'view' => 'main-menu',
                   ])
                !!}
            </div>

            <div class="d-flex justify-content-between align-items-center" style="padding: 20px 0">
                @if (is_plugin_active('language') && theme_option('language_switcher_enabled', true))
                    {!! Theme::partial('language-switcher') !!}
                @endif

                {!! Theme::partial('account') !!}

                <div class="echo-header-top-menu-bar menu-btn ms-3 d-none">
                    <button data-bb-toggle="menu-sidebar" type="button" aria-label="{{ __('Toggle Sidebar Menu') }}">
                        <svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.526001 0.953461H20V3.11724H0.526001V0.953461ZM7.01733 8.52668H20V10.6905H7.01733V8.52668ZM0.526001 16.0999H20V18.2637H0.526001V16.0999Z" fill="currentColor" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
