@php
    /**
     * Grid item — GrimbaNews refit.
     * Bias badge + optional L/C/R coverage bar + source kicker.
     */
    $__title = \App\Support\GrimbaTranslationPresenter::title($post);
    $__desc = \App\Support\GrimbaTranslationPresenter::description($post);
    $__isTr = \App\Support\GrimbaTranslationPresenter::isTranslated($post);
@endphp
<article class="article-card">
    <div class="article-card__image">
        <a href="{{ $post->url }}">
            {!! Theme::partial('post-hero-img', ['post' => $post, 'size' => 'medium']) !!}
        </a>

        {{-- Bias Badge + S173 save toggle, top-right stack --}}
        <div class="position-absolute top-0 end-0 p-2 d-flex flex-column align-items-end gap-1">
            {!! Theme::partial('bias-badge', [
                'bias'      => $post->bias_rating ?? null,
                'showLabel' => false,
                'size'      => 'sm',
            ]) !!}
            {!! Theme::partial('save-button', ['post' => $post, 'variant' => 'icon']) !!}
        </div>

        @if($post->is_blindspot ?? false)
            <div class="position-absolute top-0 start-0 p-2">
                <span class="blindspot-badge">{{ __('Angle mort') }}</span>
            </div>
        @endif
    </div>

    <div class="article-card__content">
        {{-- Category + Source kicker + language badge --}}
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            {!! Theme::partial('category-badge', ['post' => $post]) !!}
            @if($post->source_name)
                <span class="opacity-50">·</span>
                <span class="grimba-topnews__kicker opacity-75">{{ $post->source_name }}</span>
            @endif
            {!! Theme::partial('home.language-badge', ['post' => $post, 'compact' => true]) !!}
            {{-- S179 — reading time --}}
            {!! Theme::partial('reading-time', ['post' => $post]) !!}
        </div>

        {{-- Title --}}
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

        {{-- Coverage bar (only draws for balanced clusters, else Centre/Source label) --}}
        {!! Theme::partial('home.coverage-bar', ['post' => $post, 'compact' => false]) !!}
    </div>
</article>
