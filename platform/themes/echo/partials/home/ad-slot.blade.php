@php
    $location = (string) ($location ?? '');
    $label = (string) ($label ?? __('Publicité'));
    $class = trim('grimba-ad-slot ' . (string) ($class ?? ''));
    $attributes = [
        'class' => $class,
        'aria-label' => $label,
    ];

    $html = $location !== ''
        ? apply_filters('ads_render', null, $location, $attributes)
        : '';
@endphp

@if(trim((string) $html) !== '')
    <aside class="grimba-ad-wrap" data-ad-location="{{ $location }}">
        <span class="grimba-ad-wrap__label">{{ $label }}</span>
        {!! $html !!}
    </aside>
@endif
