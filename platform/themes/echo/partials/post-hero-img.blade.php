@php
    // S96 / S326 — unified card/hero image renderer. Falls back to our
    // editorial SVG placeholder when posts.image is null OR when RvMedia
    // would resolve to its generic 1920x1080 "placeholder.png" default
    // (which renders as a gray box reading literally "1920 x 1080").
    $__size = $size ?? 'medium';
    $__alt  = \App\Support\GrimbaTranslationPresenter::title($post);
    $__eager = (bool) ($eager ?? false);

    $__resolved = ! empty($post->image)
        ? \Botble\Media\Facades\RvMedia::getImageUrl($post->image, $__size)
        : null;
    $__defaultUrl = \Botble\Media\Facades\RvMedia::getDefaultImage(false, $__size);
    $__hasUsableImg = $__resolved !== null && $__resolved !== $__defaultUrl;
    $__isExternal = $__hasUsableImg && is_string($__resolved) && preg_match('#^https?://#i', $__resolved);
    $__theme = request()?->cookie('grimba_theme', 'light') ?: 'light';
    if (! in_array($__theme, ['light', 'dark'], true)) {
        $__theme = 'light';
    }
@endphp
{{-- Wave NNNNN part 2 (Vader 2026-05-19) — when $__eager is set,
     also emit fetchpriority="high" so the browser prioritizes this
     hero in the network queue (LCP win on home + post pages). The
     `loading` + `decoding` already mirror `$__eager`; `fetchpriority`
     is the third leg of the same hint. --}}
@if($__isExternal)
    <img
        src="{{ route('public.img-proxy', ['provider' => 'article-hero', 'pid' => $post->id, 'theme' => $__theme, 'u' => $__resolved]) }}"
        alt="{{ $__alt }}"
        loading="{{ $__eager ? 'eager' : 'lazy' }}"
        decoding="{{ $__eager ? 'sync' : 'async' }}"
        @if($__eager) fetchpriority="high" @endif
        width="1200" height="630"
        data-grimba-post-id="{{ $post->id }}"
    />
@elseif($__hasUsableImg)
    {!! \Botble\Media\Facades\RvMedia::image($post->image, $__alt, $__size, attributes: array_filter([
        'loading' => $__eager ? 'eager' : 'lazy',
        'decoding' => $__eager ? 'sync' : 'async',
        'fetchpriority' => $__eager ? 'high' : null,
        'width' => 1200,
        'height' => 630,
        'data-grimba-post-id' => (string) $post->id,
    ], fn ($v) => $v !== null)) !!}
@else
    <img
        src="{{ route('public.og.placeholder', ['id' => $post->id]) }}"
        alt="{{ $__alt }}"
        loading="{{ $__eager ? 'eager' : 'lazy' }}"
        decoding="{{ $__eager ? 'sync' : 'async' }}"
        @if($__eager) fetchpriority="high" @endif
        width="1200" height="630"
        class="gn-placeholder"
        data-grimba-post-id="{{ $post->id }}"
    />
@endif
