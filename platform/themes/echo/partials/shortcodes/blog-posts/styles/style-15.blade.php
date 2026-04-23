<section class="echo-latest-news-area home-seven" @style($variableStyles)>
    <div class="hm-7-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @if ($title = $shortcode->title)
                    <div class="hm5-feature-title">
                        <div class="hm-5-main-title text-{{ $shortcode->title_align ?: 'center' }}">
                            <h2>{!! BaseHelper::clean($title) !!}</h2>
                        </div>
                    </div>
                @endif
                <div class="section-inner">
                    <div class="row">
                        @foreach($posts as $post)
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="echo-hero-baner small">
                                    <div class="echo-hero-banner-main-img">
                                        <a href="{{ $post->url }}">
                                            {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'banner-image-one']) }}
                                        </a>
                                    </div>
                                    <div class="content">
                                        {!! Theme::partial('category-badge', compact('post')) !!}

                                        <div class="echo-hero-title font-weight-bold">
                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12">
                    {!! dynamic_sidebar($sidebar) !!}
                </div>
            @endif
        </div>
    </div>
</section>
