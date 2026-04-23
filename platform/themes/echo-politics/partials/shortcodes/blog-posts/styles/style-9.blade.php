<section class="echo-video-area home-eight" @style($variableStyles)>
    <div @class(['container' => $sidebar])>
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @if ($title = $shortcode->title)
                    <div class="hm5-feature-title">
                        <div class="hm-5-title-btn">
                            <div class="hm-5-main-title w-100">
                                <h2 class="text-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                            </div>
                        </div>
                    </div>
                @endif

                @if (($firstPost = $posts->first()) && echo_is_video_post($firstPost))
                    <div class="echo-video-area-inner">
                        <div class="vedio-icone">
                            <a class="play-video popup-youtube video-play-button" href="{{ echo_get_post_video_url($firstPost) }}" aria-label="{{ __('Play Video') }}">
                                <span></span>
                            </a>
                            <div class="video-overlay">
                                <a class="video-overlay-close">×</a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="slider-inner">
                    <div class="echo-video-bottom-area">
                        <div class="swiper swiper-container-h8">
                            <div class="swiper-wrapper">
                                @foreach($posts as $post)
                                    <div class="swiper-slide">
                                        <div class="slider-inner">
                                            <div class="container">
                                                <div class="echo-video-bottom-area">
                                                    <div class="banner-bottom-inner">
                                                        <div class="content">
                                                            @if ($category = $post->firstCategory)
                                                                <span class="content-catagory-tag">{{ $category->name }}</span>
                                                            @endif

                                                            <h5 class="title">
                                                                <a href="{{ $post->url }}" title="{{ $post->name }}" class="text-capitalize title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                            </h5>

                                                            {!! Theme::partial('post-meta', compact('post')) !!}
                                                        </div>
                                                    </div>
                                                    <div class="swiper-btn">
                                                        <div class="swiper-button-prev"><i class="fa-regular fa-angle-left"></i></div>
                                                        <div class="swiper-button-next"><i class="fa-regular fa-angle-right"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
                </div>
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12 sticky-coloum-item">
                    {!! dynamic_sidebar($sidebar) !!}
                </div>
            @endif
        </div>
    </div>
</section>
