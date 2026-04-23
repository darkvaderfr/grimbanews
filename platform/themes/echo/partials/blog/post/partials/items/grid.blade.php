<div class="echo-hero-baner post-item-grid">
    <div class="echo-inner-img-ct-1  img-transition-scale position-relative">
        <a href="{{ $post->url }}">
            {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['width' => '100%', 'height' => '100%']) }}
        </a>

        @if ($category = $post->firstCategory)
            <div class="echo-ct-style-3-shep">
                <p>
                    <a href="{{ $category->url }}" class="truncate-custom truncate-1-custom">{{ $category->name }}</a>
                </p>
            </div>
        @endif
    </div>
    <div class="echo-banner-texting">
        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share text-center justify-content-center']) !!}
        <h4 class="echo-hero-title text-capitalize font-weight-bold text-center">
            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
        </h4>
        @if ($description = $post->description)
            <p class="echo-hero-discription text-center truncate-custom truncate-2-custom">{!! BaseHelper::clean($description) !!}</p>
        @endif
    </div>
</div>
