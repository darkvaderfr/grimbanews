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
@endphp
@if($__hasUsableImg)
    {!! \Botble\Media\Facades\RvMedia::image($post->image, $__alt, $__size, attributes: [
        'loading' => $__eager ? 'eager' : 'lazy',
        'decoding' => $__eager ? 'sync' : 'async',
        'width' => 1200,
        'height' => 630,
        'data-grimba-post-id' => (string) $post->id,
    ]) !!}
@else
    <img
        src="{{ route('public.og.placeholder', ['id' => $post->id]) }}"
        alt="{{ $__alt }}"
        loading="{{ $__eager ? 'eager' : 'lazy' }}"
        decoding="{{ $__eager ? 'sync' : 'async' }}"
        width="1200" height="630"
        class="gn-placeholder"
        data-grimba-post-id="{{ $post->id }}"
    />
@endif
