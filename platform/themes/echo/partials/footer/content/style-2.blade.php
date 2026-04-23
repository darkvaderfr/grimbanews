<div class="container">
    <div class="footer-top">
        {!! dynamic_sidebar('above_footer_sidebar') !!}
    </div>
    <div class="footer-main">
        <div class="footer-main-inner">
            @if ($logo = theme_option('logo_dark'))
                <div class="logo-area">
                    <a href="{{ route('public.index') }}">
                        {{ RvMedia::image($logo, theme_option('site_title')) }}
                    </a>
                </div>
            @endif
            {!! dynamic_sidebar('footer_sidebar') !!}
        </div>
    </div>

    @if ($siteCopyright = Theme::getSiteCopyright())
        <div class="footer-copyright-area">
            <div class="copyright-area-inner">
                <div class="title">{!! $siteCopyright !!}</div>
            </div>
        </div>
    @endif
</div>
