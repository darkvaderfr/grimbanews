@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Surface</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Commande surface</span>
                <h1 class="grimba-admin-title">Règles de traduction</h1>
                <p class="grimba-admin-copy">
                    Tagging linguistique fort + traduction automatique des contenus africains et populaires.
                    Chaque réglage prend effet immédiatement (cache vidé à la sauvegarde).
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <span class="grimba-admin-status">{{ $callsToday }}/{{ $dailyCap }} appels aujourd'hui</span>
            </div>
        </section>

        @if(session('success_msg'))
            <div class="alert alert-success">{{ session('success_msg') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('grimba.translation-rules.save') }}" class="grimba-admin-form">
            @csrf

            {{-- ============================================================
                 Block 1 — Strict locale surfacing (S-LSAT-04)
                 ============================================================ --}}
            <x-core::card class="mb-3">
                <x-core::card.header>
                    <x-core::card.title>Filtrage strict par langue de lecture</x-core::card.title>
                    <small class="text-muted">
                        Quand un lecteur passe en EN/FR via le sélecteur de langue, seuls les articles natifs
                        de la langue ou traduits s'affichent. Les articles dans l'autre langue sans traduction
                        sont masqués. Désactiver une surface = ranking doux (filtre off).
                    </small>
                </x-core::card.header>
                <x-core::card.body>
                    <div class="row g-3">
                        <div class="col-md-12">
                            @include('grimba-admin.translation-rules._toggle', [
                                'name' => 'strict_surface',
                                'label' => 'Filtrage strict — interrupteur principal',
                                'current' => $current['strict_surface'] ?? $defaults['strict_surface'],
                                'help' => "Désactiver = toutes les surfaces tombent en ranking doux, quel que soit leur réglage individuel.",
                            ])
                        </div>
                        <div class="col-md-4">
                            @include('grimba-admin.translation-rules._toggle', [
                                'name' => 'strict_home',
                                'label' => "Page d'accueil",
                                'current' => $current['strict_home'] ?? $defaults['strict_home'],
                            ])
                        </div>
                        <div class="col-md-4">
                            @include('grimba-admin.translation-rules._toggle', [
                                'name' => 'strict_breaking',
                                'label' => 'Page /breaking',
                                'current' => $current['strict_breaking'] ?? $defaults['strict_breaking'],
                            ])
                        </div>
                        <div class="col-md-4">
                            @include('grimba-admin.translation-rules._toggle', [
                                'name' => 'strict_latest',
                                'label' => 'Page /latest',
                                'current' => $current['strict_latest'] ?? $defaults['strict_latest'],
                            ])
                        </div>
                        <div class="col-md-4">
                            @include('grimba-admin.translation-rules._toggle', [
                                'name' => 'strict_dossiers',
                                'label' => 'Page /dossiers',
                                'current' => $current['strict_dossiers'] ?? $defaults['strict_dossiers'],
                            ])
                        </div>
                        <div class="col-md-4">
                            @include('grimba-admin.translation-rules._toggle', [
                                'name' => 'strict_category',
                                'label' => 'Pages catégorie',
                                'current' => $current['strict_category'] ?? $defaults['strict_category'],
                                'help' => 'OFF par défaut — évite les pages vides sur des catégories thin.',
                            ])
                        </div>
                        <div class="col-md-4">
                            @include('grimba-admin.translation-rules._toggle', [
                                'name' => 'strict_search',
                                'label' => 'Page /search',
                                'current' => $current['strict_search'] ?? $defaults['strict_search'],
                                'help' => 'OFF par défaut — search reste exhaustive.',
                            ])
                        </div>
                    </div>
                </x-core::card.body>
            </x-core::card>

            {{-- ============================================================
                 Block 2 — Rule engine (S-LSAT-09/10)
                 ============================================================ --}}
            <x-core::card class="mb-3">
                <x-core::card.header>
                    <x-core::card.title>Moteur de règles — traduction automatique</x-core::card.title>
                    <small class="text-muted">
                        Le moteur tourne toutes les 15 minutes. Il pousse à la traduction les articles africains
                        (seuil bas) + les articles globaux dépassant le seuil de popularité (le Le Monde @ 500
                        vues décrit par Vader).
                    </small>
                </x-core::card.header>
                <x-core::card.body>
                    <div class="row g-3">
                        <div class="col-md-6">
                            @include('grimba-admin.translation-rules._toggle', [
                                'name' => 'rule_engine_enabled',
                                'label' => 'Activer le moteur de règles',
                                'current' => $current['rule_engine_enabled'] ?? $defaults['rule_engine_enabled'],
                                'help' => "Quand OFF, le job */15 *cron tourne en no-op silencieux.",
                            ])
                        </div>
                        <div class="col-md-6">
                            @include('grimba-admin.translation-rules._toggle', [
                                'name' => 'tail_expander_enabled',
                                'label' => 'Activer le ribbon "aussi disponible en…"',
                                'current' => $current['tail_expander_enabled'] ?? $defaults['tail_expander_enabled'],
                                'help' => 'Affiche en bas de page un lien vers les contenus filtrés dans la langue opposée.',
                            ])
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Seuil de popularité global
                                <span class="text-muted">(vues)</span>
                            </label>
                            <input type="number" name="popularity_threshold" class="form-control"
                                   value="{{ $current['popularity_threshold'] ?? $defaults['popularity_threshold'] }}"
                                   min="10" max="100000">
                            <small class="form-text text-muted">
                                Vues à partir desquelles un article (hors région forcée) déclenche la traduction.
                                Défaut : <strong>{{ $defaults['popularity_threshold'] }}</strong>.
                            </small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Seuil pour régions forcées
                                <span class="text-muted">(vues)</span>
                            </label>
                            <input type="number" name="popularity_threshold_africa" class="form-control"
                                   value="{{ $current['popularity_threshold_africa'] ?? $defaults['popularity_threshold_africa'] }}"
                                   min="10" max="100000">
                            <small class="form-text text-muted">
                                Seuil plus bas pour les régions de la liste ci-dessous. Défaut : <strong>{{ $defaults['popularity_threshold_africa'] }}</strong>.
                            </small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Plafond quotidien
                                <span class="text-muted">(appels)</span>
                            </label>
                            <input type="number" name="rule_engine_daily_cap" class="form-control"
                                   value="{{ $current['rule_engine_daily_cap'] ?? $defaults['rule_engine_daily_cap'] }}"
                                   min="1" max="100000">
                            <small class="form-text text-muted">
                                Plafond global des appels traducteur lancés par le moteur de règles par jour.
                                Défaut : <strong>{{ $defaults['rule_engine_daily_cap'] }}</strong>.
                                Aujourd'hui : <strong>{{ $callsToday }}</strong> utilisés.
                            </small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Régions à traduire systématiquement (CSV)</label>
                            <input type="text" name="region_force_both" class="form-control"
                                   value="{{ $current['region_force_both'] ?? $defaults['region_force_both'] }}"
                                   placeholder="africa">
                            <small class="form-text text-muted">
                                Liste séparée par virgules : <code>africa</code>, <code>africa,americas</code>, etc.
                                Saisir <code>none</code> pour désactiver la règle. Les articles de ces régions utilisent
                                le seuil bas et sont systématiquement candidats. Défaut : <strong>{{ $defaults['region_force_both'] }}</strong>.
                            </small>
                        </div>
                    </div>
                </x-core::card.body>
            </x-core::card>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('grimba.translation-rules.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
@stop
