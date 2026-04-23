@php
    $rows = $posts->chunk(5);
@endphp

<div class="echo-food-review-area" @style($variableStyles)>
    <div class="hm-9-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @if ($title = $shortcode->title)
                    <div class="hm5-feature-title">
                        <div class="hm-5-main-title">
                            <h2 @class([
                                'text-center' => $shortcode->title_align == 'center',
                                'text-end' => $shortcode->title_align == 'end',
                            ])>
                                {!! BaseHelper::clean($title) !!}
                            </h2>
                        </div>
                    </div>
                @endif
                <div class="section-inner">
                    @foreach($rows as $posts)
                        <div class="row mb-5">
                            @if ($firstPost = $posts->first())
                                <div class="col-xl-8 col-lg-7">
                                    <div class="left-side-content">
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
                                                    <a href="{{ $firstPost->url }}" class="title-hover truncate-2-custom truncate-custom">{{ $firstPost->name }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-xl-4 col-lg-5">
                                <div class="right-side-banner">
                                    @foreach($posts->skip(1) as $post)
                                        <div class="echo-hero-baner small">
                                            <div class="echo-hero-banner-main-img img-transition-scale">
                                                <a href="{{ $post->url }}">
                                                    {{ RvMedia::image($post->image, $post->name, 'thumb-medium', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                                </a>
                                            </div>
                                            <div class="content">
                                                {!! Theme::partial('category-badge', compact('post')) !!}
                                                <div class="echo-hero-title font-weight-bold">
                                                    <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
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
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12">
                    {!! dynamic_sidebar($sidebar) !!}
                </div>
            @endif
        </div>
    </div>
</div>
