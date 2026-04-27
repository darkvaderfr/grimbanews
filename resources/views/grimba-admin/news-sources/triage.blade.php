@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <a href="{{ route('grimba.news-sources.index') }}">Sources</a>
            <span>Triage</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">File de classification</span>
                <h1 class="grimba-admin-title">Sources à classer</h1>
                <p class="grimba-admin-copy">
                    Classez les sources créées automatiquement avant qu'elles influencent la distribution des biais publics et les synthèses NobuAI.
                </p>
            </div>
            <span class="grimba-admin-status">{{ $rows->count() }} en attente</span>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <x-core::card.title>
                    GrimbaNews — Sources à classer
                    <small class="text-muted">{{ $rows->count() }}</small>
                </x-core::card.title>
                <a href="{{ route('grimba.news-sources.index') }}" class="btn btn-outline-secondary btn-sm">
                    ← Toutes les sources
                </a>
            </x-core::card.header>

            <x-core::card.body>
                <div class="grimba-admin-section mb-4">
                    <p class="text-muted small mb-0">
                        Sources créées automatiquement par l'ingest (RSS ou NewsAPI) sans biais classé.
                        Donnez à chacune un biais L/C/R, un type de propriété et un score de crédibilité (0-100).
                        Référencer <a href="https://www.allsides.com/media-bias/media-bias-chart" target="_blank" rel="noopener">AllSides</a>
                        et <a href="https://adfontesmedia.com/interactive-media-bias-chart/" target="_blank" rel="noopener">Ad Fontes Media</a>
                        pour les outlets EN ; pour la presse francophone, juger sur la ligne éditoriale globale.
                    </p>
                </div>

                @if($rows->isEmpty())
                    <div class="grimba-admin-empty">
                        <div class="grimba-admin-empty__icon">OK</div>
                        <div class="grimba-admin-empty__title">File de triage vide</div>
                        <p class="grimba-admin-empty__copy">
                            Aucune source générée automatiquement n'attend une classification.
                        </p>
                        <div class="grimba-admin-empty__actions">
                            <a href="{{ route('grimba.news-sources.index') }}" class="btn btn-sm btn-primary">Toutes les sources</a>
                        </div>
                    </div>
                @else
                    <div class="table-responsive grimba-admin-table-responsive">
                    <table class="table table-hover align-middle grimba-admin-table">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th class="text-end">Articles</th>
                                <th>Échantillon</th>
                                <th>Biais</th>
                                <th>Score</th>
                                <th>Propriété</th>
                                <th>Crédibilité</th>
                                <th>Pays</th>
                                <th>Langue</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $r)
                            <tr data-source-id="{{ $r->id }}">
                                <td data-label="Source">
                                    <strong>{{ $r->name }}</strong>
                                    @if($r->website)<br><small class="text-muted">{{ $r->website }}</small>@endif
                                    @if($r->api_id)<br><small class="text-muted">api: <code>{{ $r->api_id }}</code></small>@endif
                                </td>
                                <td data-label="Articles" class="text-end">{{ $counts[$r->id] ?? 0 }}</td>
                                <td data-label="Échantillon">
                                    @foreach(($samples[$r->id] ?? []) as $s)
                                        <div class="small text-muted text-truncate" style="max-width:280px;" title="{{ $s }}">{{ $s }}</div>
                                    @endforeach
                                </td>
                                <td data-label="Biais" style="min-width:140px;">
                                    <select class="form-select form-select-sm" data-field="bias_rating">
                                        <option value="unknown" {{ $r->bias_rating === 'unknown' ? 'selected' : '' }}>—</option>
                                        <option value="left"   {{ $r->bias_rating === 'left'   ? 'selected' : '' }}>Gauche</option>
                                        <option value="center" {{ $r->bias_rating === 'center' ? 'selected' : '' }}>Centre</option>
                                        <option value="right"  {{ $r->bias_rating === 'right'  ? 'selected' : '' }}>Droite</option>
                                    </select>
                                </td>
                                <td data-label="Score" style="width:105px;">
                                    <input type="number" min="-2" max="2" step="0.1" class="form-control form-control-sm" data-field="bias_score" value="{{ $r->bias_score }}" placeholder="-2..2">
                                </td>
                                <td data-label="Propriété" style="min-width:140px;">
                                    <select class="form-select form-select-sm" data-field="ownership_type">
                                        <option value="" {{ ! $r->ownership_type ? 'selected' : '' }}>—</option>
                                        @foreach(['independent'=>'Indépendant','corporate'=>'Privé','state'=>'État','nonprofit'=>'Associatif'] as $k => $v)
                                            <option value="{{ $k }}" {{ $r->ownership_type === $k ? 'selected' : '' }}>{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td data-label="Crédibilité" style="width:90px;">
                                    <input type="number" min="0" max="100" class="form-control form-control-sm" data-field="credibility_score" value="{{ $r->credibility_score }}" placeholder="0-100">
                                </td>
                                <td data-label="Pays" style="width:80px;">
                                    <input type="text" class="form-control form-control-sm" data-field="country" value="{{ $r->country }}" placeholder="FR">
                                </td>
                                <td data-label="Langue" style="width:80px;">
                                    <input type="text" class="form-control form-control-sm" data-field="language" value="{{ $r->language }}" placeholder="fr">
                                </td>
                                <td data-label="Actions" class="grimba-admin-inline-actions">
                                    <button type="button" class="btn btn-sm btn-primary" data-save-row>Enregistrer</button>
                                    <span class="text-success small d-none" data-saved-flag>✓</span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </x-core::card.body>
        </x-core::card>
    </div>

    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value
                || '';

            document.querySelectorAll('[data-save-row]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const tr = btn.closest('tr');
                    if (! tr) return;
                    const sourceId = tr.dataset.sourceId;
                    const url = "{{ url('admin/grimba/news-sources') }}/" + sourceId + "/quick-classify";

                    const data = {};
                    tr.querySelectorAll('[data-field]').forEach(el => {
                        data[el.dataset.field] = el.value === '' ? null : el.value;
                    });

                    btn.disabled = true;
                    btn.textContent = '…';
                    try {
                        const r = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify(data),
                        });
                        if (! r.ok) throw new Error('HTTP ' + r.status);
                        const flag = tr.querySelector('[data-saved-flag]');
                        if (flag) {
                            flag.classList.remove('d-none');
                            setTimeout(() => flag.classList.add('d-none'), 2000);
                        }
                        // If the new bias isn't unknown, fade the row out
                        // (it would no longer match this triage filter).
                        if (data.bias_rating && data.bias_rating !== 'unknown') {
                            tr.style.transition = 'opacity .4s';
                            tr.style.opacity = '0.35';
                        }
                    } catch (e) {
                        alert('Erreur: ' + e.message);
                    } finally {
                        btn.disabled = false;
                        btn.textContent = 'Enregistrer';
                    }
                });
            });
        })();
    </script>
@endsection
