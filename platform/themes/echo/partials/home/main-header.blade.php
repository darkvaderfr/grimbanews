@php
    $topDate = \Carbon\Carbon::now()->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY');
    $rawFollow = (string) request()->cookie('grimba_follow', '');
    $followCount = count(array_filter(array_map('intval', explode(',', $rawFollow))));

    // S178 — vault count for the header link. Derived from the same
    // grimba_vault cookie /coffre reads, so the SSR badge matches the
    // landing page on first paint. Live JS updates handled below.
    $rawVault = (string) request()->cookie(\App\Support\GrimbaVault::COOKIE, '');
    $vaultCount = count(\App\Support\GrimbaVault::parseIds($rawVault));
    $themePref = (string) request()->cookie('grimba_theme', 'light');
    if (! in_array($themePref, ['light', 'dark'], true)) $themePref = 'light';
    $themeIcon = ['light' => '☀', 'dark' => '☾'][$themePref];

    // Region-scoped editorial pulse for first paint. Per Vader 2026-05-16
    // every editorial surface for a named edition (Africa / Europe /
    // Americas) must show only that region's pulse. International stays
    // global because the scope short-circuits on `international`.
    $__pulseRegion = \App\Ground\Regions::migrate(
        (string) request()->cookie(\App\Scopes\GrimbaRegionScope::COOKIE_NAME, 'international')
    );
    $pulse = \Illuminate\Support\Facades\Cache::remember(
        'grimba_header_pulse_v3:' . $__pulseRegion,
        300,
        function (): array {
            $morning = now()->setTime(6, 0);
            return [
                'new' => \App\Support\GrimbaPostRecency::wherePublishedSince(
                    \Botble\Blog\Models\Post::query()->where('status', 'published'),
                    $morning
                )->count(),
                'blindspots' => \Botble\Blog\Models\Post::query()
                    ->where('status', 'published')
                    ->where('is_blindspot', true)
                    ->count(),
                'clusters' => \Botble\Blog\Models\Post::query()
                    ->where('status', 'published')
                    ->whereNotNull('story_cluster_id')
                    ->distinct('story_cluster_id')
                    ->count('story_cluster_id'),
            ];
        }
    );
