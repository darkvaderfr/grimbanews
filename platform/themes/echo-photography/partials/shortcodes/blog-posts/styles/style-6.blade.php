@php
    $postsCount = $posts->count();

    if ($postsCount == 6) {
        $rows = $posts->chunk(3);
    }
@endphp

<div @class(['echo-photography-area', 'area3' => $postsCount == 6]) @style($variableStyles)>
    <div class="hm-9-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @if ($title = $shortcode->title)
                    <div class="hm5-feature-title">
                        <div class="hm-5-title-btn">
                            <div class="hm-5-main-title">
                                <h2 class="text-{{ $shortcode->title_align ?: 'center' }}">{!! BaseHelper::clean($title) !!}</h2>
                            </div>

                            @if ($postsCount !== 6)
                                <div class="hm5-area-title-btn">
                                    <a href="{{ get_blog_page_url() }}" class="hm8-btn-1">{{ __('SEE ALL') }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <div @class(['section-inner', 'd-flex' => $postsCount !== 6])>
                    @if ($postsCount === 6)
                        @foreach($rows as $posts)
                            <div class="bottom d-flex">
                                @foreach($posts as $post)
                                    <div class="echo-hero-baner">
                                        <div class="echo-hero-banner-main-img img-transition-scale">
                                            <a href="{{ $post->url }}">
                                                {{ RvMedia::image($post->image, $post->name, 'column', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                            </a>

                                            {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                        </div>
                                        <div class="content">
                                            <div class="echo-hero-title font-weight-bold">
                                                <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-3-custom">{{ $post->name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        @foreach($posts as $post)
                            <div class="echo-hero-baner">
                                <div class="echo-hero-banner-main-img img-transition-scale">
                                    <a href="{{ $post->url }}">
                                        {{ RvMedia::image($post->image, $post->name, 'large', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                    </a>

                                    {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                </div>
                                <div class="content">
                                    <div class="echo-hero-title font-weight-bold">
                                        <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-3-custom">{{ $post->name }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
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
