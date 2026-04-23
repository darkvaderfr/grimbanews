@php
    $mainMenu = trim(Menu::renderMenuLocation('main-menu', [
        'options' => ['class' => 'mainmenu', 'id' => 'mobile-menu-active'],
        'view' => 'main-menu-mobile',
    ]));
@endphp

@if ($mainMenu)
    <div class="mobile-menu d-block d-lg-none">
        <nav class="nav-main mainmenu-nav mt--30 d-flex flex-column justify-content-center">
            {!!
               Menu::renderMenuLocation('main-menu', [
                   'options' => ['class' => 'mainmenu', 'id' => 'mobile-menu-active'],
                   'view' => 'main-menu-mobile',
               ])
            !!}
        </nav>
    </div>
@endif
