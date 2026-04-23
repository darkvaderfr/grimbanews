<article class="article-card {{ $classWrapper ?? null }}">
    <div class="article-card__image">
        <a href="{{ $post->url }}">
            {{ RvMedia::image($post->image, $post->name, 'large') }}
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

        {{-- Meta --}}
        <div class="article-card__meta">
            {!! Theme::partial('post-meta', [
                'post' => $post,
                'wrapperClass' => 'mb-0'
            ]) !!}
        </div>
    </div>
</article>
