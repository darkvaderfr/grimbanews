<div class="container">
    <div class="row echo-row">
        {!! dynamic_sidebar('footer_sidebar') !!}
    </div>
    <div class="echo-footer-copyright-area">
        <div class="copyright-area-inner">

        <div class="footer-logo">
            @if ($logo = theme_option('logo'))
                <a class="logo-light" href="{{ route('public.index') }}">
                    {{ RvMedia::image($logo, theme_option('site_title')) }}
                </a>
            @endif

            @if ($logoDark = theme_option('logo_dark'))
                <a class="logo-dark" href="{{ route('public.index') }}">
                    {{ RvMedia::image($logoDark, theme_option('site_title')) }}
                </a>
            @endif
        </div>

            @if ($siteCopyright = Theme::getSiteCopyright())
                <div class="copyright-content">
                    <p class="title">{!! $siteCopyright !!}</p>
                </div>
            @endif

            @if (is_plugin_active('language'))
                {!! Theme::partial('language-switcher', ['location' => 'footer']) !!}
            @endif
        </div>
    </div>
</div>
