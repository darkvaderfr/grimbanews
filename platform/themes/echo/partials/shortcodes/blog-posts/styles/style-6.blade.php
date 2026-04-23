<section @class(['echo-popular-news-area', 'echo-feature-area' => $sidebar])
    @style($variableStyles)
>
    <div class="echo-popular-news-area-content">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                    'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                    'col-12' => ! $sidebar,
                ])>
                    <div class="echo-popular-news-area-full-content">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                                @if ($title = $shortcode->title)
                                    <div class="echo-popular-area-title">
                                        <h2 class="text-capitalize title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row gx-5">
                            @foreach($posts as $post)
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                    <div class="echo-popular-area-single-item">
                                        <div class="echo-popular-area-img img-transition-scale position-relative">
                                            <a href="{{ $post->url }}">
                                                {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                            </a>
                                            {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                        </div>
                                        <div class="echo-popular-area-item-title">
                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="text-center text-capitalize title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                        </div>

                                        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-popular-area-read-view text-center']) !!}
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
