@if (is_plugin_active('gallery') && $galleries->isNotEmpty())
    @php
        $textColor = $shortcode->text_color;
        $backgroundColor = $shortcode->background_color;
    @endphp

    <section class="echo-social-media-area-hm2 home-ten"
        @style([
           "--background-color: $backgroundColor" => $backgroundColor,
           "--text-color: $textColor" => $textColor,
       ])
    >
        @if ($title = $shortcode->title)
            <div class="hm5-feature-title">
                <div class="hm-5-title-btn">
                    <div class="hm-5-main-title w-100 text-center">
                        <h2>{!! BaseHelper::clean($title) !!}</h2>
                    </div>
                </div>
            </div>
        @endif

        <div class="echo-social-media-area-content">
            <div class="echo-social-media-main-content">
                @foreach($galleries as $gallery)
                    <div class="echo-hm2-social-item">
                        {{ RvMedia::image($gallery->image, $gallery->name, 'column-small') }}
                        <a href="{{ $gallery->url }}" title="{{ $gallery->name }}">
                            <div class="echo-hm2-social-item-overlay">
                                <span><i class="fa-brands fa-instagram"></i></span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
