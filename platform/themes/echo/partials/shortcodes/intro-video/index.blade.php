<section class="echo-video-area inner" @if ($image = $shortcode->image) style="background-image: url({{ RvMedia::getImageUrl($image) }}) !important;" @endif>
    <div class="container">
        <div class="echo-video-area-inner">
            <div class="vedio-icone">
                @if (($videoId = $shortcode->video_id) && ($playIcon = $shortcode->play_icon))
                    <a
                        class="play-btn popup-video play-video popup-youtube video-play-button"
                        data-aos="fade-left"
                        data-aos-delay="700"
                        href="https://www.youtube.com/watch?v={{ $videoId }}"
                        aria-label="{{ __('Play Video') }}"
                    >
                        {!! BaseHelper::renderIcon($playIcon, attributes: ['class' => 'about-us-play-icon']) !!}
                    </a>
                @endif

                <div class="video-overlay">
                    <a class="video-overlay-close">×</a>
                </div>
            </div>
        </div>
    </div>
</section>
