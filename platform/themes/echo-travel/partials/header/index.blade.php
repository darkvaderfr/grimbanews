{!! Theme::partial('menu-sidebar.index') !!}
<header @class(['echo-header-area header-six', 'background-transparent' => Theme::get('header-transparent', false), 'no-absolute' => ! Theme::get('isHomepage')])>
    {!! Theme::partial('header.content') !!}
</header>
