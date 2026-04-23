<section @class(['echo-cl-blocks-area', 'echo-feature-area' => $sidebar])
    @style($variableStyles)
>
    <div class="echo-cl-blocks-content">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                        'col-12' => ! $sidebar,
                    ])
                >
                    <div class="echo-cl-blocks-full-content">
                        @if ($title = $shortcode->title)
                            <div class="echo-home-2-title">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="echo-home-2-main-title">
                                            <h2 class="text-capitalize title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @foreach($posts as $post)
                            <div @class(['row gx-5', 'mb-4' => ! $loop->last])>
                                <div class="col-lg-5">
                                    <div class="echo-cl-blocks-img img-transition-scale position-relative">
                                        <a href="{{ $post->url }}" title="{{ $post->name }}">
                                            {{ RvMedia::image($post->image, $post->name, 'thumb-medium') }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                    </div>
                                </div>

                                <div class="col-lg-7">
                                    <div class="echo-cl-blocks-text">
                                        @if ($category = $post->firstCategory)
                                            <div class="echo-cl-blocks-area-shep">
                                                <div class="home-2-area-shep secondary">
                                                    <p class="text-capitalize">
                                                        <a href="{{ $category->url }}"><span>{{ $category->name }}</span></a>
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="echo-cl-blocks-title">
                                            <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>

                                            @if ($description = $post->description)
                                                <p class="desc">{!! BaseHelper::clean($description) !!}</p>
                                            @endif
                                        </div>

                                        <div class="home-2-read-more-btn">
                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="text-uppercase df-color truncate-custom truncate-2-custom">
                                                {{ __('Read More') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
