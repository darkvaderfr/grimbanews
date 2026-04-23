<section class="echo-business-area area2" @style($variableStyles)>
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
                            <div class="col-xl-3">
                                <div class="echo-hero-baner">
                                    <div class="echo-hero-banner-main-img  img-transition-scale position-relative">
                                        <a href="{{ $post->url }}">
                                            {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'banner-image-one img-hover']) }}
                                        </a>
                                        <div class="tag">
                                            <span>{{ $loop->iteration }}</span>
                                        </div>

                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                    </div>
                                    <div class="content">
                                        <p class="author">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="10" viewBox="0 0 11 10" fill="none">
                                            <path d="M8 1H10C10.1326 1 10.2598 1.05268 10.3536 1.14645C10.4473 1.24021 10.5 1.36739 10.5 1.5V9.5C10.5 9.63261 10.4473 9.75979 10.3536 9.85355C10.2598 9.94732 10.1326 10 10 10H1C0.867392 10 0.740215 9.94732 0.646447 9.85355C0.552678 9.75979 0.5 9.63261 0.5 9.5V1.5C0.5 1.36739 0.552678 1.24021 0.646447 1.14645C0.740215 1.05268 0.867392 1 1 1H3V0H4V1H7V0H8V1ZM7 2H4V3H3V2H1.5V4H9.5V2H8V3H7V2ZM9.5 5H1.5V9H9.5V5Z" fill="currentColor"/>
                                        </svg>
                                        {{ $post->created_at->format('d M Y') }}
                                    </span>/

                                            @if ($category = $post->firstCategory)
                                                <a href="{{ $category->url }}" >
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="10" viewBox="0 0 9 10" fill="none">
                                                    <path d="M1 0H8C8.13261 0 8.25979 0.051036 8.35355 0.141881C8.44732 0.232725 8.5 0.355937 8.5 0.484411V9.75749C8.50006 9.8008 8.48813 9.84333 8.46545 9.88065C8.44277 9.91798 8.41018 9.94873 8.37107 9.9697C8.33196 9.99068 8.28776 10.0011 8.24307 9.99991C8.19839 9.99871 8.15485 9.98592 8.117 9.96288L4.5 7.76511L0.883 9.9624C0.845184 9.98541 0.801694 9.9982 0.757051 9.99942C0.712408 10.0006 0.668243 9.99025 0.629148 9.96933C0.590052 9.94842 0.557453 9.91774 0.534741 9.88048C0.512029 9.84323 0.500033 9.80076 0.5 9.75749V0.484411C0.5 0.355937 0.552679 0.232725 0.646447 0.141881C0.740215 0.051036 0.867392 0 1 0ZM7.5 0.968822H1.5V8.44425L4.5 6.62238L7.5 8.44425V0.968822Z" fill="currentColor"/>
                                                </svg>
                                                {{ $category->name }}
                                            </span>
                                                </a>
                                            @endif
                                        </p>
                                        <div class="echo-hero-title text-capitalize font-weight-bold">
                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-2-custom truncate-custom">{{ $post->name }}</a>
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
