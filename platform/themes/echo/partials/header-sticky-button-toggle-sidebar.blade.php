@php($menuSidebar = app()->has('themes.menu_sidebar') ? app('themes.menu_sidebar') : null)

<div @class(['echo-header-top-menu-bar menu-btn ms-3', 'd-md-none d-block' => empty($menuSidebar)])>
    <button data-bb-toggle="menu-sidebar" type="button" aria-label="{{ __('Toggle Sidebar Menu') }}">
        <svg width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.526001 0.953461H20V3.11724H0.526001V0.953461ZM7.01733 8.52668H20V10.6905H7.01733V8.52668ZM0.526001 16.0999H20V18.2637H0.526001V16.0999Z" fill="currentColor" />
        </svg>
    </button>
</div>
