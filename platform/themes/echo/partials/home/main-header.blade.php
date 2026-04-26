@php
    $topDate = \Carbon\Carbon::now()->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY');
    $rawFollow = (string) request()->cookie('grimba_follow', '');
    $followCount = count(array_filter(array_map('intval', explode(',', $rawFollow))));
@endphp
<header class="grimba-header">
    <div class="grimba-header__meta">
        <div class="container-xxl d-flex flex-wrap justify-content-between align-items-center py-1 grimba-utility-bar">
            <div class="small opacity-75 d-flex align-items-center gap-2">
                {{-- Theme as icon-only toggle — label moved to tooltip --}}
                <div class="grimba-theme-switch" role="radiogroup" aria-label="{{ __('Choix du thème') }}">
                    <button type="button" data-grimba-theme="light" aria-pressed="false" title="{{ __('Thème clair') }}">
                        <span aria-hidden="true">☀</span>
                    </button>
                    <button type="button" data-grimba-theme="dark"  aria-pressed="false" title="{{ __('Thème sombre') }}">
                        <span aria-hidden="true">☾</span>
                    </button>
                    <button type="button" data-grimba-theme="auto"  aria-pressed="true"  title="{{ __('Thème auto (suit le système)') }}">
                        <span aria-hidden="true">◐</span>
                    </button>
                </div>
                <span class="opacity-25">·</span>
                @include(Theme::getThemeNamespace('partials.home.lang-switch'))
                {{-- S162 — translate-picker moved out of header into the
                     story page itself. Header had two language-related
                     pills (FR/EN + VO/NobuAI) which read as duplicate
                     intent. The reader meets the toggle where it
                     matters — on an article. Cookie is global, so a
                     flip on one article applies sitewide. --}}
            </div>
            <div class="small opacity-75 d-flex align-items-center gap-2">
                <span class="d-none d-md-inline">{{ ucfirst($topDate) }}</span>
                <span class="opacity-25 d-none d-md-inline">·</span>
                <a href="{{ url('/pour-vous') }}" class="text-decoration-none" title="{{ __('Pour vous') }}">
                    {{ __('Pour vous') }} (<span id="grimba-follow-count">{{ $followCount }}</span>)
                </a>
                <span class="opacity-25">·</span>
                @include(Theme::getThemeNamespace('partials.home.region-dropdown'))
            </div>
        </div>
    </div>

    <div class="grimba-header__main glass-panel">
        <div class="container-xxl d-flex align-items-center gap-4 py-3">
            <a href="{{ url('/') }}" class="grimba-wordmark" aria-label="Grimba News — accueil">
                <span class="grimba-wordmark__mark">Grimba</span>
                <span class="grimba-wordmark__tag">News</span>
            </a>

            <nav class="grimba-nav d-none d-lg-flex" aria-label="{{ __('Principal') }}">
                <a href="{{ url('/') }}" class="active">{{ __('Accueil') }}</a>
                <a href="{{ url('/pour-vous') }}">{{ __('Pour vous') }}</a>
                <a href="{{ url('/local') }}">{{ __('Local') }}</a>
                <a href="{{ url('/angles-morts') }}">{{ __('Angles morts') }}</a>
                <a href="{{ url('/sources') }}">{{ __('Sources') }}</a>
            </nav>

            <form action="{{ url('/search') }}" method="get" class="grimba-search flex-grow-1" role="search">
                <input type="search" name="q" placeholder="{{ __('Rechercher une histoire, un sujet, une source…') }}" aria-label="{{ __('Recherche') }}">
                <button type="submit" aria-label="{{ __('Lancer la recherche') }}">
                    <x-core::icon name="ti ti-search" />
                </button>
            </form>

            <div class="d-flex align-items-center gap-2">
                <button type="button" data-grimba-newsletter-open class="btn-grimba btn-grimba--solid">{{ __('S\'abonner') }}</button>
                <a href="{{ url('/login') }}" class="btn-grimba btn-grimba--ghost">{{ __('Se connecter') }}</a>
            </div>
        </div>
    </div>
</header>

@include(Theme::getThemeNamespace('partials.home.newsletter-modal'))
@include(Theme::getThemeNamespace('partials.home.onboarding-modal'))

<script>
    (function () {
        const buttons = document.querySelectorAll('[data-grimba-theme]');
        if (!buttons.length) return;

        const active = document.documentElement.getAttribute('data-grimba-theme-pref') || 'auto';
        buttons.forEach(b => b.setAttribute('aria-pressed', String(b.dataset.grimbaTheme === active)));

        function apply(pref) {
            const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
            const effective = pref === 'auto' ? (prefersDark ? 'dark' : 'light') : pref;
            document.documentElement.setAttribute('data-bs-theme', effective);
            document.documentElement.setAttribute('data-grimba-theme-pref', pref);
            const oneYear = 60 * 60 * 24 * 365;
            document.cookie = 'grimba_theme=' + pref + '; path=/; max-age=' + oneYear + '; SameSite=Lax';
            buttons.forEach(b => b.setAttribute('aria-pressed', String(b.dataset.grimbaTheme === pref)));
        }

        buttons.forEach(b => b.addEventListener('click', () => apply(b.dataset.grimbaTheme)));

        // Follow OS changes when in auto mode.
        window.matchMedia?.('(prefers-color-scheme: dark)').addEventListener('change', () => {
            const pref = document.documentElement.getAttribute('data-grimba-theme-pref');
            if (pref === 'auto') apply('auto');
        });
    })();
</script>
