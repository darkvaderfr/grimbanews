@php
    $rows = $posts->chunk(4);
@endphp

<section class="echo-video-area home-seven" @style($variableStyles)>
    <div class="hm-7-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                <div class="col-xl-12 col-lg-12 col-md-12">
                    @if ($title = $shortcode->title)
                        <div class="hm5-feature-title">
                            <div class="hm-5-title-btn">
                                <div class="hm-5-main-title">
                                    @php $titleAlign = $shortcode->title_align; @endphp
                                    <h2 @class(['text-start' => $titleAlign == 'start', 'text-end' => $titleAlign == 'end'])>
                                        {!! BaseHelper::clean($title) !!}
                                    </h2>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
                <div class="section-inner">
                    @foreach($rows as $posts)
                        @php
                            $firstPost = $posts->first();
                        @endphp
                        <div class="row">
                            @if ($firstPost)
                                <div class="col-xl-6 col-lg-6">
                                    <div class="echo-hero-baner">
                                        <div class="echo-hero-banner-main-img  img-transition-scale position-relative">
                                            <a href="{{ $firstPost->url }}">
                                                {{ RvMedia::image($firstPost->image, $firstPost->name, 'extra-large', attributes: ['class' => 'banner-image-one img-hover']) }}
                                            </a>

                                            {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                        </div>
                                        <div class="content">
                                            {!! Theme::partial('category-badge', ['post' => $firstPost]) !!}
                                            <h1 class="echo-hero-title text-capitalize font-weight-bold">
                                                <a href="{{ $firstPost->url }}" class="title-hover">{{ $firstPost->name }}</a>
                                            </h1>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-xl-6 col-lg-6">
                                <div class="echo-home-1-hero-area-top-story">
                                    @foreach($posts->skip(1) as $post)
                                        <div @class(['echo-top-story', 'last' => $loop->last])>
                                            <div class="echo-story-picture img-transition-scale">
                                                <a href="{{ $post->url }}">
                                                    {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                            </div>
                                            <div class="echo-story-text">
                                                <a href="#" class="pe-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10" fill="none">
                                                        <path d="M7.5 1H9.5C9.63261 1 9.75979 1.05268 9.85355 1.14645C9.94732 1.24021 10 1.36739 10 1.5V9.5C10 9.63261 9.94732 9.75979 9.85355 9.85355C9.75979 9.94732 9.63261 10 9.5 10H0.5C0.367392 10 0.240215 9.94732 0.146447 9.85355C0.0526784 9.75979 0 9.63261 0 9.5V1.5C0 1.36739 0.0526784 1.24021 0.146447 1.14645C0.240215 1.05268 0.367392 1 0.5 1H2.5V0H3.5V1H6.5V0H7.5V1ZM6.5 2H3.5V3H2.5V2H1V4H9V2H7.5V3H6.5V2ZM9 5H1V9H9V5Z" fill="#909090" />
                                                    </svg>
                                                    {{ $post->created_at->format('d M Y') }}
                                                </a>
                                                <a href="{{ $post->url }}" class="title-hover truncate-3-custom truncate-custom">{{ $post->name }}</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12">
                    {!! dynamic_sidebar($sidebar) !!}
                </div>
            @endif
        </div>
    </div>
</section>
