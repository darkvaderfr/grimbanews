<section class="echo-about-area">
    <div class="container">
        <div class="echo-about-area-inner">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="content-area">
                        @if ($title = $shortcode->title)
                            <h3 class="title">{!! BaseHelper::clean($title) !!}</h3>
                        @endif
                        @if ($subtitle = $shortcode->subtitle)
                            <h5 class="sub-title">{!! BaseHelper::clean($subtitle) !!}</h5>
                        @endif
                        @if ($description = $shortcode->description)
                            <p class="desc">{!! BaseHelper::clean($description) !!}</p>
                        @endif

                        @if (($staffName = $shortcode->staff_name) || $shortcode->staff_signature)
                            <div class="bottom-author-area">
                                <div class="content">
                                    <h5 class="author-name">{{ $staffName }}</h5>
                                    @if ($staffDescription = $shortcode->staff_description)
                                        <p class="desc">{{ $staffDescription }}</p>
                                    @endif
                                </div>

                                @if ($signature = $shortcode->staff_signature)
                                    <div class="sign">
                                        {{ RvMedia::image($signature, 'signature') }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if (($firstImage = $shortcode->first_image) || $shortcode->second_image)
                    <div class="col-lg-6">
                        <div class="hero-image">
                            @if ($firstImage)
                                <div class="image">
                                    {{ RvMedia::image($firstImage, $title) }}
                                </div>
                            @endif

                            @if ($secondImage = $shortcode->second_image)
                                <div class="image image-2">
                                    {{ RvMedia::image($secondImage, $title) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
