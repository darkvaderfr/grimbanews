@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Source intelligence</span>
                <h1 class="grimba-admin-title">Sources suivies</h1>
                <p class="grimba-admin-copy">
                    Review every outlet GrimbaNews knows about, classify unknown sources, and keep bias/ownership signals reliable.
                </p>
            </div>
            <span class="grimba-admin-status">{{ $unknownCount }} à classer</span>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <x-core::card.title>GrimbaNews — Sources suivies</x-core::card.title>

                <div class="d-flex gap-2 align-items-center">
                    <form method="GET" action="{{ route('grimba.news-sources.index') }}" class="d-flex">
                        <input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Chercher…">
                    </form>
                    <a href="{{ route('grimba.news-sources.triage') }}" class="btn btn-outline-primary btn-sm">
                        Sources à classer
                        @if($unknownCount > 0)
                            <span class="badge text-bg-danger ms-1">{{ $unknownCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('grimba.news-sources.create') }}" class="btn btn-primary btn-sm">
                        + Nouvelle source
                    </a>
                </div>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Site</th>
                                <th>Biais</th>
                                <th>Propriété</th>
                                <th class="text-end">Crédibilité</th>
                                <th>Pays</th>
                                <th>Langue</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sources as $src)
                                <tr>
                                    <td><strong>{{ $src->name }}</strong></td>
                                    <td>
                                        @if($src->website)
                                            <a href="https://{{ $src->website }}" target="_blank" rel="noopener">{{ $src->website }}</a>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $biasLabel = ['left'=>'Gauche','center'=>'Centre','right'=>'Droite','unknown'=>'—'][$src->bias_rating] ?? '—';
                                            $biasColor = ['left'=>'#3b82f6','center'=>'#a8a8a8','right'=>'#ef4444','unknown'=>'#9ca3af'][$src->bias_rating] ?? '#9ca3af';
                                        @endphp
                                        <span class="badge" style="background: {{ $biasColor }}22; color: {{ $biasColor }}; border: 1px solid {{ $biasColor }}44;">
                                            {{ $biasLabel }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ ['state'=>'État','corporate'=>'Privé','independent'=>'Indépendant','nonprofit'=>'Associatif'][$src->ownership_type] ?? '—' }}</td>
                                    <td class="text-end">{{ $src->credibility_score ?? '—' }}</td>
                                    <td>{{ $src->country ?? '—' }}</td>
                                    <td class="text-uppercase">{{ $src->language ?? '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('grimba.news-sources.edit', $src->id) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                        <form method="POST" action="{{ route('grimba.news-sources.destroy', $src->id) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Supprimer {{ addslashes($src->name) }} ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Aucune source trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-core::card.body>

            <x-core::card.footer>
                {!! $sources->links() !!}
            </x-core::card.footer>
        </x-core::card>
    </div>
@endsection
