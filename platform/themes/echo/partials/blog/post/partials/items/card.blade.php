<div class="echo-hero-baner post-item-card {{ $classWrapper ?? null }}">
    <div class="echo-inner-img-ct-1  img-transition-scale position-relative">
        <a href="{{ $post->url }}">
            {{ RvMedia::image($post->image, $post->name, 'large') }}
        </a>
        @include(Theme::getThemeNamespace('partials.blog.post.partials.action-post'))
    </div>
    <div class="echo-hero-baner-text-heading-info-ct-1">
        <h2 class="echo-hero-title text-capitalize font-weight-bold"><a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-3-custom">{{ $post->name }}</a></h2>

        {!! Theme::partial('post-meta', [
            'post' => $post,
            'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share post-meta'
        ]) !!}

        @if ($description = $post->description)
            <p class="echo-hero-discription truncate-custom truncate-3-custom" title="{{ $description }}">{!! BaseHelper::clean($description) !!}</p>
        @endif
    </div>
</div>
