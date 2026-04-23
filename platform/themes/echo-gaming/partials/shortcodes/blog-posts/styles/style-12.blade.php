<section class="echo-feature-area area-2" @style($variableStyles)>
    <div class="echo-feature-area-content">
        <div class="container">
            <div class="echo-feature-full-content">
                <div class="row gx-6">
                    <div @class([
                        'col-xl-8 col-lg-8"' => $sidebar = $shortcode->sidebar,
                        'col-12' => ! $sidebar,
                    ])>
                        <div class="row">
                            @if ($title = $shortcode->title)
                                <div class="col-lg-12">
                                    <div class="title-area">
                                        <h2 class="sub-title title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                    </div>
                                </div>
                            @endif
                            <div class="col-lg-12">
                                <div class="echo-feature-area-site-title">
                                    <div class="echo-feature-area-last-news">
                                        <div class="row gy-5">
                                            @foreach($posts as $post)
                                                <div class="col-xl-12">
                                                    <div class="echo-feature-area-last-content">
                                                        <div class="echo-feature-area-last-content-img img-transition-scale">
                                                            <a href="{{ $post->url }}">
                                                                {{ RvMedia::image($post->image, $post->name, 'medium-square', attributes: ['class' => 'img-hover']) }}
                                                            </a>
                                                        </div>
                                                        <div class="echo-feature-area-last-content-text">
                                                            <span class="content-catagory-tag">Game Guides</span>
                                                            {!! Theme::partial('category-badge', compact('posts')) !!}
                                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover text-capitalize text-heading truncate-custom truncate-2-custom">{{ $post->name }}</a>

                                                            {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-latest-news-time-views']) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
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
