@php
    $firstPost = $posts->shift();
@endphp

<section @class(['echo-hero-section', 'echo-feature-area' => $sidebar]) @style($variableStyles)>
    <div class="echo-hero">
        <div class="container">
            <div class="echo-full-hero-content">
                <div class="row gx-6">
                    <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                        'col-12' => ! $sidebar,
                    ])>
                        <div class="row gx-5">
                            <div class="col-xl-8 col-lg-7 col-md-12">
                                <div class="echo-hero-baner">
                                    <div class="echo-hero-banner-main-img  img-transition-scale position-relative mb-3">
                                        <a href="{{ $firstPost->url }}">
                                            {{ RvMedia::image($firstPost->image, $firstPost->name, 'large', attributes: ['class' => 'banner-image-one img-hover', 'loading' => false]) }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                    </div>
                                    <a href="{{ $firstPost->url }}" class="title-hover truncate-custom truncate-2-custom echo-hero-title text-capitalize font-weight-bold" title="{{ $firstPost->name }}">{{ $firstPost->name }}</a>
                                    <hr>
                                    @if ($description = $firstPost->description)
                                        <p class="echo-hero-discription desc">{!! BaseHelper::clean(Str::limit($description, 200)) !!}</p>
                                    @endif

                                    {!! Theme::partial('post-meta', ['post' => $firstPost, 'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share']) !!}
                                </div>
                            </div>

                            @if (! empty($posts))
                                <div class="col-xl-4 col-lg-5 col-md-12">
                                    <div class="echo-home-1-hero-area-top-story">
                                        <h2 class="title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($shortcode->title) !!}</h2>

                                        @foreach ($posts as $post)
                                            @if ($loop->first)
                                                <div class="echo-top-story first">
                                                    <div class="echo-story-picture img-transition-scale position-relative">
                                                        <a href="{{ $post->url }}">
                                                            {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                                        </a>

                                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                                    </div>
                                                    <div class="echo-story-text">
                                                        <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>

                                                        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-trending-post-bottom-icons']) !!}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="echo-top-story">
                                                    <div class="echo-story-picture img-transition-scale position-relative">
                                                        <a href="{{ $post->url }}">
                                                            {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'img-hover']) }}
                                                        </a>

                                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                                    </div>
                                                    <div class="echo-story-text">
                                                        <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>

                                                        {!! Theme::partial('post-meta', compact('post')) !!}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
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
    </div>
</section>
