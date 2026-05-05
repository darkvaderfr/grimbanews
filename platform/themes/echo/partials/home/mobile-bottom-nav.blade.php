@php
    $current = request()->path();
    $items = [
        ['label' => __('Accueil'), 'href' => url('/'), 'icon' => 'ti ti-home', 'active' => request()->is('/') || $current === '/'],
        ['label' => __('Pour vous'), 'href' => url('/pour-vous'), 'icon' => 'ti ti-circle-dotted', 'active' => request()->is('pour-vous')],
        ['label' => __('Local'), 'href' => url('/local'), 'icon' => 'ti ti-current-location', 'active' => request()->is('local')],
        ['label' => __('Coffre'), 'href' => url('/coffre'), 'icon' => 'ti ti-star', 'active' => request()->is('coffre*')],
        ['label' => __('Compte'), 'href' => url('/account'), 'icon' => 'ti ti-user-circle', 'active' => request()->is('account') || request()->is('login') || request()->is('register')],
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
