@php
    $postsChunk = $posts->chunk(5);
@endphp

<section class="echo-feature-area home-four" @style($variableStyles)>
    <div class="echo-feature-area-content">
        <div class="container">
            <div class="echo-feature-area-full-content">
                <div class="row">
                    <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $sidebar,
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

                            @foreach($postsChunk as $postsList)
                                @continue($postsList->isEmpty())
                                <div class="row">
                                    <div class="col-lg-5">
                                        <div class="echo-banner-right-area-content">
                                            <ul>
                                                @foreach($postsList->skip(1) as $post)
                                                    <li>
                                                        <div class="echo-latest-news-img img-transition-scale">
                                                            <a href="{{ $post->url }}">
                                                                {!! RvMedia::image($post->image, $post->name, 'thumb') !!}
                                                            </a>
                                                        </div>
                                                        <div class="content">
                                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title text-capitalize title-hover truncate-custom truncate-2-custom text-heading">{{ $post->name }}</a>
                                                            <p class="desc"><i class="fa-solid fa-calendar"></i>{{ $post->created_at->format('d M Y') }}</p>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    @php $firstPost = $postsList->first() @endphp
                                    <div class="col-lg-7">
                                        <div class="echo-latest-news-main-content">
                                            <div class="echo-latest-news-img img-transition-scale position-relative">
                                                <a href="{{ $firstPost->url }}">
                                                    {{ RvMedia::image($firstPost->image, $firstPost->name, 'thumb-medium', attributes: ['class' => 'img-hover']) }}
                                                </a>

                                                @if ($category = $firstPost->firstCategory)
                                                    <a href="{{ $category->url }}"><span class="content-catagory-tag truncate-custom truncate-2-custom">{{ $category->name }}</span></a>
                                                @endif

                                                {!! Theme::partial('blog.post.partials.action-post', ['post' => $post]) !!}
                                            </div>
                                            <div class="echo-latest-news-single-title">
                                                <a href="{{ $firstPost->url }}" title="{{ $firstPost->name }}" class="text-capitalize title-hover truncate-custom truncate-2-custom text-heading">{{ $firstPost->name }}</a>
                                            </div>

                                            {!! Theme::partial('post-meta', ['post' => $firstPost, 'wrapperClass' => 'echo-latest-news-time-views']) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>

                    @if ($sidebar)
                        <div class="col-xl-4 col-lg-5 col-md-12">
                            <div class="echo-feature-area-right-site-flexing right-side-inner">
                                {!! dynamic_sidebar($sidebar) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
