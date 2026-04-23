@php
    $backgroundColor = $shortcode->background_color;
    $backgroundImage = $shortcode->background_image ? RvMedia::url($shortcode->background_image) : null;
@endphp

<div class="container">
    <div class="echo-software-download-full-content-bg"
        @style([
            "background-color: $backgroundColor !important;" => $backgroundColor,
            "--background-image: url('$backgroundImage')" => $backgroundImage
        ])>
        @if ($image = $shortcode->image)
            <div class="echo-software-download-img-content">
                {{ RvMedia::image($image, $shortcode->title) }}
            </div>
        @endif
        <div class="row">
            <div class="col-xl-12">
                <div class="echo-software-download-full-content">
                    <div class="echo-software-download-text-content">
                        @if ($title = $shortcode->title)
                            <div class="echo-software-download-title">
                                <h2 class="text-capitalize">{!! BaseHelper::clean($title) !!}</h2>
                            </div>
                        @endif

                        @if ($subtitle = $shortcode->subtitle)
                            <div class="echo-software-download-sub-title">
                                <p>{!! BaseHelper::clean($subtitle) !!}</p>
                            </div>
                        @endif

                        <div class="echo-software-download-place">
                            @if (($platformAppleLabel = $shortcode->platform_apple_label) && ($platformAppleUrl = $shortcode->platform_apple_url))
                                <a href="{{ $platformAppleUrl }}" class="echo-py-hover-white">
                                    @if ($appleIcon = $shortcode->platform_apple_icon)
                                        {!! BaseHelper::renderIcon($appleIcon) !!}
                                    @endif
                                    {!! BaseHelper::clean($platformAppleLabel) !!}
                                </a>
                            @endif
                            @if (($platformGoogleLabel = $shortcode->platform_google_label) && ($platformGoogleUrl = $shortcode->platform_google_url))
                                <a href="{{ $platformGoogleUrl }}" class="echo-py-hover-white">
                                    @if ($googleIcon = $shortcode->platform_google_icon)
                                        {!! BaseHelper::renderIcon($googleIcon) !!}
                                    @endif
                                    {!! BaseHelper::clean($platformGoogleLabel) !!}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
