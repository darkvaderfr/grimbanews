<section class="echo-latest-news-area home-three"  @style($variableStyles)>
    <div class="echo-latest-news-content">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                        'col-12' => ! $sidebar,
                    ])
                >
                    @if ($title = $shortcode->title)
                        <h2 @class([
                            'subtitle text-heading mb-0',
                            'text-center' => $shortcode->title_align == 'center',
                            'text-end' => $shortcode->title_align == 'end',
                        ])>
                            {!! BaseHelper::clean($title) !!}<span>/</span>
                        </h2>
                    @endif
                    <div class="echo-latest-news-full-content">
                        <div class="row">
                            @foreach($posts as $post)
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="echo-latest-news-main-content">
                                        <div class="echo-latest-news-img img-transition-scale position-relative">
                                            <a href="{{ $post->url }}">
                                                {{ RvMedia::image($post->image, $post->name ,'small', attributes: ['class' => 'img-hover']) }}
                                            </a>
                                            @if ($category = $post->firstCategory)
                                                {!! Theme::partial('category-badge', compact('category')) !!}
                                            @endif

                                            {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                        </div>
                                        <div class="echo-latest-news-single-title">
                                            <a href="{{ $post->url }}" title="{{ $name = $post->name }}" class="text-capitalize title-hover truncate-custom truncate-2-custom">{{ $name }}</a>
                                        </div>

                                        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-latest-news-time-views']) !!}
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
