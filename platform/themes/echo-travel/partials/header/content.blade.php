<div class="echo-home-1-menu header-six">
    <div class="echo-site-main-logo-menu-social">
        <div class="header-inner">
            {!! Theme::partial('header.partials.logo', ['onlyLogoLight' => true, 'wrapperClass' => 'logo2 d-block d-lg-none d-md-block']) !!}

            <div class="echo-header-top-menu-bar menu-btn btn-1">
                <button data-bb-toggle="menu-sidebar" type="button" aria-label="{{ __('Toggle Sidebar Menu') }}">
                    <span class="sr-only">{{ __('Toggle Sidebar Menu') }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="49" height="15" viewBox="0 0 49 15" fill="none">
                        <line x1="0.249023" y1="1.41577" x2="48.6221" y2="1.41577" stroke="white" stroke-width="2"></line>
                        <line x1="0.249023" y1="13.9224" x2="48.6221" y2="13.9224" stroke="white" stroke-width="2"></line>
                    </svg>
                </button>
            </div>

            {!! Theme::partial('header.partials.logo', ['onlyLogoLight' => true, 'wrapperClass' => 'echo-site-logo d-none d-lg-block d-md-none']) !!}

            <div class="home-2-card-etc">
                <div class="card-inner">
                    @if (is_plugin_active('language') && theme_option('language_switcher_enabled', true))
                        {!! Theme::partial('language-switcher') !!}
                    @endif
                    {!! Theme::partial('account') !!}
                    {!! dynamic_sidebar('header_sidebar') !!}
                    <div class="echo-header-top-menu-bar menu-btn btn2 d-block d-lg-none d-md-block">
                        <button data-bb-toggle="menu-sidebar" type="button" aria-label="{{ __('Toggle Sidebar Menu') }}">
                            <span class="sr-only">{{ __('Toggle Sidebar Menu') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="49" height="15" viewBox="0 0 49 15" fill="none">
                                <line x1="0.249023" y1="1.41577" x2="48.6221" y2="1.41577" stroke="white" stroke-width="2"></line>
                                <line x1="0.249023" y1="13.9224" x2="48.6221" y2="13.9224" stroke="white" stroke-width="2"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
