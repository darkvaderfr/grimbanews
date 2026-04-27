@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    $isEdit = (bool) $cluster;
    $action = $isEdit
        ? route('grimba.story-clusters.update', $cluster->id)
        : route('grimba.story-clusters.store');

    $biasColor = [
        'left'    => '#3b82f6',
        'center'  => '#a8a8a8',
        'right'   => '#ef4444',
        'unknown' => '#9ca3af',
    ];
    $biasLabel = [
        'left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite', 'unknown' => '—',
    ];
@endphp

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Story desk</span>
                <h1 class="grimba-admin-title">{{ $isEdit ? 'Edit story cluster' : 'New story cluster' }}</h1>
                <p class="grimba-admin-copy">
                    Curate story groupings, attach or detach articles, and keep the public comparison page coherent.
                </p>
            </div>
            <span class="grimba-admin-status">{{ $isEdit ? 'Cluster #' . $cluster->id : 'Create mode' }}</span>
        </section>

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
            <x-core::card class="mb-4">
                <x-core::card.header class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <x-core::card.title>NobuAI insights</x-core::card.title>
                        <p class="text-muted mb-0 small">Génère une synthèse façon Ground: faits confirmés, cadrages par biais, angle mort.</p>
                    </div>
                    <form method="POST" action="{{ route('grimba.story-clusters.nobuai-summary', $cluster->id) }}">
                        @csrf
                        <button type="submit"
                                class="btn btn-primary"
                                @disabled(! $nobuAiReady || $attached->where('status', 'published')->count() < 2)>
                            {{ $summaryInfo ? 'Régénérer' : 'Générer' }} l'insight NobuAI
                        </button>
                    </form>
                </x-core::card.header>
                <x-core::card.body>
                    @if(! $nobuAiReady)
                        <p class="text-muted mb-0">Aucun fournisseur LLM n'est configuré. Ajoutez une clé OpenAI, OpenRouter, Anthropic, xAI ou autre dans Traduction.</p>
                    @elseif($attached->where('status', 'published')->count() < 2)
                        <p class="text-muted mb-0">Ajoutez au moins deux articles publiés pour produire un insight multi-sources.</p>
                    @elseif($summaryInfo)
                        <div class="grimba-admin-section">
                            <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                                <strong>Insight actuel</strong>
                                <span class="text-muted small">
                                    {{ $summaryInfo->summary_generated_at ? \Carbon\Carbon::parse($summaryInfo->summary_generated_at)->diffForHumans() : 'date inconnue' }}
                                    @if($summaryInfo->summary_driver)
                                        · via {{ $summaryInfo->summary_driver }}
                                    @endif
                                </span>
                            </div>
                            <pre class="bg-light p-3 rounded mb-0" style="white-space:pre-wrap;">{{ $summaryInfo->summary_nobuai }}</pre>
                        </div>
                    @else
                        <p class="text-muted mb-0">Aucun insight généré pour ce dossier.</p>
                    @endif
                </x-core::card.body>
            </x-core::card>

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
