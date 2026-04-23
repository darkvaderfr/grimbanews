<section class="echo-business-area area3" @style($variableStyles)>
    <div class="hm-7-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="hm5-feature-title">
                        <div class="hm-5-title-btn">
                            @if ($title = $shortcode->title)
                                @php $titleAlign = $shortcode->title_align; @endphp
                                <div class="hm-5-main-title w-100">
                                    <h2 @class(['text-center' => $titleAlign == 'center', 'text-end' => $titleAlign == 'end'])>
                                        {!! BaseHelper::clean($title) !!}
                                    </h2>
                                </div>
                            @endif
                            <a href="{{ get_blog_page_url() }}" class="see-all-btn" style="flex-shrink: 0">{{ __('See All') }} <i class="fa-regular fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="section-inner">
                    <div class="row">
                        @foreach($posts as $post)
                            <div class="col-xl-4">
                                <div class="echo-hero-baner">
                                    <div class="echo-hero-banner-main-img img-transition-scale position-relative">
                                        <a href="{{ $post->url }}">
                                            {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'banner-image-one img-hover']) }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                    </div>
                                    <div class="content">
                                        <div class="echo-hero-title text-capitalize font-weight-bold">
                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                        </div>
                                        <a href="{{ $post->url }}" class="listen-now-btn">
                                            {{ __('Read More') }} <i class="fa-regular fa-arrow-right"></i>
                                        </a>
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
