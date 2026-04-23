@php
    $reverseRow ??= false;
    $rows = $posts->chunk(4);

    $isBackgroundTransparent = echo_check_transparent_color($shortcode->background_color);
    $titleColor = $isBackgroundTransparent ? 'inherit' : '#ffffff';
    $lineColor = $isBackgroundTransparent ? '#5E5E5E' : '#ffffff';
@endphp

<section @class(['echo-fd-post-area', 'echo-feature-area' => $sidebar])
    @style([
        ...$variableStyles,
        "--title-color: $titleColor" => $titleColor,
        "--line-color: $lineColor" => $lineColor,
    ])
>
    <div class="echo-fd-post-content">
        <div class="container">
            <div class="row gx-6">
                <div
                    @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                        'col-12' => ! $sidebar,
                    ])
                >
                    <div class="echo-fd-post-full-content">
                    @if ($title = $shortcode->title)
                        <div class="echo-home-2-title">
                            <div class="row">
                                <div class="col-lg-12 col-md-12">
                                    <div class="echo-home-2-main-title">
                                        <h2 class="text-capitalize title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="echo-fd-post-main-content">
                        @foreach($rows as $posts)
                            <div @class(['row gx-5', 'flex-row-reverse' => $reverseRow])>
                                @if ($firstPost = $posts->first())
                                    <div class="col-lg-6 col-md-12">
                                        <div class="echo-fd-post-left">
                                            <div class="echo-fd-post-left-img img-transition-scale position-relative">
                                                <a href="{{ $firstPost->url }}">
                                                    {{ Rvmedia::image($firstPost->image, $firstPost->name, 'thumb-medium') }}
                                                </a>

                                                @if ($category = $firstPost->firstCategory)
                                                    <div class="echo-fd-area-left-site-shep">
                                                        <div class="home-2-area-shep secondary">
                                                            <p class="text-capitalize">
                                                                <a href="{{ $category->url }}" title="{{ $category->name }}"><span>{{ $category->name }}</span></a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            {!! Theme::partial('post-meta', [
                                                'post' => $firstPost,
                                                'wrapperClass' => 'text-center'
                                            ]) !!}
                                            <div class="echo-fd-post-left-title">
                                                <a href="{{ $firstPost->url }}" class="title-hover truncate-custom truncate-2-custom" title="{{ $name = $firstPost->name }}">
                                                    {{ $name }}
                                                </a>

                                                @if ($description = $firstPost->description)
                                                    <p class="text-center desc truncate-custom truncate-2-custom">{!! BaseHelper::clean($description) !!}</p>
                                                @endif
                                            </div>

                                            <div class="home-2-read-more-btn text-center">
                                                <a href="{{ $firstPost->url }}" class="text-uppercase">{{ __('Read More') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-lg-6 col-md-12">
                                    <div class="echo-fd-post-home-2-responsive-flexing">
                                        @foreach($posts->skip(1) as $post)
                                            <div class="echo-fd-post-right-area">
                                                <div class="echo-fd-post-right-content img-transition-scale">
                                                    <a href="{{ $post->url }}">
                                                        {{ Rvmedia::image($post->image, $post->name, 'small') }}
                                                    </a>
                                                </div>
                                                <div class="echo-fd-post-right-text">
                                                    @if ($category = $post->firstCategory)
                                                        <div class="echo-fd-post-right-area-shep">
                                                            <div class="home-2-area-shep secondary">
                                                                <p class="text-capitalize">
                                                                    <a href="{{ $category->url }}"><span>{{ $category->name }}</span></a>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'text-center']) !!}
                                                    <div class="echo-fd-post-right-heading">
                                                        <a href="{{ $post->url }}" class="text-capitalize title-hover truncate-custom truncate-3-custom" title="{{ $name = $post->name }}">{{ $name }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
