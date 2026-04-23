<section class="echo-hero-section inner home-six" @style($variableStyles)>
    <div class="echo-hero">
        <div class="hm-6-container">
            <div class="echo-full-hero-content inner-category-1">
                <div class="row gx-5 sticky-coloum-wrap">
                    <div @class([
                        'col-xl-9 col-lg-7 col-md-12' => $sidebar,
                        'col-12' => ! $sidebar,
                    ])>
                        @foreach($posts as $post)
                            <div class="echo-hero-baner">
                                <div class="echo-hero-banner-main-img  img-transition-scale position-relative">
                                    <a href="{{ $post->url }}">
                                        {{ RvMedia::image($post->image, $post->name, attributes: ['class' => 'banner-image-one img-hover']) }}
                                    </a>

                                    {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                </div>
                                <div class="content">
                                    @if (class_exists($post->author_type) && ($author = $post->author))
                                        <a href="{{ $author->url }}"><p class="author">{{ __('By :name / :date', ['name' => $author->name, 'date' =>  $post->created_at->format('d M Y')]) }}</p></a>
                                    @endif
                                    <div class="echo-hero-title text-capitalize font-weight-bold">
                                        <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-2-custom">{{ $post->name }}</a>
                                    </div>

                                    @if ($description = $post->description)
                                        <p class="echo-hero-discription truncate-custom truncate-2-custom">{!! BaseHelper::clean($description) !!}</p>
                                    @endif
                                    <div class="hero-hm6-btn">
                                        <a href="{{ $post->url }}" class="hm6-btn-1"> {{ __('Read More ') }}
                                            {!! Theme::partial('icon-button') !!}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
        </div>
    </div>
</section>
