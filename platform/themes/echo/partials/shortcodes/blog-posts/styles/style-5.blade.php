<section class="echo-feature-area" @style($variableStyles)>
    <div class="echo-feature-area-content">
        <div class="container">
            <div class="echo-feature-full-content">
                <div class="row gx-6">
                    <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar = $shortcode->sidebar,
                        'col-12' => ! $sidebar,
                    ])>
                        <div class="echo-feature-area-site-title">
                            @if ($title = $shortcode->title)
                                <h2 class="title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                            @endif

                            <div class="row gx-5">
                                @foreach($posts as $post)
                                    @if ($loop->iteration <= 2)
                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                            <div class="echo-feature-area-post">
                                                <div class="echo-feature-area-post-img img-transition-scale position-relative">
                                                    <a href="{{ $post->url }}">
                                                        {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                                    </a>

                                                    {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                                </div>
                                                <div class="echo-feature-area-post-hins">
                                                    <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                </div>
                                                <hr>

                                                {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-feature-area-read-view']) !!}
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <div class="echo-feature-area-option">
                                <div class="row gy-5">
                                    @foreach($posts as $post)
                                        @if ($loop->iteration > 2 && $loop->iteration <= 6)
                                            <div class="col-xl-6 col-lg-6 col-md-6">
                                                <div class="echo-feature-area-option-content">
                                                    <div class="echo-feature-area-option-number">
                                                        <h3>{{ $loop->index + 1 }}</h3>
                                                    </div>
                                                    <div class="echo-feature-area-option-content-text">
                                                        <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>

                                                        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-feature-area-option-read-more']) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="echo-feature-area-last-news">
                                <div class="row gx-5 gy-5">
                                    @foreach($posts as $post)
                                        @if ($loop->iteration > 6)
                                            <div class="col-xl-12">
                                                <div class="echo-feature-area-last-content">
                                                    <div class="echo-feature-area-last-content-img img-transition-scale">
                                                        <a href="{{ $post->url }}">
                                                            {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                                        </a>
                                                    </div>
                                                    <div class="echo-feature-area-last-content-text">
                                                        <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-3-custom">{{ $post->name }}</a>

                                                        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-feature-area-last-content-read-view']) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
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
    </div>
</section>
