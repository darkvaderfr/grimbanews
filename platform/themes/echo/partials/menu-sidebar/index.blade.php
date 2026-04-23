@php
    tap(
        dynamic_sidebar('menu_sidebar'),
        fn ($menuSidebar) => app()->instance('themes.menu_sidebar', $menuSidebar)
    );
@endphp

<div id="side-bar" class="side-bar header-one" data-bb-toggle="menu-sidebar-content" data-url="{{ route('public.ajax.menu-sidebar') }}">
    <div class="menu-sidebar-loading-wrapper">
        <div class="backdrop"></div>
        <div class="loading"></div>
    </div>
</div>
