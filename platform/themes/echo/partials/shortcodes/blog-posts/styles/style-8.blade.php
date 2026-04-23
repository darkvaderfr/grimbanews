@php
    $latestTwoPosts = $posts->count() >= 3 ? $posts->pop(2) : collect();
@endphp

<section @class(['echo-archive-area', 'echo-feature-area' => $sidebar])
    @style($variableStyles)
>
    <div class="echo-archive-area-content">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                        'col-12' => ! $sidebar,
                    ])>
                    <div class="echo-archive-area-full-content">
                        @if ($title = $shortcode->title)
                            <div class="echo-home-2-title">
                                <div class="row text-left">
                                    <div class="col-lg-12 col-md-12">
                                        <div class="echo-home-2-main-title">
                                            <h2 class="text-capitalize title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="echo-archive-area-slider-content">
                            <div class="row gx-5">
                                <div class="col-lg-7 col-md-12">
                                    <div class="echo-archive-main-content area-2">
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
                                                                <div class="echo-banner-shep">
                                                                    @if ($category = $post->firstCategory)
                                                                        <div class="home-2-area-shep">
                                                                            <p class="text-capitalize">
                                                                                <a href="{{ $category->url }}"><span>{{ $category->name }}</span></a>
                                                                            </p>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                {!! Theme::partial('post-meta', compact('post')) !!}
                                                                <div class="echo-banner-heading">
                                                                    <h1 class="text-capitalize">
                                                                        <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                                    </h1>
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
                                </div>
                                <div class="col-lg-5 col-md-12">
                                    @if ($latestTwoPosts->isNotEmpty())
                                        <div class="echo-fd-psot-home-2-responsive-flexing">
                                            @foreach($latestTwoPosts as $post)
                                                <div class="echo-archive-area-right-site">
                                                    <div class="echo-archive-right-content">
                                                        <div class="echo-archive-right-text">
                                                            @if ($category = $post->firstCategory)
                                                                <div class="echo-archive-right-area-shep">
                                                                    <div class="home-2-area-shep secondary">
                                                                        <p class="text-capitalize">
                                                                            <a href="{{ $category->url }}">
                                                                                <span>{{ $category->name }}</span>
                                                                            </a>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            {!! Theme::partial('post-meta', ['post' => $post]) !!}
                                                            <div class="echo-archive-right-heading">
                                                                <h5 class="text-capitalize">
                                                                    <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                                </h5>
                                                            </div>
                                                        </div>
                                                        <div class="echo-archive-right-img img-transition-scale position-relative">
                                                            <a href="{{ $post->url }}">
                                                                {{ RvMedia::image($post->image, $post->name, 'thumb') }}
                                                            </a>

                                                            {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
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
