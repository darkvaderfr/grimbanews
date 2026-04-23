<section class="echo-latest-news-area home-eight" @style($variableStyles)>
    <div class="hm-7-container">
        <div class="echo-full-hero-content inner-category-1">
            <div class="row gx-5 sticky-coloum-wrap">
                <div @class([
                    'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                    'col-12' => ! $sidebar,
                ])>
                    <div class="top-area">
                        @if ($title = $shortcode->title)
                            <div class="hm5-feature-title">
                                <div class="hm-5-title-btn">
                                    <div class="hm-5-main-title gap-2 justify-content-{{ $shortcode->title_align ?: 'start' }}">
                                        <img src="{{ Theme::asset()->url('images/shape-title.svg') }}" alt="title shape">
                                        <h2>{!! BaseHelper::clean($title) !!}</h2>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-xl-12">
                                @foreach($posts as $post)
                                    <div class="echo-hero-baner">
                                        <div class="echo-hero-banner-main-img img-transition-scale position-relative">
                                            <a href="{{ $post->url }}">
                                                {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                            </a>

                                            {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                        </div>
                                        <div class="content">
                                            {!! Theme::partial('category-badge', compact('post')) !!}
                                            <div class="echo-hero-title font-weight-bold">
                                                <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-2-custom truncate-custom">{{ $post->name }}</a>
                                            </div>

                                            {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share']) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @if ($sidebar)
                    <div class="col-xl-4 col-lg-5 col-md-12 sticky-coloum-item">
                        {!! dynamic_sidebar($sidebar) !!}
                    </div>
                @endif
            </div>
            <div class="button-area">
                <a href="{{ get_blog_page_url() }}" class="learn-more-btn">{{ __('View More') }}</a>
            </div>
        </div>
    </div>
</section>
