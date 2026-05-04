@php
    $__title = \App\Support\GrimbaTranslationPresenter::title($post);
    $__desc = \App\Support\GrimbaTranslationPresenter::description($post);
    $__isTr = \App\Support\GrimbaTranslationPresenter::isTranslated($post);
@endphp
<div class="echo-hero-baner post-item-list">
    <div class="echo-inner-img-ct-1  img-transition-scale position-relative">
        <a href="{{ $post->url }}">
            {!! Theme::partial('post-hero-img', ['post' => $post, 'size' => 'medium-square']) !!}
        </a>
        @include(Theme::getThemeNamespace('partials.blog.post.partials.action-post'))
        {{-- S173 — save toggle on list cards too --}}
        <div class="position-absolute top-0 end-0 p-2">
            {!! Theme::partial('save-button', ['post' => $post, 'variant' => 'icon']) !!}
        </div>
    </div>
    <div class="echo-banner-texting">
        <h3 class="echo-hero-title text-capitalize font-weight-bold"><a href="{{ $post->url }}" title="{{ $__title }}" class="title-hover truncate-custom truncate-2-custom">{{ $__title }}</a></h3>
        @if($__isTr)
            <div class="mb-2">{!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
            {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share mb-0']) !!}
            {{-- S340: reading-time chip on every card variant. --}}
            {!! Theme::partial('reading-time', ['post' => $post]) !!}
        </div>
        @if ($description = $__desc)
            <p class="echo-hero-discription truncate-custom truncate-3-custom" title="{{ $description }}">{!! BaseHelper::clean($description) !!}</p>
        @endif

        {{-- S136 — coverage bar (compact). Only fires when the
             cluster has ≥2 bias sides, else falls back to Centre/Source. --}}
        {!! Theme::partial('home.coverage-bar', ['post' => $post, 'compact' => true]) !!}
    </div>
</div>
