@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-900">
        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between">
                <x-core::card.title>GrimbaNews — NewsAPI</x-core::card.title>
                <div class="small text-muted">
                    @if($key)
                        Clé configurée · {{ $active ? 'actif' : 'pause' }}
                    @else
                        Clé non configurée
                    @endif
                </div>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <p class="text-muted small">
                    Pipeline d'ingest secondaire — récupère <strong>top-headlines</strong> (par pays) et
                    <strong>everything</strong> (recherche par mots-clés) toutes les 30 minutes,
                    dédoublonne par URL d'article (sha1) et crée des brouillons que la file d'éditeur
                    peut publier. Les sources connues (50+ outlets dans <code>NewsApiSourceBiasSeeder</code>)
                    sont mappées automatiquement avec leur biais L/C/R, propriétaire et crédibilité.
                    Les sources inconnues sont créées en <code>biais=unknown</code> pour révision manuelle.
                </p>

                <form method="POST" action="{{ route('grimba.newsapi.save') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">
                            <strong>Clé NewsAPI</strong>
                            <span class="text-muted small">(ou variable env <code>NEWSAPI_KEY</code>)</span>
                        </label>
                        <input type="password"
                               name="key"
                               class="form-control"
                               value="{{ $key }}"
                               autocomplete="off"
                               placeholder="abc123…">
                        <div class="form-text">Stocké dans la table <code>settings</code>. Compte gratuit : 1000 req/jour, délai de 24h sur <code>/everything</code>.</div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox"
                               class="form-check-input"
                               name="active"
                               id="newsapi-active"
                               value="1"
                               {{ $active ? 'checked' : '' }}>
                        <label class="form-check-label" for="newsapi-active">
                            <strong>Pipeline actif</strong>
                            <span class="text-muted small d-block">
                                Le cron vérifie ce drapeau avant chaque appel.
                            </span>
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><strong>Requêtes /everything</strong></label>
                        <textarea name="queries"
                                  class="form-control font-monospace"
                                  rows="3">{{ $queries }}</textarea>
                        <div class="form-text">
                            Une requête par ligne (ou virgules). Syntaxe NewsAPI : <code>OR</code>, <code>AND</code>, guillemets, parenthèses.
                            Exemple : <code>"intelligence artificielle" OR "IA générative"</code>.
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label"><strong>Langue /everything</strong></label>
                            <select name="language" class="form-select">
                                @foreach(['fr' => 'Français', 'en' => 'Anglais', 'es' => 'Espagnol', 'de' => 'Allemand', 'pt' => 'Portugais', 'it' => 'Italien'] as $code => $label)
                                    <option value="{{ $code }}" {{ $language === $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Pays /top-headlines</strong></label>
                            <input type="text"
                                   name="countries"
                                   class="form-control"
                                   value="{{ $countries }}"
                                   placeholder="fr,us,gb">
                            <div class="form-text">CSV ISO-2.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Fenêtre /everything</strong></label>
                            <input type="number"
                                   name="window"
                                   min="24" max="720"
                                   class="form-control"
                                   value="{{ $window }}">
                            <div class="form-text">Heures (24-720). Free tier indexe avec 24h de retard.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mt-4">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="button" class="btn btn-outline-secondary" id="newsapi-test-btn">
                            Tester la clé
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="newsapi-run-btn">
                            Lancer un fetch maintenant
                        </button>
                    </div>

                    <div id="newsapi-result" class="mt-3"></div>
                </form>
            </x-core::card.body>
        </x-core::card>
    </div>

    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value
                || '';
            const result = document.getElementById('newsapi-result');

            // Build alert via DOM nodes — never set innerHTML with
            // dynamic strings (NewsAPI source names + titles are
            // external content; treat them as untrusted).
            function clearResult() {
                while (result.firstChild) result.removeChild(result.firstChild);
            }
            function alertNode(kind) {
                const div = document.createElement('div');
                div.className = 'alert alert-' + kind;
                return div;
            }
            function setStatus(text, kind) {
                clearResult();
                const a = alertNode(kind);
                a.textContent = text;
                result.appendChild(a);
            }

            document.getElementById('newsapi-test-btn').addEventListener('click', async () => {
                setStatus('Test en cours…', 'info');
                try {
                    const r = await fetch(@json(route('grimba.newsapi.test')), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    });
                    const d = await r.json();
                    if (! d.ok) { setStatus('Erreur: ' + (d.error || 'Inconnue'), 'danger'); return; }

                    clearResult();
                    const a = alertNode('success');
                    const head = document.createElement('strong');
                    head.textContent = 'OK · ' + d.totalResults + ' articles disponibles.';
                    a.appendChild(head);

                    const ul = document.createElement('ul');
                    ul.className = 'mb-0 mt-2';
                    (d.samples || []).forEach(s => {
                        const li = document.createElement('li');
                        const src = document.createElement('strong');
                        src.textContent = s.source;
                        li.appendChild(src);
                        li.appendChild(document.createTextNode(' — ' + (s.title || '')));
                        ul.appendChild(li);
                    });
                    a.appendChild(ul);
                    result.appendChild(a);
                } catch (e) { setStatus('Erreur réseau: ' + e.message, 'danger'); }
            });

            document.getElementById('newsapi-run-btn').addEventListener('click', async () => {
                setStatus("Lancement de grimba:fetch-newsapi… (jusqu'à 30s)", 'info');
                try {
                    const r = await fetch(@json(route('grimba.newsapi.run')), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    });
                    const d = await r.json();
                    if (! d.ok) { setStatus('Erreur: ' + (d.error || 'exit ' + d.exitCode), 'danger'); return; }

                    clearResult();
                    const a = alertNode('success');
                    const head = document.createElement('strong');
                    head.textContent = 'Fetch terminé.';
                    a.appendChild(head);
                    const pre = document.createElement('pre');
                    pre.className = 'mb-0 mt-2 small bg-light p-2 rounded';
                    pre.textContent = d.output || '';
                    a.appendChild(pre);
                    result.appendChild(a);
                } catch (e) { setStatus('Erreur réseau: ' + e.message, 'danger'); }
            });
        })();
    </script>
@endsection
