@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Dossiers</span>
        </nav>

        <section class="grimba-admin-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <span class="grimba-admin-kicker">Bureau dossiers</span>
                    <h1 class="grimba-admin-title">Dossiers actifs</h1>
                    <p class="grimba-admin-copy">
                        Regroupez les articles par histoire, vérifiez la balance gauche-centre-droite, puis ouvrez la carte de couverture pour traiter les angles manquants.
                    </p>
                </div>
                <div class="grimba-admin-actions justify-content-end">
                    <span class="grimba-admin-status">{{ $coverageStats['one_sided'] ?? 0 }} unilatéraux</span>
                    <span class="grimba-admin-status">{{ $coverageStats['partial'] ?? 0 }} partiels</span>
                    <a href="{{ route('grimba.coverage-map.index') }}" class="btn btn-outline-primary btn-sm">Carte couverture</a>
                    <a href="{{ route('grimba.story-clusters.create') }}" class="btn btn-primary btn-sm">+ Nouveau dossier</a>
                </div>
            </div>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <x-core::card.title>GrimbaNews — Dossiers (story clusters)</x-core::card.title>
                <a href="{{ route('grimba.coverage-map.index', ['filter' => 'gaps']) }}" class="btn btn-outline-primary btn-sm">Voir les gaps</a>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <div class="table-responsive grimba-admin-table-responsive">
                    <table class="table table-striped align-middle grimba-admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Titre du dossier</th>
                                <th class="text-end">Articles</th>
                                <th>Spread L / C / D</th>
                                <th>Statut barre</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clusters as $c)
                                @php
                                    $activeSides = 0;
                                    foreach (['left', 'center', 'right'] as $k) {
                                        if (($c->spread[$k] ?? 0) > 0) $activeSides++;
                                    }
                                    $barStatus = match ($activeSides) {
                                        3 => ['Équilibrée', 'success'],
                                        2 => ['Partielle', 'warning'],
                                        1 => ['Unilatérale', 'danger'],
                                        default => ['Vide', 'secondary'],
                                    };
                                @endphp
                                <tr>
                                    <td data-label="#" class="text-muted">{{ $c->id }}</td>
                                    <td data-label="Dossier"><strong>{{ $c->topic }}</strong></td>
                                    <td data-label="Articles" class="text-end">{{ $c->total }}</td>
                                    <td data-label="Spread" style="min-width:170px;">
                                        <span style="color:#3b82f6;">● {{ $c->spread['left'] }}</span>
                                        <span style="color:#a8a8a8;" class="mx-2">● {{ $c->spread['center'] }}</span>
                                        <span style="color:#ef4444;">● {{ $c->spread['right'] }}</span>
                                        @if($c->spread['unknown'] > 0)
                                            <span class="text-muted ms-2">(+{{ $c->spread['unknown'] }} ?)</span>
                                        @endif
                                    </td>
                                    <td data-label="Statut">
                                        <span class="badge text-bg-{{ $barStatus[1] }}">{{ $barStatus[0] }}</span>
                                    </td>
                                    <td data-label="Actions" class="text-end grimba-admin-inline-actions">
                                        <a href="{{ route('grimba.story-clusters.edit', $c->id) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                        @if($c->total > 0)
                                            <a href="{{ url('/comparatif/' . $c->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Voir</a>
                                        @endif
                                        <form method="POST" action="{{ route('grimba.story-clusters.destroy', $c->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Supprimer ce dossier ? Les articles seront détachés mais conservés.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="grimba-admin-empty">
                                            <div class="grimba-admin-empty__icon">DOS</div>
                                            <div class="grimba-admin-empty__title">Aucun dossier actif</div>
                                            <p class="grimba-admin-empty__copy">
                                                Créez un dossier pour regrouper les articles, suivre la balance gauche-centre-droite, et générer les insights NobuAI.
                                            </p>
                                            <div class="grimba-admin-empty__actions">
                                                <a href="{{ route('grimba.story-clusters.create') }}" class="btn btn-sm btn-primary">Nouveau dossier</a>
                                                <a href="{{ route('grimba.coverage-map.index') }}" class="btn btn-sm btn-outline-primary">Carte couverture</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>
@endsection
