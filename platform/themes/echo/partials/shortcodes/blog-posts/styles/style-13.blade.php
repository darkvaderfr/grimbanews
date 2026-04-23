<section class="echo-popular-news-area home-four" @style($variableStyles)>
    <div class="echo-popular-news-area-content">
        <div class="container">
            <div class="echo-popular-news-area-full-content">
                <div class="row">
                    <div @class([
                        'col-xl-8 col-lg-8"' => $sidebar = $shortcode->sidebar,
                        'col-12' => ! $sidebar,
                    ])>
                        <div class="row">
                            @if ($title = $shortcode->title)
                                <div class="col-lg-12">
                                    <div class="title-area">
                                        <h2 class="sub-title title-has-icon title-align-{{ $shortcode->title_align ?: 'start' }}">
                                            <svg class="shape-title" width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="22.5388" cy="22.5388" r="22.5388" fill="var(--primary-color)"/>
                                                <path d="M23.4131 21.0815H29.5335L21.6644 32.448V24.5789H15.5439L23.4131 13.2123V21.0815Z" fill="white"/>
                                            </svg>
                                            {!! BaseHelper::clean($title) !!}
                                        </h2>
                                    </div>
                                </div>
                            @endif
                            <div class="col-lg-12">
                                <div class="left-side-content">
                                    <div class="row">
                                        @foreach($posts as $post)
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                <div class="echo-latest-news-main-content">
                                                    <div class="echo-latest-news-img img-transition-scale position-relative">
                                                        <a href="{{ $post->url }}">
                                                            {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                                        </a>

                                                        {!! Theme::partial('category-badge', ['post' => $post]) !!}

                                                        {!! Theme::partial('blog.post.partials.action-post', ['post' => $post]) !!}
                                                    </div>
                                                    <div class="echo-latest-news-single-title">
                                                        <a href="{{ $post->url }}" title="{{ $post->name }}" class="text-capitalize text-heading title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                    </div>

                                                    {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-latest-news-time-views']) !!}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
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
