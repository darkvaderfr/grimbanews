@php
    $__title = \App\Support\GrimbaTranslationPresenter::title($post);
    $__desc  = \App\Support\GrimbaTranslationPresenter::description($post);
    $__isTr = \App\Support\GrimbaTranslationPresenter::isTranslated($post);
@endphp
<article class="article-card {{ $classWrapper ?? null }}">
    <div class="article-card__image">
        <a href="{{ $post->url }}">
            {!! Theme::partial('post-hero-img', ['post' => $post, 'size' => 'large']) !!}
        </a>

        {{-- Bias Badge --}}
        <div class="position-absolute top-0 end-0 p-2">
            {!! Theme::partial('bias-badge', [
                'bias' => $post->bias_rating ?? null,
                'showLabel' => false,
                'size' => 'sm'
            ]) !!}
        </div>

        {{-- Blindspot Badge (if article is covered by only one side) --}}
        @if($post->is_blindspot ?? false)
            <div class="position-absolute top-0 start-0 p-2">
                <span class="blindspot-badge" title="Story covered by only one side">
                    Blindspot
                </span>
            </div>
        @endif

        @include(Theme::getThemeNamespace('partials.blog.post.partials.action-post'))
    </div>
    <div class="article-card__content">
        {{-- Category + Bias Row --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            @include(Theme::getThemeNamespace('partials.category-badge'), ['post' => $post])
            {!! Theme::partial('bias-badge', [
                'bias' => $post->bias_rating ?? null,
                'showLabel' => true,
                'size' => 'sm'
            ]) !!}
        </div>

        <h2 class="article-card__title">
            <a href="{{ $post->url }}" title="{{ $__title }}" class="title-hover text-decoration-none">
                {{ $__title }}
            </a>
        </h2>
        @if($__isTr)
            <div class="mb-2">{!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}</div>
        @endif

        {{-- Description --}}
        @if ($description = $__desc)
            <p class="echo-hero-discription truncate-custom truncate-3-custom mb-2" title="{{ $description }}">
                {!! BaseHelper::clean($description) !!}
            </p>
        @endif

        {{-- S136 — coverage bar (only renders when story_cluster has
             ≥2 bias sides; otherwise falls back to source · bias chip
             OR nothing if neither is set). --}}
        {!! Theme::partial('home.coverage-bar', ['post' => $post, 'compact' => true]) !!}

        {{-- Meta --}}
        <div class="article-card__meta d-flex justify-content-between align-items-center gap-2 flex-wrap">
            {!! Theme::partial('post-meta', [
                'post' => $post,
                'wrapperClass' => 'mb-0'
            ]) !!}
            {{-- S340: reading-time chip on every card variant. --}}
            {!! Theme::partial('reading-time', ['post' => $post]) !!}
        </div>
    </div>
</article>
