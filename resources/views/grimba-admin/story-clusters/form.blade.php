@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    $isEdit = (bool) $cluster;
    $action = $isEdit
        ? route('grimba.story-clusters.update', $cluster->id)
        : route('grimba.story-clusters.store');

    $biasColor = [
        'left'    => '#3b82f6',
        'center'  => '#b39152',
        'right'   => '#ef4444',
        'unknown' => '#9ca3af',
    ];
    $biasLabel = [
        'left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite', 'unknown' => '—',
    ];
@endphp

@section('content')
    <div class="max-width-1200">
        @if(session('success_msg'))
            <div class="alert alert-success">{{ session('success_msg') }}</div>
        @endif

        <form method="POST" action="{{ $action }}" class="mb-4">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ $isEdit ? 'Modifier le dossier #' . $cluster->id : 'Nouveau dossier' }}
                    </x-core::card.title>
                </x-core::card.header>

                @if($errors->any())
                    <div class="alert alert-danger mx-3 mt-3 mb-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <x-core::card.body>
                    <div class="mb-3">
                        <label class="form-label">Titre du dossier<span class="text-danger">*</span></label>
                        <input name="topic" class="form-control"
                               value="{{ old('topic', $cluster->topic ?? '') }}" required maxlength="200">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description (interne)</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $cluster->description ?? '') }}</textarea>
                    </div>
                </x-core::card.body>

                <x-core::card.footer class="d-flex justify-content-between">
                    <a href="{{ route('grimba.story-clusters.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? 'Enregistrer le titre' : 'Créer le dossier' }}
                    </button>
                </x-core::card.footer>
            </x-core::card>
        </form>

        @if($isEdit)
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>Articles attachés ({{ $attached->count() }})</x-core::card.title>
                </x-core::card.header>
                <x-core::card.body>
                    @if($attached->isEmpty())
                        <p class="text-muted mb-0">Aucun article attaché. Ajoutez-en via le sélecteur ci-dessous.</p>
                    @else
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr><th>Biais</th><th>Source</th><th>Titre</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($attached as $p)
                                    @php $bc = $biasColor[$p->bias_rating] ?? '#9ca3af'; @endphp
                                    <tr>
                                        <td><span class="badge" style="background:{{ $bc }}22; color:{{ $bc }}; border:1px solid {{ $bc }}44;">{{ $biasLabel[$p->bias_rating] ?? '—' }}</span></td>
                                        <td>{{ $p->source_name ?? '—' }}</td>
                                        <td>{{ $p->name }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('grimba.story-clusters.detach', $cluster->id) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $p->id }}">
                                                <button class="btn btn-sm btn-outline-danger">Détacher</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </x-core::card.body>
            </x-core::card>

            <x-core::card class="mt-4">
                <x-core::card.header>
                    <x-core::card.title>Attacher un article</x-core::card.title>
                </x-core::card.header>
                <x-core::card.body>
                    <form method="POST" action="{{ route('grimba.story-clusters.attach', $cluster->id) }}" class="d-flex gap-2 align-items-center">
                        @csrf
                        <select name="post_id" class="form-select" required>
                            <option value="">— Choisir un article —</option>
                            @foreach($available as $p)
                                <option value="{{ $p->id }}">
                                    [{{ $biasLabel[$p->bias_rating] ?? '—' }}] {{ \Illuminate\Support\Str::limit($p->name, 80) }}
                                    @if($p->story_cluster_id) — actuellement dans dossier #{{ $p->story_cluster_id }} @endif
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Attacher</button>
                    </form>
                </x-core::card.body>
            </x-core::card>
        @endif
    </div>
@endsection
