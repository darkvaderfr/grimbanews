<section @class(['echo-latest-news-area', 'echo-feature-area' => $sidebar]) @style($variableStyles)>
    <div class="echo-latest-news-content">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                    'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                    'col-12' => ! $sidebar,
                ])>
                    <div class="echo-be-slider-btn">
                        @if ($title = $shortcode->title)
                            <div class="echo-latest-nw-title" @style(['flex-basis: 100% !important;' => count($posts) <= 4])>
                                <h2 class="title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                            </div>
                        @endif

                        <div @class(['echo-latest-news-next-prev-btn', 'd-none' => count($posts) <= 4]) >
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                    <div class="echo-latest-news-full-content">
                        <div class="swiper mySwiper">
                            <div class="swiper-wrapper">
                                @foreach($posts as $post)
                                    <div class="swiper-slide">
                                        <div class="echo-latest-news-main-content">
                                            <div class="echo-latest-news-img img-transition-scale position-relative">
                                                <a href="{{ $post->url }}">
                                                    {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                            </div>
                                            <div class="echo-latest-news-single-title">
                                                <a href="{{ $post->url }}" title="{{ $post->name }}" class="text-capitalize title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                            </div>

                                            {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-latest-news-time-views']) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @if ($sidebar)
                    <div class="col-xl-4 col-lg-5 col-md-12">
                        <div class="echo-feature-area-right-site-flexing">
                            {!! dynamic_sidebar($sidebar) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
