<div class="echo-new-dishes-area" @style($variableStyles)>
    <div class="hm-9-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @if ($title = $shortcode->title)
                    <div class="hm5-feature-title">
                        <div class="hm-5-main-title text-{{ $shortcode->title_align ?: 'center' }}">
                            <h2>{!! BaseHelper::clean($title) !!}</h2>
                        </div>
                    </div>
                @endif

                <div class="section-inner">
                    <div class="row">
                        @foreach($posts as $post)
                            <div class="col-xl-4 col-lg-4 col-md-6">
                                <div class="echo-hero-baner small">
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
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($posts->count() > 3)
                        <div class="button-area">
                            <a href="{{ get_blog_page_url() }}" class="learn-more-btn">{{ __('See All Posts') }}</a>
                        </div>
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
