@php
    // S96 — unified card/hero image renderer. Falls back to the
    // editorial SVG placeholder when posts.image is null, instead of
    // RvMedia's generic broken-image glyph.
    $__size = $size ?? 'medium';
    $__alt  = \App\Support\GrimbaTranslationPresenter::title($post);
    $__hasImg = ! empty($post->image);
    $__eager = (bool) ($eager ?? false);
@endphp
@if($__hasImg)
    {!! RvMedia::image($post->image, $__alt, $__size, attributes: [
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
