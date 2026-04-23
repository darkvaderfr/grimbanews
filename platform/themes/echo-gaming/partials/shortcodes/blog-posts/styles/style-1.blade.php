@php
    $firstPost = $posts->shift();
@endphp

<section class="echo-hero-section home-four" @style($variableStyles)>
    <div class="echo-hero">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                    'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                    'col-12' => ! $sidebar,
                ])>
                    <div class="echo-full-hero-content">
                        <div class="row gx-5">
                            <div class="col-xl-8 col-lg-8">
                                <div class="echo-latest-news-main-content">
                                    <div class="echo-latest-news-img img-transition-scale position-relative">
                                        <a href="{{ $firstPost->url }}">
                                            {!! RvMedia::image($firstPost->image, $firstPost->name, 'large', attributes: ['class' => 'img-hover']) !!}
                                        </a>

                                        @if ($category = $firstPost->firstCategory)
                                            <a href="{{ $category->url }}"><span class="content-catagory-tag truncate-custom truncate-1-custom">{{ $category->name }}</span></a>
                                        @endif

                                        {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                    </div>
                                    <div class="content">

                                        <div class="echo-latest-news-single-title">
                                            <a href="{{ $firstPost->url }}" title="{{ $firstPost->name }}" class="text-capitalize title-hover text-heading truncate-custom truncate-2-custom">{{ $firstPost->name }}</a>
                                        </div>
                                        {!! Theme::partial('post-meta', ['post' => $firstPost, 'wrapperClass' => 'echo-latest-news-time-views']) !!}
                                    </div>
                                </div>
                            </div>
                            @if (! empty($posts))
                                <div class="col-xl-4 col-lg-4">
                                    <div class="echo-banner-right-area-content">
                                        <ul>
                                            @foreach($posts as $post)
                                                <li>
                                                    <div class="content">
                                                        <a href="{{ $post->url }}" title="{{ $post->name }}" class="title text-heading text-capitalize title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>

                                                        <p class="desc"><i class="fa-solid fa-calendar"></i>{{ $post->created_at->format('d M Y') }}</p>
                                                    </div>
                                                    <div class="echo-latest-news-img img-transition-scale">
                                                        <a href="{{ $post->url }}">
                                                            {!! RvMedia::image($post->image, $post->name, 'thumb') !!}
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
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
