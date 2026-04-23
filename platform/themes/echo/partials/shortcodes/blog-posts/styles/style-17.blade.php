@php
    $rows = $posts->chunk(4);
@endphp

<div class="echo-banner-area home-ten" @style($variableStyles)>
    <div class="hm-9-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @foreach($rows as $posts)
                    <div class="banner-inner">
                        @if ($firstPost = $posts->first())
                            <div class="left-side-image">
                                <div class="echo-hero-baner">
                                    <div class="echo-hero-banner-main-img img-transition-scale">
                                        <a href="{{ $firstPost->url }}">
                                            {{ RvMedia::image($firstPost->image, $firstPost->name, 'column', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                        </a>
                                    </div>
                                    <div class="content">
                                        <div class="echo-hero-title font-weight-bold">
                                            <a href="{{ $firstPost->url }}" title="{{ $name = $firstPost->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $name }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="right-side-content">
                            <div class="right-side-inner">
                                @foreach($posts->skip(1)->take(2) as $post)
                                    <div class="echo-hero-baner">
                                        <div class="echo-hero-banner-main-img img-transition-scale">
                                            <a href="{{ $post->url }}">
                                                {{ RvMedia::image($post->image, $post->name, 'column-small' ,attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                            </a>
                                        </div>
                                        <div class="content">
                                            <div class="echo-hero-title font-weight-bold">
                                                <a href="{{ $post->url }}" title="{{ $name = $post->name }}" class="title-hover truncate-2-custom truncate-custom">{{ $name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($lastPost = $posts->skip(3)->first())
                                <div class="content">
                                    <div class="echo-hero-title font-weight-bold">
                                        <a href="{{ $lastPost->url }}" title="{{ $name = $lastPost->name }}" class="title-hover truncate-custom truncate-3-custom">{{ $name }}</a>
                                    </div>
                                    <a href="{{ $lastPost->url }}" class="read-more-btn hm8-btn-1">{{ __('Read More') }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12">
                    {!! dynamic_sidebar($sidebar) !!}
                </div>
            @endif
        </div>
    </div>
</div>
