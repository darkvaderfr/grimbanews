<div class="container">
    <div class="row echo-row">
        {!! dynamic_sidebar('footer_sidebar') !!}
    </div>
    <div class="echo-footer-copyright-area">
        <div class="copyright-area-inner">
            @if ($logo = theme_option('logo'))
                <div class="footer-logo">
                    <a href="{{ route('public.index') }}">
                        {{ RvMedia::image($logo, theme_option('site_title')) }}
                    </a>
                </div>
            @endif

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
