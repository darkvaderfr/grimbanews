@php
    $rows = $posts->chunk(4);
@endphp

<section class="echo-video-area home-six" @style($variableStyles)>
    <div class="hm-6-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @if ($title = $shortcode->title)
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="hm5-feature-title">
                            <div class="hm-5-title-btn">
                                <div class="hm-5-main-title">
                                    <h2 class="title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                                </div>
                                <div class="hero-hm6-btn">
                                    <a href="{{ get_blog_page_url() }}" class="hm6-btn-1">{{ __('See all posts') }}
                                        <svg width="20" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path id="Union" fill-rule="evenodd" clip-rule="evenodd" d="M14.4195 5.03445C13.2415 3.68932 12.5473 1.97418 12.3529 0.439292L13.9289 0.244873C14.0875 1.49657 14.6639 2.91634 15.6212 4.00949C16.5628 5.08458 17.8483 5.82178 19.4729 5.82814C19.48 5.82811 19.4871 5.8281 19.4941 5.8281V7.39556L19.4819 7.39563H19.4695C17.8969 7.40423 16.619 8.23718 15.6586 9.51662C14.6862 10.812 14.0941 12.5037 13.9306 14.0101L12.3513 13.8431C12.5407 12.0986 13.2565 9.8624 14.4195 8.3131C14.7417 7.88388 14.8223 7.75256 15.2264 7.39564L0.43457 7.39563L0.43457 5.82817L15.2264 5.82817C14.9354 5.58276 14.6663 5.31626 14.4195 5.03445Z" fill="#2B2004"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="section-inner">
                    @foreach($rows as $posts)
                        @php $firstPost = $posts->first(); @endphp
                        <div class="row mb-5">
                            @if ($firstPost)
                                <div class="col-xl-7 col-lg-6">
                                    <div class="echo-hero-baner">
                                        <div class="echo-hero-banner-main-img  img-transition-scale position-relative">
                                            <a href="{{ $firstPost->url }}">
                                                {{ RvMedia::image($firstPost->image, $firstPost->name, 'large', attributes: ['class' => 'banner-image-one img-hover']) }}
                                            </a>

                                            {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                        </div>
                                        <div class="content">
                                            @if (class_exists($firstPost->author_tyoe) && ($author = $firstPost->author))
                                                <a href="{{ $author->url }}">
                                                    <p class="author mb-5">{{ __('By :name / :date', ['name' => $author->name, 'date' =>  $firstPost->created_at->format('d M Y')]) }}</p>
                                                </a>
                                            @endif
                                            <div class="echo-hero-title text-capitalize font-weight-bold mb-3">
                                                <a href="{{ $firstPost->url }}" class="title-hover truncate-custom truncate-2-custom">{{ $firstPost->name }}</a>
                                            </div>
                                            <div class="hero-hm6-btn">
                                                <a href="{{ $firstPost->url }}" class="hm6-btn-1">{{ __('Read More') }}
                                                    {!! Theme::partial('icon-button') !!}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-xl-5 col-lg-6">
                                <div class="echo-home-1-hero-area-top-story">
                                    @foreach($posts->skip(1) as $post)
                                        <div @class(['echo-top-story', 'last' => $loop->last])>
                                            <div class="echo-story-picture img-transition-scale position-relative">
                                                <a href="{{ $firstPost->url }}">
                                                    {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                            </div>
                                            <div class="echo-story-text">
                                                @if (class_exists($post->author_type) && ($author = $post->author))
                                                    <a href="{{ $author->url }}">
                                                        <span class="pe-none" href="{{ $author->url }}">{{ __('By :name / :date', ['name' => $author->name, 'date' =>  $post->created_at->format('d M Y')]) }}</span>
                                                    </a>
                                                @endif
                                                <h6><a href="{{ $post->url }}" class="title-hover truncate-custom truncate-3-custom">{{ $post->name }}</a></h6>
                                                <a href="{{ $post->url }}" class="read-more-btn">{{ __('Read More') }}
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="13" viewBox="0 0 16 13" fill="none">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.7546 4.87625C10.7978 3.78379 10.234 2.39081 10.0761 1.14423L11.3561 0.986328C11.4849 2.00291 11.953 3.156 12.7305 4.04381C13.4952 4.91697 14.5393 5.51569 15.8587 5.52085C15.8645 5.52083 15.8702 5.52082 15.876 5.52082V6.79386L15.866 6.79391H15.856C14.5787 6.8009 13.5409 7.47739 12.7609 8.51651C11.9711 9.56854 11.4903 10.9425 11.3575 12.166L10.0748 12.0303C10.2287 10.6135 10.81 8.79733 11.7546 7.53905C12.0163 7.19045 12.0816 7.0838 12.4098 6.79392L0.396484 6.79391L0.396484 5.52088L12.4098 5.52088C12.1736 5.32156 11.955 5.10512 11.7546 4.87625Z" fill="currentColor" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12">
                    <div class="echo-feature-area-right-site-flexing echo-right-ct-1">
                        {!! dynamic_sidebar($sidebar) !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
