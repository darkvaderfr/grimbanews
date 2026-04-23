<section class="echo-latest-news-area home-four" @style($variableStyles)>
    <div class="echo-latest-news-content">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                    'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                    'col-12' => ! $sidebar,
                ])>
                    <div class="title-area">
                        @if ($title = $shortcode->title)
                            <h2 class="sub-title title-align-{{ $shortcode->title_align ?: 'start' }} title-has-icon">
                                <svg class="shape-title" width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="22.5388" cy="22.5388" r="22.5388" fill="var(--primary-color)"/>
                                    <path d="M23.4131 21.0815H29.5335L21.6644 32.448V24.5789H15.5439L23.4131 13.2123V21.0815Z" fill="white"/>
                                </svg>

                                {!! BaseHelper::clean($title) !!}
                            </h2>
                        @endif
                        <a href="{{ get_blog_page_url() }}" class="rts-btn see-all-btn text-heading"> {{ __('See All') }} <i class="fa-light fa-arrow-right"></i></a>
                    </div>
                    <div class="echo-latest-news-full-content">
                        <div class="row">
                            @foreach($posts as $post)
                                <div class="col-lg-4 col-md-6 col-sm-6 mb-5">
                                    <div class="echo-latest-news-main-content">
                                        <div class="echo-latest-news-img img-transition-scale position-relative">
                                            <a href="{{ $post->url }}">
                                                {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                            </a>

                                            @if ($category = $post->firstCategory)
                                                <a href="{{ $category->url }}"><span class="content-catagory-tag truncate-custom truncate-1-custom">{{ $category->name }}</span></a>
                                            @endif

                                            {!! Theme::partial('blog.post.partials.action-post', ['post' => $post]) !!}
                                        </div>
                                        <div class="echo-latest-news-single-title">
                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="text-capitalize title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
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
