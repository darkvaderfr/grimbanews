<section class="hm5-feature-area" @style($variableStyles)>
    <div class="hm-5-container">
        <div class="row gx-6">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @if ($title = $shortcode->title)
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <div class="hm5-feature-title">
                                <div class="hm-5-title-btn">
                                    <div class="hm-5-main-title">
                                        <h2 class="title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                    </div>

                                    <div class="hm5-area-title-btn">
                                    <a href="{{ get_blog_page_url() }}" class="hm5-btn-1">{{ __('SEE ALL POSTS ') }}
                                        {!! Theme::partial('icon-button') !!}
                                    </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="hm5-feature-content">
                    <div class="row gx-5">
                        @foreach($posts as $post)
                            <div class="col-xl-3 col-lg-6 col-md-6 mb-5">
                                {!! Theme::partial('shortcodes.blog-posts.partials.post-item', compact('post')) !!}
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
</section>
