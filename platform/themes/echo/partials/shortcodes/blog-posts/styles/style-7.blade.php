@php
    $latestTwoPosts = $posts->count() >= 3 ? $posts->pop(2) : collect();
@endphp

<section @class(['echo-banner', 'echo-feature-area' => $sidebar])
    @style($variableStyles)
>
    <div class="echo-banner-content">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                        'col-12' => ! $sidebar,
                    ])>
                    <div class="echo-banner-full-content">
                        <div class="row gx-5 justify-content-md-center">
                            <div class="col-xl-9 col-lg-8 col-md-12">
                                <div class="swiper rts-bannerSlider">
                                    <div class="swiper-wrapper">
                                        @foreach($posts as $post)
                                            <div class="swiper-slide">
                                                <div class="echo-banner-mani-content">
                                                    <div class="echo-banner-img position-relative">
                                                        <a href="{{ $post->url }}">
                                                            {{ RvMedia::image($post->image, $post->name, 'extra-large', attributes: ['class' => 'img-hover']) }}
                                                        </a>

                                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                                    </div>
                                                    <div class="echo-banner-text">
                                                        <div class="echo-home-2-banner-col-act-text">
                                                            <div class="echo-banner-shep">
                                                                @if ($category = $post->firstCategory)
                                                                    <div class="home-2-area-shep">
                                                                        <p class="text-capitalize"><a href="{{ $category->url }}"><span>{{ $category->name }}</span></a></p>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {!! Theme::partial('post-meta', ['post' => $post]) !!}

                                                            <div class="echo-banner-heading">
                                                                <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                            </div>
                                                        </div>
                                                        <div class="echo-banner-slider-button">
                                                            <div class="swiper-button-next"></div>
                                                            <div class="swiper-button-prev"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-4 col-md-8">
                                @if ($latestTwoPosts->isNotEmpty())
                                    <div class="echo-home-2-banner-right-flexing">
                                        @foreach($latestTwoPosts as $post)
                                            <div class="echo-banner-right-itme">
                                                <div class="echo-banar-right-img">
                                                    <div class="echo-banar-right-img-content img-transition-scale position-relative">
                                                        <a href="{{ $post->url }}">
                                                            {{ RvMedia::image($post->image, $post->name, 'small') }}
                                                        </a>

                                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                                    </div>

                                                    <div class="echo-banner-shep">
                                                        @if ($category = $post->firstCategory)
                                                            <div class="home-2-area-shep">
                                                                <p class="text-capitalize"><a href="{{ $category->url }}"><span>{{ $category->name }}</span></a></p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="echo-banner-right-heading">
                                                    <a href="{{ $post->url }}" class="title-hover text-capitalize truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                </div>
                                            </div>

                                            @if (! $loop->last)
                                                <hr>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
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