@endphp
<header class="grimba-header">
    <div class="grimba-header__meta">
        <div class="container-xxl grimba-utility-bar py-1">
            <div class="grimba-utility-bar__scroll">
                <div class="small opacity-75 d-flex align-items-center gap-2 grimba-utility-bar__cluster">
                    <button type="button"
                            class="grimba-theme-cycle"
                            data-grimba-theme-cycle
                            data-grimba-theme-current="{{ $themePref }}"
                            aria-label="{{ __('Changer le thème') }}"
                            title="{{ __('Changer le thème') }}">
                        <span data-grimba-theme-icon aria-hidden="true">{{ $themeIcon }}</span>
                    </button>
                    @include(Theme::getThemeNamespace('partials.home.lang-switch'))
                    <span class="grimba-header-pulse">
                        <span class="grimba-stat-pill"
                              title="{{ trans_choice(':count nouveau ce matin|:count nouveaux ce matin', $pulse['new'], ['count' => $pulse['new']]) }}"
                              aria-label="{{ trans_choice(':count nouveau ce matin|:count nouveaux ce matin', $pulse['new'], ['count' => $pulse['new']]) }}">
                            <span class="grimba-stat-pill__icon" aria-hidden="true">✦</span>
                            <span class="grimba-stat-pill__value">{{ number_format((int) $pulse['new']) }}</span>
                            <span class="grimba-stat-pill__label">{{ __('nouveau') }}</span>
                        </span>
                        <span class="grimba-stat-pill"
                              title="{{ trans_choice(':count angle mort|:count angles morts', $pulse['blindspots'], ['count' => $pulse['blindspots']]) }}"
                              aria-label="{{ trans_choice(':count angle mort|:count angles morts', $pulse['blindspots'], ['count' => $pulse['blindspots']]) }}">
                            <span class="grimba-stat-pill__icon" aria-hidden="true">⊘</span>
                            <span class="grimba-stat-pill__value">{{ number_format((int) $pulse['blindspots']) }}</span>
                            <span class="grimba-stat-pill__label">{{ __('angles') }}</span>
                        </span>
                        <span class="grimba-stat-pill"
                              title="{{ trans_choice(':count dossier actif|:count dossiers actifs', $pulse['clusters'], ['count' => $pulse['clusters']]) }}"
                              aria-label="{{ trans_choice(':count dossier actif|:count dossiers actifs', $pulse['clusters'], ['count' => $pulse['clusters']]) }}">
                            <span class="grimba-stat-pill__icon" aria-hidden="true">◉</span>
                            <span class="grimba-stat-pill__value">{{ number_format((int) $pulse['clusters']) }}</span>
                            <span class="grimba-stat-pill__label">{{ __('dossiers') }}</span>
                        </span>
                    </span>
                </div>
                <div class="small d-flex align-items-center gap-2 grimba-header__tools">
                    <span class="grimba-header-date">{{ ucfirst($topDate) }}</span>
                    {{-- Phase D-06: surface the two dedicated streams in
                         the header so readers can jump straight to
                         /breaking and /latest. Live-pulse dot beside the
                         Breaking link mirrors the ticker eyebrow. --}}
                    <a href="{{ url('/breaking') }}" class="grimba-header-tool-link grimba-header-tool-link--breaking" title="{{ __('Breaking news') }}">
                        <span class="grimba-header-tool-link__pulse" aria-hidden="true"></span>
                        <span class="grimba-header-tool-link__label">{{ __('Breaking') }}</span>
                    </a>
                    <a href="{{ url('/latest') }}" class="grimba-header-tool-link" title="{{ __('Latest news') }}">
                        <span class="grimba-header-tool-link__label">{{ __('Dernières') }}</span>
                    </a>
                    <a href="{{ url('/pour-vous') }}" class="grimba-header-tool-link grimba-header-tool-link--follow" title="{{ __('Pour vous') }}">
                        <span class="grimba-header-tool-link__label">{{ __('Pour vous') }}</span>
                        <span id="grimba-follow-count">{{ $followCount }}</span>
                    </a>
                    {{-- S178 — vault link in utility bar so readers find their saves. --}}
                    <a href="{{ url('/coffre') }}" class="grimba-header-tool-link grimba-header-tool-link--vault" title="{{ __('Mes articles sauvegardés') }}">
                        <span aria-hidden="true">★</span><span id="grimba-vault-count" data-grimba-vault-count>{{ $vaultCount }}</span>
                    </a>
                    @include(Theme::getThemeNamespace('partials.home.region-dropdown'))
                </div>
            </div>
        </div>
    </div>

    <div class="grimba-header__main glass-panel">
        <div class="container-xxl d-flex align-items-center gap-4 py-3">
            <a href="{{ url('/') }}" class="grimba-wordmark" aria-label="{{ __('Grimba News — accueil') }}">
                <span class="grimba-wordmark__mark">Grimba</span>
                <span class="grimba-wordmark__tag">News</span>
            </a>

            @php
                /* Vader 2026-05-16 Wave L — primary nav with dynamic
                   active state + new Dossiers entry pointing at
                   /dossiers (alias for /comparatif). */
                $__navItems = [
                    ['href' => url('/'),           'label' => __('Accueil'),    'match' => fn () => request()->is('/') || request()->path() === '/'],
                    ['href' => url('/dossiers'),   'label' => __('Dossiers'),   'match' => fn () => request()->is('dossiers*') || request()->is('comparatif*')],
                    ['href' => url('/pour-vous'),  'label' => __('Pour vous'),  'match' => fn () => request()->is('pour-vous*') || request()->is('for-you*')],
                    ['href' => url('/local'),      'label' => __('Local'),      'match' => fn () => request()->is('local*')],
                    ['href' => url('/sources'),    'label' => __('Sources'),    'match' => fn () => request()->is('sources*') || request()->is('source/*')],
                ];
            @endphp
            <nav class="grimba-nav d-none d-lg-flex" aria-label="{{ __('Principal') }}">
                @foreach($__navItems as $__item)
                    @php $__isActive = (bool) call_user_func($__item['match']); @endphp
                    <a href="{{ $__item['href'] }}"
                       @if($__isActive) class="active" aria-current="page" @endif>
                        {{ $__item['label'] }}
                    </a>
                @endforeach
            </nav>

            @php
                $__gnHeaderLang = (string) (request()->cookie('grimba_lang') ?? app()->getLocale() ?? 'fr');
                $__gnHeaderSearchPlaceholder = $__gnHeaderLang === 'en'
                    ? 'Topic, source, story…'
                    : 'Sujet, source, histoire…';
            @endphp
            <form action="{{ url('/search') }}" method="get" class="grimba-search flex-grow-1" role="search" data-grimba-command-form>
                <input type="search" name="q" placeholder="{{ $__gnHeaderSearchPlaceholder }}" aria-label="{{ __('Recherche') }}" data-grimba-command-source>
                <button type="submit" aria-label="{{ __('Lancer la recherche') }}">
                    <x-core::icon name="ti ti-search" />
                </button>
            </form>

            <div class="grimba-header__actions d-flex align-items-center gap-2">
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
        const cycle = document.querySelector('[data-grimba-theme-cycle]');
        if (!cycle) return;

        const root = document.documentElement;
        const VALID = ['light', 'dark'];
        const ICONS = { light: '☀', dark: '☾' };
        const THEME_COLORS = { light: '#f6f1e8', dark: '#121007' };

        function syncThemeColor(pref) {
            const meta = document.querySelector('meta[name="theme-color"]');
            if (meta) meta.setAttribute('content', THEME_COLORS[pref] || THEME_COLORS.light);
        }

        function refresh() {
            let pref = root.getAttribute('data-grimba-theme-pref') || 'light';
            if (!VALID.includes(pref)) pref = 'light';
            cycle.dataset.grimbaThemeCurrent = pref;
            const icon = cycle.querySelector('[data-grimba-theme-icon]');
            if (icon) icon.textContent = ICONS[pref] || ICONS.light;
            syncThemeColor(pref);
        }

        function apply(pref) {
            if (!VALID.includes(pref)) pref = 'light';
            root.setAttribute('data-bs-theme', pref);
            root.setAttribute('data-grimba-theme-pref', pref);
            const oneYear = 60 * 60 * 24 * 365;
            document.cookie = 'grimba_theme=' + pref + '; path=/; max-age=' + oneYear + '; SameSite=Lax';
            refresh();
        }

        refresh();
        cycle.addEventListener('click', () => {
            const pref = root.getAttribute('data-grimba-theme-pref') || 'light';
            const next = pref === 'dark' ? 'light' : 'dark';
            apply(next);
        });
    })();
</script>
