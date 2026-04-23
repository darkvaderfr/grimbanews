{!! apply_filters(THEME_FRONT_BODY, null) !!}

{!! Theme::partial('header') !!}

<main>
    @yield('content')
</main>

<script>
    'use strict';

    window.siteConfig = {};
</script>

{!! Theme::partial('footer') !!}

{!! Theme::footer() !!}
