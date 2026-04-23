@php
    /**
     * Grid item — GrimbaNews refit.
     * Bias badge + optional L/C/R coverage bar + source kicker.
     */
@endphp
<article class="article-card">
    <div class="article-card__image">
        <a href="{{ $post->url }}">
            {{ RvMedia::image($post->image, $post->name, 'medium') }}
        </a>

        {{-- Bias Badge top-right --}}
        <div class="position-absolute top-0 end-0 p-2">
            {!! Theme::partial('bias-badge', [
                'bias'      => $post->bias_rating ?? null,
                'showLabel' => false,
                'size'      => 'sm',
            ]) !!}
        </div>

        @if($post->is_blindspot ?? false)
            <div class="position-absolute top-0 start-0 p-2">
                <span class="blindspot-badge">Angle mort</span>
            </div>
        @endif
    </div>

    <div class="article-card__content">
        {{-- Category + Source kicker + language badge --}}
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            @if($post->firstCategory)
                <a href="{{ $post->firstCategory->url }}" class="grimba-topnews__kicker text-decoration-none">
                    {{ $post->firstCategory->name }}
                </a>
            @endif
            @if($post->source_name)
                <span class="opacity-50">·</span>
                <span class="grimba-topnews__kicker opacity-75">{{ $post->source_name }}</span>
            @endif
            {!! Theme::partial('home.language-badge', ['post' => $post, 'compact' => true]) !!}
        </div>

        {{-- Title --}}
        <h2 class="article-card__title">
            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover text-decoration-none">
                {{ $post->name }}
            </a>
        </h2>

        {{-- Description --}}
        @if ($description = $post->description)
            <p class="echo-hero-discription truncate-custom truncate-3-custom mb-2" title="{{ $description }}">
                {!! BaseHelper::clean($description) !!}
            </p>
        @endif

        {{-- Coverage bar (only draws for balanced clusters, else Centre/Source label) --}}
        {!! Theme::partial('home.coverage-bar', ['post' => $post, 'compact' => false]) !!}
    </div>
</article>
