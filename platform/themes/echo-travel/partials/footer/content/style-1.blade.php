<div class="footer-top-area">
    <div class="hm-6-container">
        <div class="footer-top-inner">
            @if ($logo = theme_option('logo'))
                <div class="footer-logo">
                    <a href="{{ route('public.index') }}">
                        {{ RvMedia::image($logo, theme_option('site_title')) }}
                    </a>
                </div>
            @endif
            {!! dynamic_sidebar('above_footer_sidebar') !!}

        </div>
    </div>
</div>

<div class="container">
    <div class="row echo-row">
        {!! dynamic_sidebar('footer_sidebar') !!}
    </div>
    <div class="echo-footer-copyright-area">
        <div  class="copyright-area-inner justify-content-{{ (is_plugin_active('language') && count(Language::getSupportedLocales()) > 1) ? 'between' : 'center' }}">
            @if ($siteCopyright = Theme::getSiteCopyright())
                <div class="copyright-content">
                    <div class="title">{!! $siteCopyright !!}</div>
                </div>
            @endif

            <div>
                @if (is_plugin_active('language'))
                    {!! Theme::partial('language-switcher', ['location' => 'footer']) !!}
                @endif
            </div>
        </div>
    </div>
</div>

