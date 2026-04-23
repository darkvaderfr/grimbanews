@if (is_plugin_active('gallery') && $galleries->isNotEmpty())
    @php
        $textColor = $shortcode->text_color;
        $backgroundColor = $shortcode->background_color;
    @endphp
    <section class="echo-social-media-area-hm2"
        @style(["--background-color: $backgroundColor" => $backgroundColor])>
        @if ($title = $shortcode->title)
            <div class="container">
                <div class="echo-home-2-title">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="echo-home-2-main-title">
                                <h4 class="text-capitalize" @style(["color: $textColor !important;" => $textColor])>{!! BaseHelper::clean($title) !!}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="echo-social-media-area-content">
            <div class="echo-social-media-main-content">
                @foreach($galleries as $gallery)
                    <div class="echo-hm2-social-item">
                         {{ RvMedia::image($gallery->image, $gallery->name, 'medium-square') }}
                        <a href="{{ $gallery->url }}">
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
