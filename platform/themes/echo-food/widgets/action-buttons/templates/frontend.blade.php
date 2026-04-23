@php
    $enableSearchButton = Arr::get($config, 'enable_search_button', true);
    $enableDarkLightButton = Arr::get($config, 'enable_dark_light_switcher_button', true);
    $enableToggleSideMenuButton = (app()->has('themes.menu_sidebar') && app('themes.menu_sidebar')) && Arr::get($config, 'enable_toggle_side_menu_button', true);
    $enableNewsletterButton = Arr::get($config, 'enable_newsletter_button', true);
    $alignment = Arr::get($config, 'alignment', 'center');
@endphp

@if ($enableDarkLightButton || $enableSearchButton || $enableToggleSideMenuButton || $enableNewsletterButton)
    <div class="d-flex action-buttons-content justify-content-{{ $alignment }}">
        @include(Theme::getThemeNamespace('widgets.action-buttons.templates.partials.newsletter'))

        @if ($enableToggleSideMenuButton || $enableSearchButton || $enableDarkLightButton)
            <div class="action-buttons-inner d-flex align-items-center">
                @if ($enableToggleSideMenuButton)
                    @include(Theme::getThemeNamespace('widgets.action-buttons.templates.partials.toggle-sidebar-menu-button'))
                @endif

                @if ($enableSearchButton && is_plugin_active('blog'))
                    <div class="position-relative header-action">
                        <div class="echo-header-top-social-media">
                            <a id="search" class="echo-header-top-search-btn search-icon action-item icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                                    <path d="M19.729 17.9888L25 23.2586L23.2586 25L17.9888 19.729C16.028 21.3009 13.5892 22.1558 11.0761 22.1522C4.9621 22.1522 0 17.1901 0 11.0761C0 4.9621 4.9621 0 11.0761 0C17.1901 0 22.1522 4.9621 22.1522 11.0761C22.1558 13.5892 21.3009 16.028 19.729 17.9888ZM17.2603 17.0757C18.8221 15.4695 19.6944 13.3165 19.6909 11.0761C19.6909 6.31584 15.8351 2.46136 11.0761 2.46136C6.31584 2.46136 2.46136 6.31584 2.46136 11.0761C2.46136 15.8351 6.31584 19.6909 11.0761 19.6909C13.3165 19.6944 15.4695 18.8221 17.0757 17.2603L17.2603 17.0757Z" fill="currentColor"></path>
                                </svg>
                            </a>

                            <div class="search-input-area">
                                <div class="container">
                                    <form action="{{ route('public.search') }}" class="search-input-inner">
                                        <div class="input-div">
                                            <input id="searchInput1" class="search-input" name="q" type="text" placeholder="{{ __('Search by keyword') }}">
                                        </div>
                                        <div class="search-close-icon"><i class="fa-regular fa-xmark-large rt-xmark"></i></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($enableDarkLightButton)
                    <div class="position-relative header-action">
                        <div class="home-2-social">
                            <div class="rts-darkmode">
                                <a id="rts-data-toggle" class="rts-dark-light">
                                    <i class="rts-go-dark fal fa-moon"></i>
                                    <i class="rts-go-light far fa-sun"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
