@php
    $enableSearchButton = Arr::get($config, 'enable_search_button', true);
    $enableDarkLightButton = Arr::get($config, 'enable_dark_light_switcher_button', true);
    $enableToggleSideMenuButton = (app()->has('themes.menu_sidebar') && app('themes.menu_sidebar'))  && Arr::get($config, 'enable_toggle_side_menu_button', true);
    $enableNewsletterButton = Arr::get($config, 'enable_newsletter_button', true);
    $alignment = Arr::get($config, 'alignment', 'center');
@endphp

@if ($enableDarkLightButton || $enableSearchButton || $enableToggleSideMenuButton || $enableNewsletterButton)
    <div class="d-flex action-buttons-content gap-4 justify-content-{{ $alignment }}">
        @include(Theme::getThemeNamespace('widgets.action-buttons.templates.partials.newsletter'))

        <div class="action-buttons-inner d-flex align-items-center">
            @if ($enableDarkLightButton)
                <div class="position-relative header-action">
                    <div class="echo-header-top-social-media">
                        <div class="rts-darkmode">
                            <a id="rts-data-toggle" class="rts-dark-light">
                                <i class="rts-go-dark fal fa-moon"></i>
                                <i class="rts-go-light far fa-sun"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($enableSearchButton && is_plugin_active('blog'))
                <div class="position-relative header-action">
                    <div class="echo-header-top-social-media">
                        <a id="search" class="echo-header-top-search-btn search-icon action-item icon">
                            <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9.11544 16.961C13.3484 16.961 16.7798 13.5296 16.7798 9.29665C16.7798 5.06373 13.3484 1.63226 9.11544 1.63226C4.88251 1.63226 1.45105 5.06373 1.45105 9.29665C1.45105 13.5296 4.88251 16.961 9.11544 16.961Z"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"/>
                                <path d="M14.4461 15.0254L17.451 18.0225" stroke="currentColor" stroke-width="1.5"
                                      stroke-linecap="round" stroke-linejoin="round"/>
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

            @if ($enableToggleSideMenuButton)
                @include(Theme::getThemeNamespace('widgets.action-buttons.templates.partials.toggle-sidebar-menu-button'))
            @endif
        </div>
    </div>
@endif
