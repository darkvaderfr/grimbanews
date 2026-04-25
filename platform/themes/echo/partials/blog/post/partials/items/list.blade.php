<div class="echo-hero-baner post-item-list">
    <div class="echo-inner-img-ct-1  img-transition-scale position-relative">
        <a href="{{ $post->url }}">
            {!! Theme::partial('post-hero-img', ['post' => $post, 'size' => 'medium-square']) !!}
        </a>
        @include(Theme::getThemeNamespace('partials.blog.post.partials.action-post'))
    </div>
    <div class="echo-banner-texting">
        <h3 class="echo-hero-title text-capitalize font-weight-bold"><a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a></h3>

        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share']) !!}
        @if ($description = $post->description)
            <p class="echo-hero-discription truncate-custom truncate-3-custom" title="{{ $description }}">{!! BaseHelper::clean($description) !!}</p>
        @endif

        {{-- S136 — coverage bar (compact). Only fires when the
             cluster has ≥2 bias sides, else falls back to Centre/Source. --}}
        {!! Theme::partial('home.coverage-bar', ['post' => $post, 'compact' => true]) !!}
    </div>
</div>
