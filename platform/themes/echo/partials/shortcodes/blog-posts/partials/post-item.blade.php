@php
    $wrapperClass = $wrapperClass ?? null;
    $sizeImage = $sizeImage ?? 'thumb-medium';
    $hideButton = $hideButton ?? false;
@endphp

<div @class(['hm5-featurea-main-content', $wrapperClass])>
    <div class="hm5-feature-img img-transition-scale position-relative">
        <a href="{{ $post->url }}">
            {{ RvMedia::image($post->image, $post->name, $sizeImage) }}
        </a>

        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
    </div>
    <div class="hm5-featurea-meta-content">
        {!! Theme::partial('category-badge', compact('post')) !!}

        <div class="hm5-feature-img-title">
            <a href="{{ $post->url }}" title="{{ $post->name }}" class="text-capitalize title-hover text-heading truncate-custom truncate-2-custom">{{ $post->name }}</a>
        </div>

        @if (! $hideButton)
            <div class="feature-img-content-btn">
                <a href="{{ $post->url }}" class="hm5-btn-2">{{ __('READ MORE') }} {!! Theme::partial('icon-button') !!}</a>
            </div>
        @endif
    </div>
</div>
