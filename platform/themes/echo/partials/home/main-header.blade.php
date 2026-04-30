@php
    $topDate = \Carbon\Carbon::now()->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY');
    $rawFollow = (string) request()->cookie('grimba_follow', '');
    $followCount = count(array_filter(array_map('intval', explode(',', $rawFollow))));

    // S178 — vault count for the header link. Derived from the same
    // grimba_vault cookie /coffre reads, so the SSR badge matches the
    // landing page on first paint. Live JS updates handled below.
    $rawVault = (string) request()->cookie(\App\Support\GrimbaVault::COOKIE, '');
    $vaultCount = count(\App\Support\GrimbaVault::parseIds($rawVault));

    // S206/D3 — cached editorial pulse for first paint.
    $pulse = \Illuminate\Support\Facades\Cache::remember('grimba_header_pulse_v1', 300, function (): array {
        $morning = now()->setTime(6, 0);
        return [
            'new' => \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->where('created_at', '>=', $morning)
                ->count(),
            'blindspots' => \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->where('is_blindspot', true)
                ->count(),
            'clusters' => \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
                ->where('status', 'published')
                ->whereNotNull('story_cluster_id')
                ->distinct('story_cluster_id')
                ->count('story_cluster_id'),
        ];
    });
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
                <span class="opacity-25 d-none d-lg-inline">·</span>
                <span class="grimba-header-pulse d-none d-lg-inline">
                    {{ trans_choice(':count nouveau ce matin|:count nouveaux ce matin', $pulse['new'], ['count' => $pulse['new']]) }} ·
                    {{ trans_choice(':count angle mort|:count angles morts', $pulse['blindspots'], ['count' => $pulse['blindspots']]) }} ·
                    {{ trans_choice(':count dossier actif|:count dossiers actifs', $pulse['clusters'], ['count' => $pulse['clusters']]) }}
                </span>
            </div>
            <div class="small opacity-75 d-flex align-items-center gap-2">
                <span class="d-none d-md-inline">{{ ucfirst($topDate) }}</span>
                <span class="opacity-25 d-none d-md-inline">·</span>
                <a href="{{ url('/pour-vous') }}" class="text-decoration-none" title="{{ __('Pour vous') }}">
                    {{ __('Pour vous') }} (<span id="grimba-follow-count">{{ $followCount }}</span>)
                </a>
                <span class="opacity-25">·</span>
                {{-- S178 — vault link in utility bar so readers find their saves. --}}
                <a href="{{ url('/coffre') }}" class="text-decoration-none" title="{{ __('Mes articles sauvegardés') }}">
                    <span aria-hidden="true">★</span>&nbsp;<span id="grimba-vault-count" data-grimba-vault-count>{{ $vaultCount }}</span>
                </a>
                <span class="opacity-25">·</span>
                @include(Theme::getThemeNamespace('partials.home.region-dropdown'))
            </div>
        </div>
    </div>

    <div class="grimba-header__main glass-panel">
        <div class="container-xxl d-flex align-items-center gap-4 py-3">
            <a href="{{ url('/') }}" class="grimba-wordmark" aria-label="{{ __('Grimba News — accueil') }}">
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

        const active = 'light';
        buttons.forEach(b => b.setAttribute('aria-pressed', String(b.dataset.grimbaTheme === active)));

        function apply(pref) {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            document.documentElement.setAttribute('data-grimba-theme-pref', 'light');
            const oneYear = 60 * 60 * 24 * 365;
            document.cookie = 'grimba_theme=light; path=/; max-age=' + oneYear + '; SameSite=Lax';
            buttons.forEach(b => b.setAttribute('aria-pressed', String(b.dataset.grimbaTheme === 'light')));
        }

        buttons.forEach(b => b.addEventListener('click', () => apply(b.dataset.grimbaTheme)));

        // Homepage dark mode is intentionally locked to light until the
        // dark-mode audit closes.
    })();
</script>
