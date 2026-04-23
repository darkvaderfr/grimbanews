@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <x-core::card.title>GrimbaNews — Dossiers (story clusters)</x-core::card.title>
                <a href="{{ route('grimba.story-clusters.create') }}" class="btn btn-primary btn-sm">
                    + Nouveau dossier
                </a>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
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
                                    <td class="text-muted">{{ $c->id }}</td>
                                    <td><strong>{{ $c->topic }}</strong></td>
                                    <td class="text-end">{{ $c->total }}</td>
                                    <td style="min-width:170px;">
                                        <span style="color:#3b82f6;">● {{ $c->spread['left'] }}</span>
                                        <span style="color:#b39152;" class="mx-2">● {{ $c->spread['center'] }}</span>
                                        <span style="color:#ef4444;">● {{ $c->spread['right'] }}</span>
                                        @if($c->spread['unknown'] > 0)
                                            <span class="text-muted ms-2">(+{{ $c->spread['unknown'] }} ?)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $barStatus[1] }}">{{ $barStatus[0] }}</span>
                                    </td>
                                    <td class="text-end">
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
                                    <td colspan="6" class="text-center text-muted py-4">Aucun dossier pour l'instant.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>
@endsection
