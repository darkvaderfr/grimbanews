@php
    // S96 — unified card/hero image renderer. Falls back to the
    // editorial SVG placeholder when posts.image is null, instead of
    // RvMedia's generic broken-image glyph.
    $__size = $size ?? 'medium';
    $__alt  = \App\Support\GrimbaTranslationPresenter::title($post);
    $__hasImg = ! empty($post->image);
@endphp
@if($__hasImg)
    {!! RvMedia::image($post->image, $__alt, $__size) !!}
@else
    <img
        src="{{ route('public.og.placeholder', ['id' => $post->id]) }}"
        alt="{{ $__alt }}"
        loading="lazy"
        decoding="async"
        width="1200" height="630"
        class="gn-placeholder"
    />
@endif
