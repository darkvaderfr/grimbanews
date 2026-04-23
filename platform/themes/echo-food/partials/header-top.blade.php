<div class="echo-header-top-home-2">
    <div class="echo-full-header-top">
        <div class="home-2-row">
            <div class="home-2-card-etc">
                <div class="echo-header-top-subs-social-menu-home-2">
                    {!! dynamic_sidebar('header_top_left_sidebar') !!}
                </div>
            </div>
            <div class="home-2-logo">
                <div class="echo-header-home-2-logo">
                    @if ($logo = theme_option('logo_dark'))
                        <a href="{{ route("public.index") }}">
                            {{ RvMedia::image($logo, theme_option('site_title')) }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="home-2-social">
                {!! dynamic_sidebar('header_top_right_sidebar') !!}
            </div>
        </div>
    </div>
</div>
