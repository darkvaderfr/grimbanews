@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1100">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>NewsAPI</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Moteur d'ingest</span>
                <h1 class="grimba-admin-title">News provider pipeline</h1>
                <p class="grimba-admin-copy">
                    Pilotez les fournisseurs payants et gratuits, testez l'accès, surveillez les budgets et lancez une récupération sans quitter le backend éditorial.
                </p>
            </div>
            <span class="grimba-admin-status">
                NewsAPI {{ $key ? 'configured' : 'missing' }} · Webz {{ $webzKey ? 'configured' : 'missing' }} · {{ $newsApiStats['calls_today'] }}/{{ $newsApiStats['daily_budget'] }} NewsAPI calls today
            </span>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between">
                <x-core::card.title>GrimbaNews — providers</x-core::card.title>
                <div class="small text-muted">
                    NewsAPI {{ $active ? 'actif' : 'pause' }} · Webz.io {{ $webzActive ? 'actif' : 'pause' }}
                </div>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <div class="grimba-admin-section mb-4">
                    <p class="text-muted small mb-0">
                    Pipeline d'ingest secondaire — NewsAPI récupère <strong>top-headlines</strong> et
                    <strong>everything</strong>; Webz.io News API Lite ajoute une source live avec quota mensuel.
                    Les deux chemins passent par le même dédoublonnage URL canonique + titre/source avant de créer
                    un article, afin qu'un doublon fournisseur ne compte pas deux fois dans l'analyse des biais.
                    </p>
                </div>

                <div class="grimba-admin-section mb-4">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                        <div>
                            <h3 class="h5 mb-1">Webz.io live lane</h3>
                            <p class="text-muted small mb-0">
                                News API Lite: 1,000 appels/mois, 10 articles/appel. Le scheduler limite par jour et par mois avant de toucher le fournisseur.
                            </p>
                        </div>
                        <span class="badge {{ $webzActive && $webzKey ? 'bg-success' : 'bg-secondary' }}">
                            {{ $webzActive && $webzKey ? 'ready' : 'paused' }}
                        </span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <div class="grimba-admin-stat rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">Webz today</div>
                                <div class="fs-4 fw-semibold">{{ $liveProviderStats['webz_calls_today'] }}/{{ $liveProviderStats['webz_daily_budget'] }}</div>
                                <div class="text-muted small">max {{ $webzMaxCallsPerRun }} call/run</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="grimba-admin-stat rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">Webz month</div>
                                <div class="fs-4 fw-semibold">{{ $liveProviderStats['webz_calls_month'] }}/{{ $liveProviderStats['webz_monthly_budget'] }}</div>
                                <div class="text-muted small">hard cap under Lite quota</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="grimba-admin-stat rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">Webz 24h ingest</div>
                                <div class="fs-4 fw-semibold">{{ $liveProviderStats['webz_ingested_24h'] }}</div>
                                <div class="text-muted small">{{ $liveProviderStats['webz_deduped_24h'] }} deduped</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="grimba-admin-stat rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">Webz failures</div>
                                <div class="fs-4 fw-semibold">{{ $liveProviderStats['webz_failed_24h'] }}</div>
                                <div class="text-muted small">last 24h</div>
                            </div>
                        </div>
                    </div>

                    @if($recentLiveRuns->isNotEmpty())
                        <div class="table-responsive grimba-admin-table-responsive">
                            <table class="table table-sm align-middle grimba-admin-table">
                                <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th>Status</th>
                                        <th class="text-end">Returned</th>
                                        <th class="text-end">Ingested</th>
                                        <th class="text-end">Deduped</th>
                                        <th>When</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLiveRuns as $run)
                                        <tr>
                                            <td data-label="Provider">
                                                <strong>{{ $run->provider }}</strong>
                                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($run->query_label, 84) }}</div>
                                            </td>
                                            <td data-label="Status">
                                                <span class="badge {{ $run->status === 'ok' ? 'bg-success' : ($run->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}">
                                                    {{ $run->status }}
                                                </span>
                                                @if($run->error_message)
                                                    <div class="small text-danger">{{ \Illuminate\Support\Str::limit($run->error_message, 70) }}</div>
                                                @endif
                                            </td>
                                            <td data-label="Returned" class="text-end">{{ $run->returned_articles }}</td>
                                            <td data-label="Ingested" class="text-end">{{ $run->ingested_articles }}</td>
                                            <td data-label="Deduped" class="text-end">{{ $run->deduped_articles }}</td>
                                            <td data-label="When" class="small text-muted">
                                                {{ $run->started_at ? \Carbon\Carbon::parse($run->started_at)->diffForHumans() : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-secondary py-2 mb-0">
                            Aucun run live fournisseur enregistré. Lancez Webz.io une fois pour remplir ce ledger.
                        </div>
                    @endif
                </div>

                <div class="grimba-admin-section mb-4">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                        <div>
                            <h3 class="h5 mb-1">NewsAPI run ledger</h3>
                            <p class="text-muted small mb-0">
                                Suivi par appel: pays, catégorie, articles retournés, ingérés, dédoublonnés et budget quotidien.
                            </p>
                        </div>
                        <span class="badge {{ $newsApiStats['budget_pct'] >= 80 ? 'bg-warning text-dark' : 'bg-secondary' }}">
                            {{ $newsApiStats['budget_pct'] }}% budget
                        </span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <div class="grimba-admin-stat rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">Calls today</div>
                                <div class="fs-4 fw-semibold">{{ $newsApiStats['calls_today'] }}/{{ $newsApiStats['daily_budget'] }}</div>
                                <div class="progress mt-2" style="height: 7px;">
                                    <div class="progress-bar" style="width: {{ $newsApiStats['budget_pct'] }}%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="grimba-admin-stat rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">Planned/run</div>
                                <div class="fs-4 fw-semibold">{{ $newsApiStats['planned_calls'] }}</div>
                                <div class="text-muted small">Cap: {{ $newsApiStats['max_calls_per_run'] }} calls</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="grimba-admin-stat rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">24h ingest</div>
                                <div class="fs-4 fw-semibold">{{ $newsApiStats['ingested_24h'] }}</div>
                                <div class="text-muted small">{{ $newsApiStats['returned_24h'] }} returned</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="grimba-admin-stat rounded-3 p-3 h-100">
                                <div class="text-muted small text-uppercase">24h dedupe</div>
                                <div class="fs-4 fw-semibold">{{ $newsApiStats['deduped_24h'] }}</div>
                                <div class="text-muted small">{{ $newsApiStats['failed_24h'] }} failed calls</div>
                            </div>
                        </div>
                    </div>

                    @if($recentRuns->isNotEmpty())
                        <div class="table-responsive grimba-admin-table-responsive">
                            <table class="table table-sm align-middle grimba-admin-table">
                                <thead>
                                    <tr>
                                        <th>Scope</th>
                                        <th>Status</th>
                                        <th class="text-end">Returned</th>
                                        <th class="text-end">Ingested</th>
                                        <th class="text-end">Deduped</th>
                                        <th>When</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRuns as $run)
                                        <tr>
                                            <td data-label="Scope">
                                                <strong>{{ $run->endpoint }}</strong>
                                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($run->query_label, 80) }}</div>
                                            </td>
                                            <td data-label="Status">
                                                <span class="badge {{ $run->status === 'ok' ? 'bg-success' : ($run->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}">
                                                    {{ $run->status }}
                                                </span>
                                                @if($run->error_message)
                                                    <div class="small text-danger">{{ \Illuminate\Support\Str::limit($run->error_message, 70) }}</div>
                                                @endif
                                            </td>
                                            <td data-label="Returned" class="text-end">{{ $run->returned_articles }}</td>
                                            <td data-label="Ingested" class="text-end">{{ $run->ingested_articles }}</td>
                                            <td data-label="Deduped" class="text-end">{{ $run->deduped_articles }}</td>
                                            <td data-label="When" class="small text-muted">
                                                {{ $run->started_at ? \Carbon\Carbon::parse($run->started_at)->diffForHumans() : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-secondary py-2 mb-0">
                            Aucun run NewsAPI enregistré. Le prochain fetch remplira ce ledger.
                        </div>
                    @endif
                </div>

                <div class="grimba-admin-section mb-4">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                        <div>
                            <h3 class="h5 mb-1">NewsAPI draft readiness</h3>
                            <p class="text-muted small mb-0">Même garde-fous que RSS : source, biais, traduction et extrait avant publication.</p>
                        </div>
                        <span class="badge bg-secondary">{{ $newsApiDrafts->count() }} brouillon(s)</span>
                    </div>
                    <div class="alert alert-secondary py-2">
                        <strong>Blockers NewsAPI:</strong>
                        {{ $guardrailStats['blocked'] }} bloqué(s), {{ $guardrailStats['ready'] }} prêt(s).
                        @foreach($guardrailStats['reasons'] as $reason => $count)
                            @if($count > 0)
                                @php
                                    $fixUrl = match ($reason) {
                                        'source manquante', 'biais inconnu' => route('grimba.news-sources.triage'),
                                        'traduction manquante' => route('grimba.translation.index'),
                                        default => route('grimba.newsapi.index'),
                                    };
                                @endphp
                                <a href="{{ $fixUrl }}" class="badge bg-warning text-dark ms-1 text-decoration-none">{{ $reason }} {{ $count }}</a>
                            @endif
                        @endforeach
                    </div>

                    @if($newsApiDrafts->isEmpty())
                        <div class="grimba-admin-empty">
                            <div class="grimba-admin-empty__icon">API</div>
                            <div class="grimba-admin-empty__title">Aucun brouillon NewsAPI en attente</div>
                            <p class="grimba-admin-empty__copy">
                                Lancez une récupération manuelle ou ajustez les requêtes si le flux secondaire doit alimenter la rédaction maintenant.
                            </p>
                            <div class="grimba-admin-empty__actions">
                                <form method="POST" action="{{ route('grimba.newsapi.run') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">Lancer NewsAPI</button>
                                </form>
                                <a href="{{ route('grimba.news-sources.triage') }}" class="btn btn-sm btn-outline-primary">Classer les sources</a>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('grimba.newsapi.publish-drafts') }}">
                            @csrf
                            <div class="table-responsive grimba-admin-table-responsive">
                                <table class="table table-sm align-middle grimba-admin-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 34px;"></th>
                                            <th>Article</th>
                                            <th>Source</th>
                                            <th>Biais</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($newsApiDrafts as $draft)
                                            @php
                                                $flags = grimba_newsapi_draft_guardrails($draft);
                                                $isReady = empty($flags);
                                                $biasLabel = ['left'=>'Gauche','center'=>'Centre','right'=>'Droite','unknown'=>'—'][$draft->bias_rating] ?? '—';
                                            @endphp
                                            <tr @class(['table-warning' => ! $isReady])>
                                                <td data-label="Sélection">
                                                    <input type="checkbox" name="ids[]" value="{{ $draft->id }}" class="form-check-input" @disabled(! $isReady)>
                                                </td>
                                                <td data-label="Article">
                                                    <a href="{{ route('posts.edit', $draft->id) }}" target="_blank">
                                                        <strong>{{ \Illuminate\Support\Str::limit($draft->name, 86) }}</strong>
                                                    </a>
                                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags((string) $draft->description), 120) }}</div>
                                                    @php
                                                        $imageMethod = trim((string) ($draft->image_extraction_method ?? ''));
                                                        $imageSource = trim((string) ($draft->image_source_url ?? ''));
                                                        $imageError = trim((string) ($draft->image_extract_error ?? ''));
                                                        $fullError = trim((string) ($draft->full_extract_error ?? ''));
                                                    @endphp
                                                    @if($imageMethod !== '' || $imageSource !== '' || $imageError !== '')
                                                        <div class="small mt-2 d-flex flex-wrap gap-1 align-items-center">
                                                            <span class="badge bg-secondary">Image provenance</span>
                                                            @if($imageMethod !== '')
                                                                <span class="badge bg-light text-dark border">méthode {{ $imageMethod }}</span>
                                                            @endif
                                                            @if($imageError !== '')
                                                                <span class="badge bg-danger">{{ \Illuminate\Support\Str::limit($imageError, 80) }}</span>
                                                            @endif
                                                            @if($imageSource !== '')
                                                                <a href="{{ $imageSource }}" target="_blank" rel="noopener" class="badge bg-light text-dark border text-decoration-none">source image</a>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($fullError !== '')
                                                        <div class="small mt-1 text-danger">
                                                            Échec extraction article: {{ \Illuminate\Support\Str::limit($fullError, 110) }}
                                                        </div>
                                                    @endif
                                                    @if(! $isReady)
                                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                                            @foreach($flags as $flag)
                                                                @php
                                                                    $fixUrl = match ($flag) {
                                                                        'source manquante', 'biais inconnu' => route('grimba.news-sources.triage'),
                                                                        'traduction manquante' => route('grimba.translation.index'),
                                                                        default => route('posts.edit', $draft->id),
                                                                    };
                                                                @endphp
                                                                <a href="{{ $fixUrl }}" class="badge bg-warning text-dark text-decoration-none">{{ $flag }}</a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="small text-success mt-1">Prêt à publier</div>
                                                    @endif
                                                </td>
                                                <td data-label="Source" class="small">{{ $draft->source_name ?: '—' }}</td>
                                                <td data-label="Biais" class="small">{{ $biasLabel }}</td>
                                                <td data-label="Actions" class="text-end grimba-admin-inline-actions">
                                                    <a href="{{ route('posts.edit', $draft->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">Éditer</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-sm btn-success">
                                Publier les prêts
                            </button>
                            <span class="text-muted small ms-2">Les brouillons signalés restent bloqués côté serveur.</span>
                        </form>
                    @endif
                </div>

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

                    <div class="mb-4">
                        <label class="form-label">
                            <strong>Clé Webz.io News API Lite</strong>
                            <span class="text-muted small">(ou <code>WEBZ_NEWS_API_LITE_TOKEN</code>)</span>
                        </label>
                        <input type="password"
                               name="webz_key"
                               class="form-control"
                               value="{{ $webzKey }}"
                               autocomplete="off"
                               placeholder="webz token…">
                        <div class="form-text">
                            Quota Lite: 1,000 appels/mois, 10 articles/appel. Le token reste côté serveur et n'est jamais exposé au front public.
                        </div>
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

                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="webz_active" value="0">
                        <input type="checkbox"
                               class="form-check-input"
                               name="webz_active"
                               id="webz-active"
                               value="1"
                               {{ $webzActive ? 'checked' : '' }}>
                        <label class="form-check-label" for="webz-active">
                            <strong>Webz.io actif</strong>
                            <span class="text-muted small d-block">
                                Ajoute Webz.io au flux <code>grimba:fetch-breaking</code> avec garde-fous de coût.
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

                    <div class="mb-4">
                        <label class="form-label"><strong>Requêtes Webz.io</strong></label>
                        <textarea name="webz_queries"
                                  class="form-control font-monospace"
                                  rows="4">{{ $webzQueries }}</textarea>
                        <div class="form-text">
                            Une requête par ligne. Le scheduler tourne les requêtes au fil du mois pour éviter de consommer le quota sur un seul sujet.
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
                                   placeholder="fr,us,gb,ca">
                            <div class="form-text">CSV ISO-2.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Catégories /top-headlines</strong></label>
                            <input type="text"
                                   name="categories"
                                   class="form-control"
                                   value="{{ $categories }}"
                                   placeholder="business,entertainment,general,health,science,sports,technology">
                            <div class="form-text">Balayées 5 fois par jour pour chaque pays configuré.</div>
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
                        <div class="col-md-4">
                            <label class="form-label"><strong>Budget quotidien</strong></label>
                            <input type="number"
                                   name="daily_budget"
                                   min="1" max="100000"
                                   class="form-control"
                                   value="{{ $dailyBudget }}">
                            <div class="form-text">Arrête les appels avant dépassement du quota fournisseur.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Max calls/run</strong></label>
                            <input type="number"
                                   name="max_calls_per_run"
                                   min="1" max="200"
                                   class="form-control"
                                   value="{{ $maxCallsPerRun }}">
                            <div class="form-text">Cap de sécurité pour éviter une explosion pays × catégories.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Webz budget quotidien</strong></label>
                            <input type="number"
                                   name="webz_daily_budget"
                                   min="1" max="1000"
                                   class="form-control"
                                   value="{{ $webzDailyBudget }}">
                            <div class="form-text">Recommandé: 30 appels/jour pour rester sous 1,000/mois.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Webz budget mensuel</strong></label>
                            <input type="number"
                                   name="webz_monthly_budget"
                                   min="1" max="1000"
                                   class="form-control"
                                   value="{{ $webzMonthlyBudget }}">
                            <div class="form-text">Hard cap local; protège le quota Lite.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Webz max calls/run</strong></label>
                            <input type="number"
                                   name="webz_max_calls_per_run"
                                   min="1" max="10"
                                   class="form-control"
                                   value="{{ $webzMaxCallsPerRun }}">
                            <div class="form-text">Le cron live tourne toutes les 15 minutes.</div>
                        </div>
                    </div>

                    <div class="grimba-admin-actions mt-4">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="button" class="btn btn-outline-secondary" id="newsapi-test-btn">
                            Tester la clé
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="newsapi-run-btn">
                            Lancer un fetch maintenant
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="webz-run-btn">
                            Lancer Webz.io maintenant
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

            document.getElementById('webz-run-btn').addEventListener('click', async () => {
                setStatus("Lancement de grimba:fetch-breaking --provider=webz…", 'info');
                try {
                    const r = await fetch(@json(route('grimba.newsapi.run-live')), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    });
                    const d = await r.json();
                    if (! d.ok) { setStatus('Erreur: ' + (d.error || 'exit ' + d.exitCode), 'danger'); return; }

                    clearResult();
                    const a = alertNode('success');
                    const head = document.createElement('strong');
                    head.textContent = 'Fetch Webz.io terminé.';
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
