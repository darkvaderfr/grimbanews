@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <a href="{{ route('grimba.news-sources.index') }}">Sources</a>
            <span>Classification</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Table de classification</span>
                <h1 class="grimba-admin-title">Sources classées par crédibilité</h1>
                <p class="grimba-admin-copy">
                    Corrigez les signaux éditoriaux au même endroit: biais, propriété, propriétaire, crédibilité, pays et langue.
                </p>
            </div>
            <span class="grimba-admin-status">{{ $stats['missing_credibility'] }} sans score</span>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <x-core::card.title>
                    GrimbaNews — Classification sources
                    <small class="text-muted">{{ $sources->total() }}</small>
                </x-core::card.title>
                <div class="grimba-admin-actions">
                    <form method="GET" action="{{ route('grimba.news-sources.classification') }}" class="d-flex">
                        <input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Source, propriétaire, pays…">
                    </form>
                    <a href="{{ route('grimba.news-sources.triage') }}" class="btn btn-outline-primary btn-sm">
                        Sources à classer
                    </a>
                    <a href="{{ route('grimba.news-sources.index') }}" class="btn btn-outline-secondary btn-sm">
                        Toutes les sources
                    </a>
                </div>
            </x-core::card.header>

            <x-core::card.body>
                <div class="row g-2 mb-4">
                    <div class="col-md-3">
                        <div class="grimba-admin-stat rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase">Sources suivies</div>
                            <div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="grimba-admin-stat rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase">Biais inconnu</div>
                            <div class="fs-4 fw-semibold">{{ $stats['unknown_bias'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="grimba-admin-stat rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase">Crédibilité manquante</div>
                            <div class="fs-4 fw-semibold">{{ $stats['missing_credibility'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="grimba-admin-stat rounded-3 p-3 h-100">
                            <div class="text-muted small text-uppercase">Pays manquant</div>
                            <div class="fs-4 fw-semibold">{{ $stats['missing_country'] }}</div>
                        </div>
                    </div>
                </div>

                @if($sources->isEmpty())
                    <div class="grimba-admin-empty">
                        <div class="grimba-admin-empty__icon">SRC</div>
                        <div class="grimba-admin-empty__title">Aucune source trouvée</div>
                        <p class="grimba-admin-empty__copy">
                            Ajustez la recherche ou revenez à la liste complète pour reprendre la classification.
                        </p>
                        <div class="grimba-admin-empty__actions">
                            <a href="{{ route('grimba.news-sources.classification') }}" class="btn btn-sm btn-primary">Réinitialiser</a>
                            <a href="{{ route('grimba.news-sources.index') }}" class="btn btn-sm btn-outline-primary">Toutes les sources</a>
                        </div>
                    </div>
                @else
                    <div class="table-responsive grimba-admin-table-responsive">
                        <table class="table table-hover align-middle grimba-admin-table">
                            <thead>
                                <tr>
                                    <th>Source</th>
                                    <th class="text-end">Articles</th>
                                    <th>Biais</th>
                                    <th>Score</th>
                                    <th>Propriété</th>
                                    <th>Propriétaire</th>
                                    <th>Crédibilité</th>
                                    <th>Pays</th>
                                    <th>Langue</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sources as $src)
                                    <tr data-source-id="{{ $src->id }}">
                                        <td data-label="Source">
                                            <strong>{{ $src->name }}</strong>
                                            @if($src->website)
                                                <br><small class="text-muted">{{ $src->website }}</small>
                                            @endif
                                        </td>
                                        <td data-label="Articles" class="text-end">{{ $articleCounts[$src->id] ?? 0 }}</td>
                                        <td data-label="Biais" style="min-width:140px;">
                                            <select class="form-select form-select-sm" data-field="bias_rating">
                                                <option value="unknown" {{ $src->bias_rating === 'unknown' ? 'selected' : '' }}>—</option>
                                                <option value="left" {{ $src->bias_rating === 'left' ? 'selected' : '' }}>Gauche</option>
                                                <option value="center" {{ $src->bias_rating === 'center' ? 'selected' : '' }}>Centre</option>
                                                <option value="right" {{ $src->bias_rating === 'right' ? 'selected' : '' }}>Droite</option>
                                            </select>
                                        </td>
                                        <td data-label="Score" style="width:105px;">
                                            <input type="number" min="-2" max="2" step="0.1" class="form-control form-control-sm" data-field="bias_score" value="{{ $src->bias_score }}" placeholder="-2..2">
                                        </td>
                                        <td data-label="Propriété" style="min-width:140px;">
                                            <select class="form-select form-select-sm" data-field="ownership_type">
                                                <option value="" {{ ! $src->ownership_type ? 'selected' : '' }}>—</option>
                                                @foreach(['independent'=>'Indépendant','corporate'=>'Privé','state'=>'État','nonprofit'=>'Associatif'] as $key => $label)
                                                    <option value="{{ $key }}" {{ $src->ownership_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td data-label="Propriétaire" style="min-width:180px;">
                                            <input type="text" class="form-control form-control-sm" data-field="owner_name" value="{{ $src->owner_name }}" placeholder="Groupe / famille / État">
                                        </td>
                                        <td data-label="Crédibilité" style="width:105px;">
                                            <input type="number" min="0" max="100" class="form-control form-control-sm" data-field="credibility_score" value="{{ $src->credibility_score }}" placeholder="0-100">
                                        </td>
                                        <td data-label="Pays" style="width:82px;">
                                            <input type="text" maxlength="3" class="form-control form-control-sm text-uppercase" data-field="country" value="{{ $src->country }}" placeholder="FR">
                                        </td>
                                        <td data-label="Langue" style="width:82px;">
                                            <input type="text" maxlength="5" class="form-control form-control-sm text-lowercase" data-field="language" value="{{ $src->language }}" placeholder="fr">
                                        </td>
                                        <td data-label="Actions" class="text-end grimba-admin-inline-actions">
                                            <button type="button" class="btn btn-sm btn-primary" data-save-row>Enregistrer</button>
                                            <a href="{{ route('grimba.news-sources.edit', $src->id) }}" class="btn btn-sm btn-outline-secondary">Détails</a>
                                            <span class="text-success small d-none" data-saved-flag>✓</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-core::card.body>

            <x-core::card.footer>
                {!! $sources->links() !!}
            </x-core::card.footer>
        </x-core::card>
    </div>

    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const base = @json(url(BaseHelper::getAdminPrefix() . '/grimba/news-sources'));

            document.querySelectorAll('[data-save-row]').forEach(button => {
                button.addEventListener('click', async () => {
                    const row = button.closest('tr');
                    if (! row) return;

                    const payload = {};
                    row.querySelectorAll('[data-field]').forEach(field => {
                        payload[field.dataset.field] = field.value === '' ? null : field.value;
                    });

                    button.disabled = true;
                    button.textContent = '…';

                    try {
                        const response = await fetch(base + '/' + row.dataset.sourceId + '/quick-classify', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify(payload),
                        });

                        if (! response.ok) throw new Error('HTTP ' + response.status);

                        const flag = row.querySelector('[data-saved-flag]');
                        if (flag) {
                            flag.classList.remove('d-none');
                            setTimeout(() => flag.classList.add('d-none'), 2000);
                        }
                    } catch (error) {
                        alert('Erreur: ' + error.message);
                    } finally {
                        button.disabled = false;
                        button.textContent = 'Enregistrer';
                    }
                });
            });
        })();
    </script>
@endsection
