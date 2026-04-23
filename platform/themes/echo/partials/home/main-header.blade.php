@php
    $topDate = \Carbon\Carbon::now()->locale(app()->getLocale())->isoFormat('dddd D MMMM YYYY');
@endphp
<header class="grimba-header">
    <div class="grimba-header__meta">
        <div class="container-xxl d-flex flex-wrap justify-content-between align-items-center py-1">
            <div class="small opacity-75 d-flex align-items-center gap-2">
                <span>Extension navigateur</span>
                <span class="opacity-50">·</span>
                <span>Thème&nbsp;: Clair / Sombre / Auto</span>
            </div>
            <div class="small opacity-75 d-flex align-items-center gap-3">
                <span>{{ ucfirst($topDate) }}</span>
                <span class="opacity-50">·</span>
                <a href="#location" class="text-decoration-none">Localisation</a>
                <span class="opacity-50">·</span>
                <span>Édition FR</span>
            </div>
        </div>
    </div>

    <div class="grimba-header__main glass-panel">
        <div class="container-xxl d-flex align-items-center gap-4 py-3">
            <a href="{{ url('/') }}" class="grimba-wordmark" aria-label="GrimbaNews — accueil">
                <span class="grimba-wordmark__mark">GRIMBA</span>
                <span class="grimba-wordmark__tag">News</span>
            </a>

            <nav class="grimba-nav d-none d-lg-flex" aria-label="{{ __('Principal') }}">
                <a href="{{ url('/') }}" class="active">Accueil</a>
                <a href="{{ url('/blog') }}">Pour vous</a>
                <a href="#local">Local</a>
                <a href="{{ url('/angles-morts') }}">Angles morts</a>
                <a href="{{ url('/sources') }}">Sources</a>
            </nav>

            <form action="{{ url('/search') }}" method="get" class="grimba-search flex-grow-1" role="search">
                <input type="search" name="q" placeholder="Rechercher une histoire, un sujet, une source…" aria-label="{{ __('Recherche') }}">
                <button type="submit" aria-label="{{ __('Lancer la recherche') }}">
                    <x-core::icon name="ti ti-search" />
                </button>
            </form>

            <div class="d-flex align-items-center gap-2">
                <a href="#subscribe" class="btn-grimba btn-grimba--solid">S'abonner</a>
                <a href="{{ url('/login') }}" class="btn-grimba btn-grimba--ghost">Se connecter</a>
            </div>
        </div>
    </div>
</header>
