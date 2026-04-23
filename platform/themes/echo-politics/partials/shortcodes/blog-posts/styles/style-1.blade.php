@php
    $firstPost = $posts->shift();
@endphp

<div class="echo-banner-area home-seven" @style($variableStyles)>
    <div class="hm-7-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                <div class="banner-inner">
                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="left-side">
                                <div class="echo-hero-baner">
                                    <div class="echo-hero-banner-main-img img-transition-scale position-relative">
                                        <a href="{{ $firstPost->url }}">
                                            {{ RvMedia::image($firstPost->image, $firstPost->name, 'extra-large', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                    </div>
                                    <div class="content">
                                        {!! Theme::partial('category-badge', ['post' => $firstPost]) !!}
                                        <div class="echo-hero-title font-weight-bold">
                                            <a href="{{ $firstPost->url }}" title="{{ $firstPost->name }}" class="title-hover truncate-2-custom truncate-custom">{{ $firstPost->name }}</a>
                                        </div>

                                        {!! Theme::partial('post-meta', ['post' => $firstPost, 'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (! empty($posts))
                            <div class="col-xl-4 col-lg-5">
                                <div class="right-side-banner">
                                    @foreach($posts as $post)
                                        <div @class(['echo-hero-baner small', 'last' => $loop->last])>
                                            <div class="echo-hero-banner-main-img img-transition-scale position-relative">
                                                <a href="{{ $post->url }}">
                                                    {{ RvMedia::image($post->image, $post->name, 'medium-square', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                            </div>
                                            <div class="content">
                                                {!! Theme::partial('category-badge', compact('post')) !!}
                                                <div class="echo-hero-title font-weight-bold">
                                                    <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                </div>
                                                <p class="echo-story-text">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="10" viewBox="0 0 11 10" fill="none">
                                                    <path d="M8 1H10C10.1326 1 10.2598 1.05268 10.3536 1.14645C10.4473 1.24021 10.5 1.36739 10.5 1.5V9.5C10.5 9.63261 10.4473 9.75979 10.3536 9.85355C10.2598 9.94732 10.1326 10 10 10H1C0.867392 10 0.740215 9.94732 0.646447 9.85355C0.552678 9.75979 0.5 9.63261 0.5 9.5V1.5C0.5 1.36739 0.552678 1.24021 0.646447 1.14645C0.740215 1.05268 0.867392 1 1 1H3V0H4V1H7V0H8V1ZM7 2H4V3H3V2H1.5V4H9.5V2H8V3H7V2ZM9.5 5H1.5V9H9.5V5Z" fill="#9F68F5"/>
                                                </svg>
                                                {{ $post->created_at->format('d M Y') }}
                                            </span>
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12">
                    {!! dynamic_sidebar($sidebar) !!}
                </div>
            @endif
        </div>
    </div>
</div>
