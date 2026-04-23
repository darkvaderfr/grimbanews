<div class="echo-dishes-area" @style($variableStyles)>
    <div class="hm-9-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                @foreach($posts as $post)
                    <div class="section-inner" @style(['flex-direction: row-reverse;' => $loop->even])>
                        <div class="content-area">
                            <div class="echo-hero-baner">
                                <div class="content">
                                    {!! Theme::partial('category-badge', compact('post')) !!}
                                    <div class="echo-hero-title font-weight-bold">
                                        <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-3-custom">{{ $post->name }}</a>
                                    </div>
                                    <a href="{{ $post->url }}" class="read-more-btn">{{ __('Read More') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="image-area position-relative">
                            {{ RvMedia::image($post->image, $post->name, 'extra-large') }}
                            {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12">
                    {!! dynamic_sidebar($sidebar) !!}
                </div>
            @endif
        </div>
    </div>
</div>
