<section class="echo-video-area home-four" @style($variableStyles)>
    <div @class(['container' => $sidebar])>
        <div class="row gx-6">
            <div @class([
                'col-xl-8 col-lg-8"' => $sidebar = $shortcode->sidebar,
                'col-12' => ! $sidebar,
            ])>
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
                <div class="swiper swiper-container">
                    <div class="swiper-wrapper">
                        @foreach($posts as $post)
                            @php $backgroundImage = RvMedia::url($post->image) @endphp
                            <div class="swiper-slide"
                                @style(["--background-image: url($backgroundImage)" => $backgroundImage])
                            >
                                {!! Theme::partial('blog.post.partials.action-post', ['post' => $post]) !!}
                                <div class="slider-inner">
                                    <div class="container">
                                        <div class="echo-video-bottom-area">
                                            <div class="banner-bottom-inner">
                                                <div class="content">
                                                    {!! Theme::partial('category-badge', compact('post')) !!}

                                                    <a href="{{ $post->url }}" class="text-capitalize text-heading title-hover title truncate-custom truncate-2-custom">{{ $post->name }}</a>

                                                    {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-latest-news-time-views']) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-btn">
                        <div class="swiper-button-prev"><i class="fa-regular fa-angle-left"></i></div>
                        <div class="swiper-button-next"><i class="fa-regular fa-angle-right"></i></div>
                    </div>
                </div>
            </div>

            @if ($sidebar)
                <div class="col-xl-4 col-lg-4">
                    <div class="right-side-content">
                        {!! dynamic_sidebar($sidebar) !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
