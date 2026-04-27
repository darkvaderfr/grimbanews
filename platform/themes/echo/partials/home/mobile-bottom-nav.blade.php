@php
    $current = request()->path();
    $items = [
        ['label' => __('Accueil'), 'href' => url('/'), 'icon' => '⌂', 'active' => request()->is('/') || $current === '/'],
        ['label' => __('Pour vous'), 'href' => url('/pour-vous'), 'icon' => '◌', 'active' => request()->is('pour-vous')],
        ['label' => __('Local'), 'href' => url('/local'), 'icon' => '⌖', 'active' => request()->is('local')],
        ['label' => __('Coffre'), 'href' => url('/coffre'), 'icon' => '★', 'active' => request()->is('coffre*')],
        ['label' => __('Compte'), 'href' => url('/account'), 'icon' => '◎', 'active' => request()->is('account') || request()->is('login') || request()->is('register')],
    ];
@endphp

<nav class="grimba-mobile-nav" aria-label="{{ __('Navigation mobile') }}">
    @foreach($items as $item)
        <a href="{{ $item['href'] }}" class="grimba-mobile-nav__item @if($item['active']) is-active @endif">
            <span aria-hidden="true" class="grimba-mobile-nav__icon">{{ $item['icon'] }}</span>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
