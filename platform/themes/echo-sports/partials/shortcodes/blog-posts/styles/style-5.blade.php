@php
    $rows = $posts->chunk(4);
@endphp

<section class="hm5-feature-area area3" @style($variableStyles)>
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
                                    <a href="{{ get_blog_page_url() }}" class="hm5-btn-1">{{ __('SEE ALL POSTS') }}
                                        {!! Theme::partial('icon-button') !!}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="hm5-feature-content">
                    @foreach($rows as $posts)
                        <div class="row gx-5">
                            @if ($loop->odd)
                                @foreach($posts as $post)
                                    <div class="col-xl-3 col-lg-6 col-md-6">
                                        {!! Theme::partial('shortcodes.blog-posts.partials.post-item', ['post' => $post, 'styleButtonIcon' => 'style-2']) !!}
                                    </div>
                                @endforeach
                            @else
                                @php $firstPost = $posts->first(); @endphp

                                @if ($firstPost)
                                    <div class="col-xl-6 col-lg-12 col-md-12">
                                        {!! Theme::partial('shortcodes.blog-posts.partials.post-item', ['post' => $firstPost, 'sizeImage' => 'extra-large', 'wrapperClass' => 'large', 'styleButtonIcon' => 'style-2']) !!}
                                    </div>
                                @endif

                                <div class="col-xl-6 col-lg-12 col-md-12">
                                    @foreach($posts->skip(1) as $post)
                                        {!! Theme::partial('shortcodes.blog-posts.partials.post-item', [
                                                'post' => $post,
                                                'wrapperClass' => 'small mb-3',
                                                'sizeImage' => 'small',
                                                'styleButtonIcon' => 'style-2',
                                              ]) !!}
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
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
