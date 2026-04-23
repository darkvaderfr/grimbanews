@php
    $rows = $posts->chunk(3);
@endphp

<section class="hm5-feature-area area3 home-six" @style($variableStyles)>
    <div class="hm-5-container">
        <div class="row gx-5">
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
                   </div>
                @endif
                <div class="hm5-feature-content">
                    @foreach($rows as $posts)
                        <div class="row gx-5">
                            @if ($loop->odd)
                                @foreach($posts as $post)
                                    <div class="col-xl-4 col-lg-4 col-md-12">
                                        <div class="hm5-featurea-main-content">
                                            <div class="hm5-feature-img img-transition-scale position-relative">
                                                <a href="{{ $post->url }}">
                                                    {{ RvMedia::image($post->image, $post->name) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                            </div>
                                            <div class="content">
                                                @if (class_exists($post->author_type) && ($author = $post->author))
                                                    <a href="{{ $author->url }}">
                                                        <p class="author" href="{{ $author->url }}">{{ __('By :name / :date', ['name' => $author->name, 'date' =>  $post->created_at->format('d M Y')]) }}</p>
                                                    </a>
                                                @endif
                                                <div class="echo-hero-title text-capitalize font-weight-bold mb-3">
                                                    <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                </div>
                                                <div class="hero-hm6-btn">
                                                    <a href="{{ $post->url }}" class="hm6-btn-1 small">{{ __('Read More') }}
                                                        {!! Theme::partial('icon-button') !!}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                @php
                                    $firstPost = $posts->first();
                                @endphp

                                @if ($firstPost)
                                    <div class="col-xl-7 col-lg-6 col-md-12">
                                        <div class="hm5-featurea-main-content large">
                                            <div class="hm5-feature-img img-transition-scale position-relative">
                                                <a href="{{ $firstPost->url }}">
                                                    {{ RvMedia::image($firstPost->image, $firstPost->name, 'large') }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                            </div>
                                            <div class="content">
                                                @if (class_exists($firstPost->author_tyoe) && ($author = $firstPost->author))
                                                    <a href="{{ $author->url }}">
                                                        <p class="author" href="{{ $author->url }}">{{ __('By :name / :date', ['name' => $author->name, 'date' =>  $firstPost->created_at->format('d M Y')]) }}</p>
                                                    </a>
                                                @endif

                                                <div class="echo-hero-title text-capitalize font-weight-bold mb-3">
                                                    <a href="{{ $firstPost->url }}" title="{{ $firstPost->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $firstPost->name }}</a>
                                                </div>

                                            <div class="hero-hm6-btn">
                                                <a href="{{ $firstPost->url }}" class="hm6-btn-1 small">{{ __('Read More') }}
                                                    {!! Theme::partial('icon-button') !!}
                                                </a>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-xl-5 col-lg-6 col-md-12">
                                    <div class="echo-home-1-hero-area-top-story">
                                        @foreach($posts->skip(1) as $post)
                                            <div @class(['echo-top-story', 'last' => $loop->last])>
                                                <div class="echo-story-picture img-transition-scale position-relative">
                                                    <a href="{{ $post->url }}">
                                                        {{ RvMedia::image($post->image, $post->name, 'medium-square', attributes: ['class' => 'img-hover']) }}
                                                    </a>

                                                    {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                                </div>
                                                <div class="echo-story-text">
                                                    @if (class_exists($post->author_type) && ($author = $post->author))
                                                        <a href="{{ $author->url }}" class="pe-none">{{ __('By :name / :date', ['name' => $author->name, 'date' =>  $post->created_at->format('d M Y')]) }}</a>
                                                    @endif
                                                    <h6><a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-3-custom">{{ $post->name }}</a></h6>
                                                    <a href="{{ $post->url }}" class="read-more-btn">{{ __('Read More') }}
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" viewBox="0 0 16 12" fill="none">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M11.7546 4.53689C10.7978 3.44443 10.234 2.05146 10.0761 0.804873L11.3561 0.646973C11.4849 1.66356 11.953 2.81664 12.7305 3.70445C13.4952 4.57761 14.5393 5.17634 15.8587 5.1815C15.8645 5.18148 15.8702 5.18147 15.876 5.18147V6.4545L15.866 6.45455H15.856C14.5787 6.46154 13.5409 7.13803 12.7609 8.17715C11.9711 9.22919 11.4903 10.6031 11.3575 11.8266L10.0748 11.691C10.2287 10.2741 10.81 8.45798 11.7546 7.1997C12.0163 6.8511 12.0816 6.74444 12.4098 6.45456L0.396484 6.45456L0.396484 5.18152L12.4098 5.18152C12.1736 4.98221 11.955 4.76577 11.7546 4.53689Z" fill="currentColor" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($sidebar)
                <div class="col-xl-3 col-lg-5 col-md-12 sticky-coloum-item">
                    <div class="echo-feature-area-right-site-flexing echo-right-ct-1">
                        {!! dynamic_sidebar($sidebar) !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
