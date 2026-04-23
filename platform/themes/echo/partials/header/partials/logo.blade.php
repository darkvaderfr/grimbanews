@php
    $onlyLogoLight ??= false;
    $default ??= true;
    $wrapperClass ??= null;

    $logo = theme_option('logo');
    $logoDark = theme_option('logo_dark');

    $height = theme_option('logo_height', 60);
    $attributes = [
        'style' => sprintf('height: %s !important', is_numeric($height) ? "{$height}px" : $height),
        'loading' => false,
    ];
@endphp

@if($logo || $logoDark)
    <div @class(['echo-site-logo', $wrapperClass])>
        @if ($onlyLogoLight)
            <a href="{{ route('public.index') }}">
                {{ RvMedia::image($logo, theme_option('site_title'), attributes: [...$attributes]) }}
            </a>
        @else
            @if ($logo)
                <a class="logo-light" href="{{ route('public.index') }}">
                    {{ RvMedia::image($logo, theme_option('site_title'), attributes: [...$attributes]) }}
                </a>
            @endif

            @if ($logoDark)
                <a class="logo-dark d-none" href="{{ route('public.index') }}">
                    {{ RvMedia::image($logoDark, theme_option('site_title'), attributes: [...$attributes]) }}
                </a>
            @endif
        @endif
    </div>
@endif
