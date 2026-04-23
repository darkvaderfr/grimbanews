<section class="echo-trending-news-area home-seven" @style($variableStyles)>
    <div class="hm-7-container">
        <div class="section-inner">
            <div class="row">
                <div @class([
                    'col-xl-9 col-lg-9"' => $sidebar = $shortcode->sidebar,
                    'col-12' => ! $sidebar,
                ])>
                    <div class="left-side-inner">
                        @if ($title = $shortcode->title)
                            <div class="hm5-feature-title">
                                @php $titleAlign = $shortcode->title_align @endphp
                                <div @class(['hm-5-main-title', 'text-center' => $titleAlign == 'center', 'text-end' => $titleAlign == 'end'])>
                                    <h2>{!! BaseHelper::clean($title) !!}</h2>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            @foreach($posts as $post)
                                <div class="col-xl-4 col-lg-6 col-md-6">
                                    <div class="echo-hero-baner small">
                                        <div class="echo-hero-banner-main-img img-transition-scale position-relative">
                                            <a href="{{ $post->url }}">
                                                {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'banner-image-one img-hover']) }}
                                            </a>

                                            {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                        </div>
                                        <div class="content">
                                            {!! Theme::partial('category-badge', compact('post')) !!}
                                            <div class="echo-hero-title font-weight-bold">
                                                <a href="{{ $post->url }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if ($sidebar)
                    <div class="col-xl-3 col-lg-3">
                        <div class="right-side-inner">
                            {!! dynamic_sidebar($sidebar) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
