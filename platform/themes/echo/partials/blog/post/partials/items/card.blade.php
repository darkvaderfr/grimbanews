@php
    // Reader mode: original | auto | both (cookie grimba_translate, S56).
    // If the reader chose translation AND we have one, swap into the
    // article card. `both` layers a small subtitle under the main title
    // so the original language doesn't vanish.
    $__mode = (string) (request()->cookie('grimba_translate') ?? 'original');
    if (! in_array($__mode, ['original', 'auto', 'both'], true)) $__mode = 'original';
    $__target = (string) (request()->cookie('grimba_lang') ?? 'fr');
    $__hasTr = ! empty($post->translated_name)
        && ($post->translated_to ?? null) === $__target
        && ($post->original_language ?? null) !== $__target;

    $__title = ($__mode !== 'original' && $__hasTr) ? $post->translated_name : $post->name;
    $__subTitle = ($__mode === 'both' && $__hasTr) ? $post->name : null;
    $__desc  = ($__mode !== 'original' && $__hasTr && $post->translated_description)
        ? $post->translated_description
        : $post->description;
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

        {{-- Title (respects reader-mode cookie: original | auto | both) --}}
        <h2 class="article-card__title">
            <a href="{{ $post->url }}" title="{{ $__title }}" class="title-hover text-decoration-none">
                {{ $__title }}
            </a>
        </h2>
        @if ($__mode !== 'original' && $__hasTr)
            <div class="article-card__nobuai mb-1">
                {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
            </div>
        @endif
        @if ($__subTitle)
            <div class="article-card__subtitle small opacity-75 mb-1" lang="{{ $post->original_language }}" title="{{ $__subTitle }}">
                {{ $__subTitle }}
            </div>
        @endif
        @if ($__mode !== 'original' && ! $__hasTr && ($post->original_language ?? null) && $post->original_language !== $__target)
            <div class="article-card__translation-pending small opacity-50 mb-1" lang="{{ $post->original_language }}">
                <em>— traduction en attente ({{ strtoupper($post->original_language) }})</em>
            </div>
        @endif

        {{-- Description --}}
        @if ($description = $__desc)
            <p class="echo-hero-discription truncate-custom truncate-3-custom mb-2" title="{{ $description }}">
                {!! BaseHelper::clean($description) !!}
            </p>
        @endif

        {{-- Meta --}}
        <div class="article-card__meta">
            {!! Theme::partial('post-meta', [
                'post' => $post,
                'wrapperClass' => 'mb-0'
            ]) !!}
        </div>
    </div>
</article>
