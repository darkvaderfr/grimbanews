@php
    $current = request()->path();
    /* Vader 2026-05-16 Wave L — added Dossiers as the second slot
       (most-used after Home). Compte folded into the "more" menu in
       a future sprint; for now Coffre is the user-action slot. */
    $items = [
        ['label' => __('Accueil'),  'href' => url('/'),          'icon' => 'ti ti-home',             'active' => request()->is('/') || $current === '/'],
        ['label' => __('Dossiers'), 'href' => url('/dossiers'),  'icon' => 'ti ti-folders',          'active' => request()->is('dossiers*') || request()->is('comparatif*')],
        ['label' => __('Pour vous'),'href' => url('/pour-vous'), 'icon' => 'ti ti-circle-dotted',    'active' => request()->is('pour-vous*') || request()->is('for-you*')],
        ['label' => __('Local'),    'href' => url('/local'),     'icon' => 'ti ti-current-location', 'active' => request()->is('local*')],
        ['label' => __('Coffre'),   'href' => url('/coffre'),    'icon' => 'ti ti-star',             'active' => request()->is('coffre*')],
    ];
@endphp

<nav class="grimba-mobile-nav" aria-label="{{ __('Navigation mobile') }}">
    @foreach($items as $item)
        <a href="{{ $item['href'] }}" class="grimba-mobile-nav__item @if($item['active']) is-active @endif">
            <span aria-hidden="true" class="grimba-mobile-nav__icon">
                <x-core::icon :name="$item['icon']" />
            </span>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
