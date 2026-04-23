@php
    $rows = $posts->chunk(7);
@endphp

<div class="echo-banner-area home-seven" @style($variableStyles)>
    <div class="hm-7-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-8"' => $sidebar = $shortcode->sidebar,
                'col-12' => ! $sidebar,
            ])>
                <div class="banner-inner">
                    @foreach($rows as $posts)
                        @php
                            $firstPost = $posts->first();
                        @endphp
                        <div class="row">
                            <div class="col-xl-3 col-lg-3">
                                <div class="left-side-banner">
                                    @foreach($posts->skip(1)->take(2) as $post)
                                        <div @class(['echo-hero-baner small', 'last' => $loop->last])>
                                            <div class="echo-hero-banner-main-img">
                                                <a href="{{ $post->url }}">
                                                    {{  RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'banner-image-one']) }}
                                                </a>
                                            </div>
                                            <div class="content">
                                                {!! Theme::partial('category-badge', compact('post')) !!}

                                                <div class="echo-hero-title font-weight-bold">
                                                    <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if ($firstPost)
                                <div class="col-xl-6 col-lg-6">
                                    <div class="echo-hero-baner">
                                        <div class="echo-hero-banner-main-img  img-transition-scale position-relative">
                                            <a href="{{ $firstPost->url }}">
                                                {{  RvMedia::image($firstPost->image, $firstPost->name, 'extra-large', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                            </a>

                                            {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                        </div>
                                        <div class="content">
                                            {!! Theme::partial('category-badge', compact('post')) !!}
                                            <div class="echo-hero-title font-weight-bold">
                                                <a href="{{ $firstPost->url }}" class="title-hover truncate-2-custom truncate-custom ">{{ $firstPost->name }}</a>
                                            </div>

                                            @if ($description = $firstPost->description)
                                                <p class="echo-hero-discription desc truncate-2-custom truncate-custom">{!! BaseHelper::clean($description) !!}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-xl-3 col-lg-3">
                                <div class="right-side-banner">
                                    @foreach($posts->skip(3) as $post)
                                        <div @class(['echo-hero-baner small', 'last' => $loop->last])>
                                            <div class="echo-hero-banner-main-img">
                                                <a href="{{ $post->url }}">
                                                    {{  RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'banner-image-one']) }}
                                                </a>
                                            </div>
                                            <div class="content">
                                                {!! Theme::partial('category-badge', compact('post')) !!}

                                                <div class="echo-hero-title font-weight-bold">
                                                    <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
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
                <div class="col-xl-4 col-lg-4">
                    <div class="right-side-content">
                        {!! dynamic_sidebar($sidebar) !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
