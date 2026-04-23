{!! Theme::partial('menu-sidebar.index') !!}

<header @class(['echo-header-area header-five', 'no-absolute' => ! Theme::get('isHomepage')])>
    {!! Theme::partial('header.content') !!}
</header>
