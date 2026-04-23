@php
    $isBackgroundTransparent = echo_check_transparent_color($shortcode->background_color);
    $titleColor = $isBackgroundTransparent ? 'inherit' : '#ffffff';
    $lineColor = $isBackgroundTransparent ? '#5E5E5E' : '#ffffff';
    $textBodyColor = $isBackgroundTransparent ? 'var(--text-body)' : 'rgba(255, 255, 255, 0.8)';
@endphp

<section @class(['echo-home-2-feature-area', 'echo-feature-area' => $sidebar])
    @style([
        ...$variableStyles,
        "--title-color: $titleColor" => $titleColor,
        "--line-color: $lineColor" => $lineColor,
        "--text-body-color: $textBodyColor" => $textBodyColor,
    ])
>
    <div class="echo-home-2-feature-area-content">
        <div class="container">
            <div class="row gx-6">
                <div
                    @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                        'col-12' => ! $sidebar,
                    ])
                >
                    <div class="echo-home-2-feature-area-full-content">
                        @if ($title = $shortcode->title)
                            <div class="echo-home-2-title">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="echo-home-2-main-title">
                                            <h2 class="text-capitalize title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($posts->isNotEmpty())
                            <div class="echo-feature-content">
                                <div class="row gx-5">
                                    @foreach($posts as $post)
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <div class="echo-feature-main-content">
                                                <div class="echo-feature-area-img">
                                                    <div class="echo-feature-area-home-2-post img-transition-scale position-relative">
                                                        <a href="{{ $post->url }}">
                                                            {{ RvMedia::image($post->image, $post->name, 'small') }}
                                                        </a>

                                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                                    </div>

                                                    @if ($category = $post->firstCategory)
                                                        <div class="echo-feature-area-shep">
                                                            <div class="home-2-area-shep secondary">
                                                                <p class="text-capitalize">
                                                                    <a title="{{ $category->name }}" href="{{ $category->url }}"><span>{{ $category->name }}</span></a>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="echo-feature-area-post-title">
                                                    <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover text-capitalize truncate-2-custom truncate-custom">{{ $post->name }}</a>
                                                    @if ($description = $post->description)
                                                        <p class="desc">{!! BaseHelper::clean($description) !!}</p>
                                                    @endif
                                                </div>

                                                <div class="home-2-read-more-btn">
                                                    <a href="{{ $post->url }}" class="text-uppercase">{{ __('Read More') }}</a>
                                                </div>
                                            </div>
                                        </div>
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
</section>
