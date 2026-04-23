@php
    $firstPost = $posts->shift();
    Theme::set('headerAbsolute', true);
@endphp

<section class="echo-hero-section home-three" @style($variableStyles)>
    <div class="echo-hero">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                        'col-12' => ! $sidebar,
                    ])
                >
                    <div class="echo-hero-inner">
                        <div class="row">
                            @if ($firstPost)
                                <div class="col-lg-7">
                                    <div class="banner-content">
                                        @if ($category = $firstPost->firstCategory)
                                            {!! Theme::partial('category-badge', compact('category')) !!}
                                        @endif
                                        <div class="contents">
                                            <a href="{{ $firstPost->url }}" class="truncate-custom truncate-3-custom title-hover text-heading capitalize" title="{{ $name = $firstPost->name }}">{{ $name }}</a>

                                            @if ($description = $firstPost->description)
                                                <div class="desc">
                                                    <p class="truncate-custom truncate-2-custom">{!! BaseHelper::clean($description) !!}</p>
                                                </div>
                                            @endif
                                            <div class="content-bottom-info mb--25">
                                                {!! Theme::partial('post-meta', ['post' => $firstPost]) !!}
                                            </div>
                                            <div class="echo-button-wrapper">
                                                <a href="{{ $firstPost->url }}" class="rts-btn btn-secondary">
                                                    {{ $shortcode->button_label ?: __(' Read More ') }}
                                                    <i class="fa-light fa-arrow-right"></i>
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (! empty($posts))
                                <div class="col-lg-5">
                                    <div class="echo-hero-right-side">
                                        <div class="right-side-inner">
                                            @if ($title = $shortcode->title)
                                                <h2 @class([
                                                    'subtitle text-heading',
                                                    'text-center' => $shortcode->title_align == 'center',
                                                    'text-end' => $shortcode->title_align == 'end',
                                                ])>
                                                    {!! BaseHelper::clean($title) !!}
                                                </h2>
                                            @endif
                                            <div class="content-box">
                                                <ul>
                                                    @foreach($posts as $post)
                                                        <li class="wrapper">
                                                            <div class="image-area img-transition-scale">
                                                                <a href="{{ $post->url }}">
                                                                    {{ RvMedia::image($post->image, $post->name, 'thumb') }}
                                                                </a>
                                                            </div>
                                                            <div class="content">
                                                                @if ($category = $post->firstCategory)
                                                                    {!! Theme::partial('category-badge', compact('category')) !!}
                                                                @endif

                                                                <a class="truncate-custom truncate-2-custom content-title1 title-hover" href="{{ $post->url }}" title="{{ $name = $post->name }}">{{ $name }}</a>

                                                                <div class="content-bottom-info">
                                                                    {!! Theme::partial('post-meta', compact('post')) !!}
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
