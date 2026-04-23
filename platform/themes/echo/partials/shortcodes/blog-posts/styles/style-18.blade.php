@php
    $rows = $posts->chunk(4);
@endphp

<div class="echo-photography-area area2" @style($variableStyles)>
    <div class="hm-9-container">
        @if ($title = $shortcode->title)
            <div class="hm5-feature-title">
                <div class="hm-5-title-btn">
                    <div class="hm-5-main-title">
                        <h2 class="text-{{ $shortcode->title_align ?: 'center' }}">{!! BaseHelper::clean($title) !!}</h2>
                    </div>
                </div>
            </div>
        @endif
        <div class="section-inner">
            @foreach($rows as $posts)
                <div class="row">
                    @if ($firstPost = $posts->first())
                        <div class="top">
                            <div class="echo-hero-baner">
                                <div class="echo-hero-banner-main-img img-transition-scale">
                                    <a href="{{ $firstPost->url }}">
                                        {{ RvMedia::image($firstPost->image, $firstPost->name, 'large', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                    </a>

                                    {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                </div>
                                <div class="content">
                                    <div class="echo-hero-title font-weight-bold">
                                        <a href="{{ $firstPost->url }}" title="{{ $firstPost->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $firstPost->name }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($posts = $posts->skip(1))
                        <div class="bottom d-flex">
                            @foreach($posts as $post)
                                <div class="echo-hero-baner">
                                    <div class="echo-hero-banner-main-img img-transition-scale position-relative">
                                        <a href="{{ $post->url }}">
                                            {{ RvMedia::image($post->image, $post->name, 'column-small', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                    </div>
                                    <div class="content">
                                        <div class="echo-hero-title font-weight-bold">
                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
