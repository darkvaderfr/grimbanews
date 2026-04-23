<div class="echo-trending-news-area home-eight" @style($variableStyles)>
    <div class="hm-7-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                <div class="hm5-feature-title">
                    <div class="hm-5-title-btn">
                        @if ($title = $shortcode->title)
                            <div class="hm-5-main-title w-100 justify-content-{{ $shortcode->title_align ?: 'start' }}">
                                <h2>{!! BaseHelper::clean($title) !!}</h2>
                            </div>
                        @endif
                        <div class="hero-hm7-btn" style="flex-shrink: 0">
                            <a href="{{ get_blog_page_url() }}" class="hm7-btn-1">{{ __('See all') }}
                                <i class="fa-solid fa-arrow-right arrow-1"></i>
                                <i class="fa-solid fa-arrow-right arrow-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="section-inner">
                    <div class="row">
                        @foreach($posts as $post)
                            <div class="col-xl-4 col-lg-6 mb-3">
                                <div class="echo-hero-baner">
                                    <div class="echo-hero-banner-main-img img-transition-scale position-relative">
                                        <a href="{{ $post->url }}">
                                            {{ RvMedia::image($post->image, $post->name, 'thumb-medium', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                    </div>
                                    <div class="content">
                                        {!! Theme::partial('category-badge', compact('post')) !!}

                                        <div class="echo-hero-title font-weight-bold">
                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                        </div>

                                        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share']) !!}
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
