<section @class(['echo-hm2-video-area', 'echo-feature-area' => $sidebar]) @style($variableStyles)>
    <div class="echo-hm2-video-area-content">
        <div @class(['echo-container' => ! $sidebar, 'container' => $sidebar])>
            <div class="row gx-6">
                <div @class([
                    'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                    'col-12' => ! $sidebar,
                ])>
                    <div class="echo-hm2-video-area-full-content">
                        @if ($title = $shortcode->title)
                            <div class="echo-home-2-title">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="echo-home-2-main-title">
                                                <h2 class="text-capitalize title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="echo-row text-center">
                            <div class="swiper echo-hm2-video-Swiper">
                                <div class="swiper-wrapper">
                                    @foreach($posts as $post)
                                        <div class="swiper-slide">
                                            <div class="echo-hm2-video-img position-relative">
                                                <a href="{{ $post->url }}" title="{{ $post->name }}">
                                                    {{ RvMedia::image($post->image, $post->name) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}

                                                <div class="echo-hm2-video-text-box">
                                                    @if ($category = $post->firstCategory)
                                                        <div class="echo-hm2-video-area-shep">
                                                            <div class="home-2-area-shep secondary">
                                                                <p class="text-capitalize"><a href="{{ $category->url }}"><span>{{ $category->name }}</span></a></p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'text-center']) !!}
                                                    <div class="echo-hm2-video-heading">
                                                        <h1 class="text-capitalize text-center truncate-custom truncate-2-custom"><a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover">{{ $post->name }}</a></h1>
                                                    </div>

                                                    @if ($description = $post->description)
                                                        <div class="echo-hm2-video-discription">
                                                            <p class="truncate-custom truncate-2-custom desc" title="{{ $description }}">{!! BaseHelper::clean($description) !!}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="echo-banner-slider-button">
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
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
