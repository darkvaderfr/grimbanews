@php
    $backgroundImage = $shortcode->background_image ? RvMedia::url($shortcode->background_image) : null;
    $backgroundColor = $shortcode->background_color;
    $textColor = $shortcode->text_color ?: '#ffffff';
@endphp

<section class="hero-home-five" @style([
    "background-color: $backgroundColor !important;" => $backgroundColor,
     "--background-image: url('$backgroundImage')" => $backgroundImage,
     "--text-color: $textColor" => $textColor,
 ])>
    <div class="hm-5-container">
        <div class="hero-hm5-text">
            @if ($subtitle = $shortcode->subtitle)
                <div class="hero-hm5-sub-title">
                    <p>{!! BaseHelper::clean($subtitle) !!}</p>
                </div>
            @endif

            @if ($title = $shortcode->title)
                <div class="hero-hm5-title">
                    <h2>{!! BaseHelper::clean($title) !!}</h2>
                </div>
            @endif

            @if (($url = $shortcode->url) && ($buttonLabel = $shortcode->button_label))
                <div class="hero-hm5-btn">
                    <a href="{{ $url }}" class="hm5-btn-1">
                        {!! BaseHelper::clean($buttonLabel) !!}
                        {!! Theme::partial('icon-button') !!}
                    </a>
                </div>
            @endif
        </div>
        <div class="hero-hm5-img">
            @if ($bannerImage = $shortcode->banner_image)
                {{ RvMedia::image($bannerImage, $title) }}
            @endif
        </div>
    </div>
    @if ($shape1 = $shortcode->shape_image_1)
        <div class="shape1">
            {{ RvMedia::image($shape1, 'shape') }}
        </div>
    @endif

    @if ($shape2 = $shortcode->shape_image_2)
        <div class="shape2">
            {{ RvMedia::image($shape2, 'shape') }}
        </div>
    @endif
</section>
