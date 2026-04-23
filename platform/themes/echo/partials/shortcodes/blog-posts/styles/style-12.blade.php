<section class="echo-feature-area home-three" @style($variableStyles)>
    @if ($shortcode->background_image)
        <div style="position: absolute; background-image: url({{ RvMedia::url($shortcode->background_image) }}); background-repeat: no-repeat; background-size: cover; bottom: 0; height: 100%; width: 100%; z-index: -1;"></div>
    @endif

    <div class="echo-feature-area-content">
        <div class="container">
            @if ($title = $shortcode->title)
                <h2 @class([
                    'section-title',
                    'text-center' => $shortcode->title_align == 'center',
                    'text-end' => $shortcode->title_align == 'end',
                    'text-start' => $shortcode->title_align == 'start',
                ])>
                    {!! BaseHelper::clean($title) !!}
                </h2>
            @endif
            <div class="echo-feature-full-content">
                <div class="row gx-6">
                    <div @class([
                        'col-xl-8 col-lg-8"' => $sidebar = $shortcode->sidebar,
                        'col-12' => ! $sidebar,
                    ])>
                        <div class="echo-feature-area-site-title">
                            <div class="echo-feature-area-last-news">
                                <div class="row gy-5">
                                    @foreach($posts as $post)
                                        <div class="col-lg-12">
                                            <div class="echo-feature-area-last-content">
                                                <div class="echo-feature-area-last-content-img img-transition-scale position-relative">
                                                    <a href="{{ $post->url }}">
                                                        {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                                    </a>

                                                    {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                                </div>
                                                <div class="echo-feature-area-last-content-text">
                                                    @if ($category = $post->firstCategory)
                                                        {!! Theme::partial('category-badge', compact('category')) !!}
                                                    @endif
                                                    <a href="{{ $post->url }}" title="{{ $name = $post->name }}" class="title-hover text-heading text-capitalize truncate-custom truncate-3-custom">{{ $name }}</a>
                                                    {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-feature-area-last-content-read-view']) !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
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
    </div>
</section>
