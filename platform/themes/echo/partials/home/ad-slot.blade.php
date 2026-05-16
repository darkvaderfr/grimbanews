@php
    $location = (string) ($location ?? '');
    $label = (string) ($label ?? __('Publicité'));
    $class = trim('grimba-ad-slot ' . (string) ($class ?? ''));
    $attributes = [
        'class' => $class,
        'aria-label' => $label,
    ];

    $configuredHtml = $location !== ''
        ? apply_filters('ads_render', null, $location, $attributes)
        : '';
    $ad = \App\Support\GrimbaAds::resolve($location, (string) $configuredHtml, $label);

    // Top-of-page slots load eager; everything else is hinted lazy so
    // AdSense's own viewport heuristics and any future IntersectionObserver
    // wrapper can prioritise above-the-fold revenue.
    $__eagerLocations = ['grimba_home_top', 'grimba_chrome_top'];
    $__loadingHint = in_array($location, $__eagerLocations, true) ? 'eager' : 'lazy';
@endphp

@if(($ad['mode'] ?? 'hidden') !== 'hidden')
    <aside class="grimba-ad-wrap"
           data-ad-location="{{ $location }}"
           data-ad-mode="{{ $ad['mode'] }}"
           data-ad-provider="{{ $ad['provider'] ?? 'none' }}"
           data-grimba-ad-lazy="{{ $__loadingHint }}">
        <span class="grimba-ad-wrap__label">{{ $ad['label'] ?? $label }}</span>
        @if(($ad['mode'] ?? '') === 'configured')
            {!! $ad['html'] !!}
        @elseif(($ad['mode'] ?? '') === 'network')
            @include(Theme::getThemeNamespace('partials.ads.adsense-unit'), [
                'ad' => $ad,
                'attributes' => $attributes,
            ])
        @elseif(($ad['mode'] ?? '') === 'direct')
            @include(Theme::getThemeNamespace('partials.ads.direct-card'), [
                'ad' => $ad,
                'attributes' => $attributes,
            ])
        @endif
    </aside>
@endif
