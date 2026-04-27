@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    $isEdit = (bool) $source;
    $action = $isEdit
        ? route('grimba.news-sources.update', $source->id)
        : route('grimba.news-sources.store');
@endphp

@section('content')
    <div class="grimba-admin-screen max-width-1000">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <a href="{{ route('grimba.news-sources.index') }}">Sources</a>
            <span>{{ $isEdit ? 'Modifier' : 'Créer' }}</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Registre sources</span>
                <h1 class="grimba-admin-title">{{ $isEdit ? 'Modifier la source' : 'Nouvelle source' }}</h1>
                <p class="grimba-admin-copy">
                    Maintenez les signaux de biais, propriété, langue et crédibilité utilisés dans les comparatifs.
                </p>
            </div>
            <span class="grimba-admin-status">{{ $isEdit ? 'Editing #' . $source->id : 'Create mode' }}</span>
        </section>

        <form method="POST" action="{{ $action }}" class="grimba-admin-form">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ $isEdit ? 'Modifier la source' : 'Nouvelle source' }}
                    </x-core::card.title>
                </x-core::card.header>

                @if(session('success_msg'))
                    <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
                @endif

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
                    <section class="grimba-admin-form-section">
                        <h2 class="grimba-admin-form-section__title">Identité et classification</h2>
                        <p class="grimba-admin-form-section__hint mb-3">
                            Ces champs alimentent les comparatifs publics, les garde-fous de publication et les diagnostics de couverture.
                        </p>
                        <div class="row g-3">
                            <div class="col-md-8">
                            <label class="form-label">Nom<span class="text-danger">*</span></label>
                            <input name="name" class="form-control"
                                   value="{{ old('name', $source->name ?? '') }}" required maxlength="120">
                            </div>
                            <div class="col-md-4">
                            <label class="form-label">Site web</label>
                            <input name="website" class="form-control"
                                   value="{{ old('website', $source->website ?? '') }}"
                                   placeholder="lemonde.fr">
                            </div>

                            <div class="col-md-3">
                            <label class="form-label">Biais<span class="text-danger">*</span></label>
                            <select name="bias_rating" class="form-select" required>
                                @foreach(['left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite', 'unknown' => 'Non évalué'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('bias_rating', $source->bias_rating ?? 'unknown') === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                            </div>

                            <div class="col-md-3">
                            <label class="form-label">Score biais (-2 à +2)</label>
                            <input name="bias_score" type="number" min="-2" max="2" step="0.1" class="form-control"
                                   value="{{ old('bias_score', $source->bias_score ?? '') }}"
                                   placeholder="-2.0 gauche · 0 centre · +2.0 droite">
                            <small class="text-muted">Laissez vide pour utiliser la valeur par défaut du biais.</small>
                            </div>

                            <div class="col-md-3">
                            <label class="form-label">Type de propriété</label>
                            <select name="ownership_type" class="form-select">
                                <option value="">—</option>
                                @foreach(['state' => 'État', 'corporate' => 'Privé', 'independent' => 'Indépendant', 'nonprofit' => 'Associatif'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('ownership_type', $source->ownership_type ?? '') === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                            </div>

                            <div class="col-md-2">
                            <label class="form-label">Crédibilité (0–100)</label>
                            <input name="credibility_score" type="number" min="0" max="100" class="form-control"
                                   value="{{ old('credibility_score', $source->credibility_score ?? '') }}">
                            </div>

                            <div class="col-md-2">
                            <label class="form-label">Pays (code)</label>
                            <input name="country" class="form-control" maxlength="3"
                                   value="{{ old('country', $source->country ?? '') }}"
                                   placeholder="FR">
                            </div>

                            <div class="col-md-2">
                            <label class="form-label">Langue</label>
                            <input name="language" class="form-control" maxlength="5"
                                   value="{{ old('language', $source->language ?? '') }}"
                                   placeholder="fr">
                            </div>

                            <div class="col-12">
                            <label class="form-label">Notes internes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $source->notes ?? '') }}</textarea>
                            </div>
                        </div>
                    </section>
                </x-core::card.body>

                <x-core::card.footer class="grimba-admin-form-actions">
                    <a href="{{ route('grimba.news-sources.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? 'Enregistrer' : 'Créer la source' }}
                    </button>
                </x-core::card.footer>
            </x-core::card>
        </form>
    </div>
@endsection
