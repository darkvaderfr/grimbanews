@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    $biasColor = ['left' => '#3b82f6', 'center' => '#a8a8a8', 'right' => '#e84c3d', 'unknown' => '#9ca3af'];
    $pct = [
        'left'   => $coverageTotal ? round($coverage['left']   * 100 / $coverageTotal) : 0,
        'center' => $coverageTotal ? round($coverage['center'] * 100 / $coverageTotal) : 0,
        'right'  => $coverageTotal ? round($coverage['right']  * 100 / $coverageTotal) : 0,
    ];

    $sparkMax = max(1, max(array_column($sparkline, 'n')));
@endphp

@section('content')
<div class="grimba-cockpit">

    {{-- Meta strip --}}
    <div class="grimba-cockpit__meta">
        <span class="grimba-cockpit__kicker">Aujourd'hui</span>
        <span>·</span>
        <strong>{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</strong>
        <span>·</span>
        <span>{{ $publishedToday }} {{ $publishedToday === 1 ? 'article publié' : 'articles publiés' }}</span>
        <span>·</span>
        <span>{{ $draftCount }} en brouillon</span>
    </div>

    <section class="grimba-cockpit__hero mb-3">
        <div>
            <span class="grimba-cockpit__kicker">Centre de commande</span>
            <h1>Tableau de bord éditorial</h1>
            <p>Surveillez la pression brouillons, la couverture, les traductions NobuAI et les dossiers actifs avant publication.</p>
        </div>
        <div class="grimba-cockpit__hero-actions grimba-admin-actions">
            <a href="{{ route('posts.create') }}" class="btn btn-primary">+ Nouvel article</a>
            <a href="{{ route('grimba.translation.index') }}" class="btn btn-outline-primary">Clés NobuAI</a>
            <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary">Voir le site →</a>
        </div>
    </section>

    <section class="grimba-kpi-grid mb-3">
        <article class="grimba-kpi">
            <span>Publiés</span>
            <strong>{{ number_format($publishedTotal) }}</strong>
            <small>{{ $publishedToday }} aujourd'hui</small>
        </article>
        <article class="grimba-kpi grimba-kpi--warn">
            <span>Brouillons</span>
            <strong>{{ number_format($draftCount) }}</strong>
            <small>à trier avant mise en ligne</small>
        </article>
        <article class="grimba-kpi">
            <span>Dossiers actifs</span>
            <strong>{{ number_format($activeClusterCount) }}</strong>
            <small>{{ number_format($clusterCount) }} dossiers au total</small>
        </article>
        <article class="grimba-kpi {{ $translationPending > 0 ? 'grimba-kpi--warn' : '' }}">
            <span>FR NobuAI</span>
            <strong>{{ number_format($translationReady) }}</strong>
            <small>{{ $translationPending }} traduction{{ $translationPending === 1 ? '' : 's' }} en attente</small>
        </article>
    </section>

    <section class="card grimba-ops-board mb-3">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <span class="grimba-cockpit__kicker">Operations board</span>
                <h3 class="card-title mt-2 mb-0">Ingest et files à surveiller</h3>
            </div>
            <div class="grimba-runbook-actions grimba-admin-actions">
                <form method="POST" action="{{ route('grimba.cockpit.runbook') }}">
                    @csrf
                    <input type="hidden" name="action" value="health">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Health</button>
                </form>
                <form method="POST" action="{{ route('grimba.cockpit.runbook') }}">
                    @csrf
                    <input type="hidden" name="action" value="rss_poll_one">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Poll 1 RSS</button>
                </form>
                <form method="POST" action="{{ route('grimba.cockpit.runbook') }}">
                    @csrf
                    <input type="hidden" name="action" value="newsapi_fetch">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Fetch NewsAPI</button>
                </form>
                <form method="POST" action="{{ route('grimba.cockpit.runbook') }}">
                    @csrf
                    <input type="hidden" name="action" value="nobuai_health">
                    <button type="submit" class="btn btn-sm btn-outline-primary">NobuAI health</button>
                </form>
                <form method="POST" action="{{ route('grimba.cockpit.runbook') }}">
                    @csrf
                    <input type="hidden" name="action" value="translate_fr">
                    <input type="hidden" name="limit" value="3">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Translate 3 FR</button>
                </form>
                <form method="POST" action="{{ route('grimba.cockpit.runbook') }}">
                    @csrf
                    <input type="hidden" name="action" value="translate_en">
                    <input type="hidden" name="limit" value="3">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Translate 3 EN</button>
                </form>
                <a href="{{ route('grimba.rss-feeds.index') }}" class="btn btn-sm btn-outline-primary">Ouvrir RSS</a>
            </div>
        </div>
        <div class="card-body">
            <div class="grimba-ops-grid">
                <a href="{{ route('grimba.rss-feeds.index') }}" class="grimba-ops-tile">
                    <span>RSS 24h</span>
                    <strong>{{ number_format($rssItems24) }}</strong>
                    <small>{{ $rssActive }} actifs · {{ $rssSick }} sick · dernier {{ $rssLastPoll ? \Carbon\Carbon::parse($rssLastPoll)->locale('fr')->diffForHumans() : 'jamais' }}</small>
                </a>
                <a href="{{ route('grimba.newsapi.index') }}" class="grimba-ops-tile {{ ! $newsApiActive || ! $newsApiConfigured ? 'is-warn' : '' }}">
                    <span>NewsAPI 24h</span>
                    <strong>{{ number_format($newsApiItems24) }}</strong>
                    <small>{{ $newsApiConfigured ? 'clé présente' : 'clé absente' }} · {{ $newsApiActive ? 'actif' : 'désactivé' }} · {{ $newsApiLastFetch ? \Carbon\Carbon::parse($newsApiLastFetch)->locale('fr')->diffForHumans() : 'jamais fetché' }}</small>
                </a>
                <a href="{{ route('grimba.rss-drafts.index') }}" class="grimba-ops-tile {{ $draftCount > 0 ? 'is-warn' : '' }}">
                    <span>Brouillons</span>
                    <strong>{{ number_format($draftCount) }}</strong>
                    <small>à relire avant publication</small>
                </a>
                <a href="{{ route('grimba.translation.index') }}" class="grimba-ops-tile {{ ($translationPending + $englishTranslationPending) > 0 ? 'is-warn' : '' }}">
                    <span>Pending translations</span>
                    <strong>{{ number_format($translationPending + $englishTranslationPending) }}</strong>
                    <small>{{ $translationPending }} vers FR · {{ $englishTranslationPending }} vers EN</small>
                </a>
                <a href="{{ route('grimba.story-clusters.index') }}" class="grimba-ops-tile {{ $nobuInsightPending > 0 ? 'is-warn' : '' }}">
                    <span>Pending insights</span>
                    <strong>{{ number_format($nobuInsightPending) }}</strong>
                    <small>{{ $nobuInsightReady }} dossiers prêts · {{ $nobuInsightStale }} stale</small>
                </a>
                <div class="grimba-ops-tile {{ $duplicateGroups > 0 ? 'is-warn' : '' }}">
                    <span>Duplicate groups</span>
                    <strong>{{ number_format($duplicateGroups) }}</strong>
                    <small>{{ $duplicateGroups > 0 ? 'lancer grimba:dedupe-posts --apply' : 'aucun groupe détecté' }}</small>
                </div>
                <div class="grimba-ops-tile {{ $ingestGuardrailStats['blocked'] > 0 ? 'is-warn' : '' }}">
                    <span>Draft blockers</span>
                    <strong>{{ number_format($ingestGuardrailStats['blocked']) }}</strong>
                    <small>
                        {{ $ingestGuardrailStats['ready'] }} prêts ·
                        <a href="{{ route('grimba.news-sources.triage') }}">source {{ $ingestGuardrailStats['reasons']['source manquante'] ?? 0 }}</a> ·
                        <a href="{{ route('grimba.news-sources.triage') }}">biais {{ $ingestGuardrailStats['reasons']['biais inconnu'] ?? 0 }}</a> ·
                        <a href="{{ route('grimba.translation.index') }}">trad {{ $ingestGuardrailStats['reasons']['traduction manquante'] ?? 0 }}</a> ·
                        <a href="{{ route('grimba.rss-drafts.index') }}">extrait {{ $ingestGuardrailStats['reasons']['extrait trop court'] ?? 0 }}</a>
                    </small>
                </div>
            </div>
        </div>
    </section>

    @if($automationStatus->isNotEmpty())
        <section class="card grimba-ops-board mb-3">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <span class="grimba-cockpit__kicker">Scheduler health</span>
                    <h3 class="card-title mt-2 mb-0">Automation run ledger</h3>
                </div>
                <span class="text-muted small">
                    {{ $automationStatus->where('is_failed', true)->count() }} failed ·
                    {{ $automationStatus->where('is_stale', true)->count() }} stale
                </span>
            </div>
            <div class="card-body">
                <div class="grimba-ops-grid">
                    @foreach($automationStatus as $job)
                        <div class="grimba-ops-tile {{ $job->is_failed || $job->is_stale ? 'is-warn' : '' }}">
                            <span>{{ $job->label }}</span>
                            <strong>{{ $job->status === 'never' ? 'Never run' : ucfirst($job->status) }}</strong>
                            <small>
                                {{ $job->finished_at ? $job->finished_at->locale('fr')->diffForHumans() : 'No completed run yet' }}
                                · every ~{{ $job->expected_minutes }}m
                                @if($job->duration_ms)
                                    · {{ round($job->duration_ms / 1000, 1) }}s
                                @endif
                            </small>
                            @if($job->error_message)
                                <small>{{ \Illuminate\Support\Str::limit($job->error_message, 90) }}</small>
                            @endif
                            <small style="font-family:var(--gn-font-mono);">{{ $job->command }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="row g-3 mb-3">
        {{-- Coverage balance --}}
        <div class="col-xl-7 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Équilibre de couverture — aujourd'hui</h3>
                </div>
                <div class="card-body">
                    @if($coverageTotal === 0)
                        <p class="text-muted mb-0">Aucun article publié aujourd'hui.</p>
                    @else
                        <div class="grimba-coverage__bar mb-3" style="display:flex;height:12px;border-radius:9999px;overflow:hidden;background:rgba(26,23,19,0.06);">
                            <div style="width:{{ $pct['left'] }}%;background:{{ $biasColor['left'] }};"></div>
                            <div style="width:{{ $pct['center'] }}%;background:{{ $biasColor['center'] }};"></div>
                            <div style="width:{{ $pct['right'] }}%;background:{{ $biasColor['right'] }};"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="color:{{ $biasColor['left'] }};font-weight:600;">● Gauche {{ $coverage['left'] }} ({{ $pct['left'] }}%)</span>
                            <span style="color:{{ $biasColor['center'] }};font-weight:600;">● Centre {{ $coverage['center'] }} ({{ $pct['center'] }}%)</span>
                            <span style="color:{{ $biasColor['right'] }};font-weight:600;">● Droite {{ $coverage['right'] }} ({{ $pct['right'] }}%)</span>
                            @if($coverage['unknown'] > 0)
                                <span class="text-muted">Non classés {{ $coverage['unknown'] }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Angles morts counter --}}
        <div class="col-xl-5 col-12">
            <div class="card grimba-status-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <span class="grimba-cockpit__kicker">NobuAI</span>
                            <h3 class="card-title mt-2 mb-1">Statut opérationnel</h3>
                            <p class="text-muted mb-0">Le public ne voit que NobuAI; les fournisseurs restent côté admin.</p>
                        </div>
                        <div class="grimba-status-card__count">{{ $blindspotCount }}</div>
                    </div>
                    <div class="grimba-provider-row">
                        <span>LLM</span>
                        <strong>{{ count($nobuDrivers) ? implode(' → ', $nobuDrivers) : 'aucune clé configurée' }}</strong>
                    </div>
                    <div class="grimba-provider-row">
                        <span>Traduction</span>
                        <strong>{{ count($translationDrivers) ? implode(' → ', $translationDrivers) : 'aucun fournisseur' }}</strong>
                    </div>
                    <div class="grimba-provider-row">
                        <span>Insights dossiers</span>
                        <strong>{{ $nobuInsightReady }} prêts · {{ $nobuInsightPending }} à générer · {{ $nobuInsightStale }} stale</strong>
                    </div>
                    <div class="grimba-provider-row">
                        <span>Dernier insight</span>
                        <strong>{{ $nobuInsightLatest ? \Carbon\Carbon::parse($nobuInsightLatest)->locale('fr')->diffForHumans() : 'jamais généré' }}</strong>
                    </div>
                    @if(! empty($nobuFailureDiagnostics))
                        <div class="grimba-provider-failures mt-3">
                            <span class="grimba-cockpit__kicker">Dernières erreurs NobuAI</span>
                            @foreach($nobuFailureDiagnostics as $failure)
                                <div class="grimba-provider-row">
                                    <span>{{ $failure['driver'] }} · {{ ! empty($failure['at']) ? \Carbon\Carbon::parse($failure['at'])->locale('fr')->diffForHumans() : 'date inconnue' }}</span>
                                    <strong>{{ $failure['message'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="d-flex gap-2 flex-wrap mt-3">
                        <a href="{{ route('grimba.translation.index') }}" class="btn btn-sm btn-primary">Configurer les clés</a>
                        <a href="{{ route('grimba.story-clusters.index') }}" class="btn btn-sm btn-outline-primary">Gérer les dossiers</a>
                        @if($nobuInsightPending > 0 && count($nobuDrivers))
                            <form method="POST" action="{{ route('grimba.cockpit.nobuai-summaries') }}" class="d-inline-flex gap-2 align-items-center">
                                @csrf
                                <input type="hidden" name="limit" value="3">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    Générer 3 insights
                                </button>
                            </form>
                        @endif
                        @if($nobuInsightStale > 0 && count($nobuDrivers))
                            <form method="POST" action="{{ route('grimba.cockpit.nobuai-summaries') }}" class="d-inline-flex gap-2 align-items-center">
                                @csrf
                                <input type="hidden" name="limit" value="3">
                                <input type="hidden" name="stale_only" value="1">
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    Rafraîchir 3 stale
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- Active dossiers --}}
        <div class="col-lg-7 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dossiers actifs</h3>
                </div>
                <div class="card-body">
                    @if($activeClusters->isEmpty())
                        <p class="text-muted mb-0">Aucun dossier avec articles publiés.</p>
                    @else
                        <ul class="list-unstyled mb-0">
                            @foreach($activeClusters as $c)
                                @php
                                    $total = max(1, array_sum($c->spread));
                                    $cp = [
                                        'left'   => round($c->spread['left']   * 100 / $total),
                                        'center' => round($c->spread['center'] * 100 / $total),
                                        'right'  => round($c->spread['right']  * 100 / $total),
                                    ];
                                @endphp
                                <li class="mb-3 pb-3" @if(!$loop->last) style="border-bottom:1px solid var(--gn-rule);" @endif>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <a href="{{ route('grimba.story-clusters.edit', $c->id) }}" class="text-decoration-none" style="color:var(--gn-ink);font-family:var(--gn-font-display);font-size:1.05rem;font-weight:600;">
                                            {{ $c->topic }}
                                        </a>
                                        <span class="text-muted small">{{ $c->post_count }} articles</span>
                                    </div>
                                    <div style="display:flex;height:6px;border-radius:9999px;overflow:hidden;background:rgba(26,23,19,0.06);">
                                        <div style="width:{{ $cp['left'] }}%;background:{{ $biasColor['left'] }};"></div>
                                        <div style="width:{{ $cp['center'] }}%;background:{{ $biasColor['center'] }};"></div>
                                        <div style="width:{{ $cp['right'] }}%;background:{{ $biasColor['right'] }};"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top sources --}}
        <div class="col-lg-5 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sources les plus citées (7 j)</h3>
                </div>
                <div class="card-body">
                    @if($topSources->isEmpty())
                        <p class="text-muted mb-0">Aucune source utilisée cette semaine.</p>
                    @else
                        @php $srcMax = max(1, $topSources->max('n')); @endphp
                        <ul class="list-unstyled mb-0">
                            @foreach($topSources as $s)
                                @php
                                    $score = (int) ($s->score ?? 0);
                                    $barColor = $score >= 85 ? '#22c55e' : ($score >= 70 ? '#eab308' : '#ef4444');
                                    $w = round($s->n * 100 / $srcMax);
                                @endphp
                                <li class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <strong>{{ $s->source_name }}</strong>
                                        <span class="text-muted">{{ $s->n }} articles · crédibilité {{ $score }}</span>
                                    </div>
                                    <div style="height:8px;border-radius:9999px;background:rgba(26,23,19,0.06);overflow:hidden;">
                                        <div style="width:{{ $w }}%;height:100%;background:{{ $barColor }};"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Newsletter signups 7-day sparkline --}}
        <div class="col-lg-7 col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Inscriptions newsletter — 7 j</h3>
                    <span class="text-muted">{{ $signupsTotal }} total</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-end gap-2" style="height:90px;">
                        @foreach($sparkline as $s)
                            @php $h = max(4, round($s['n'] * 100 / $sparkMax)); @endphp
                            <div class="flex-grow-1 text-center" style="position:relative;">
                                <div style="background:var(--gn-ink);height:{{ $h }}%;border-radius:6px 6px 0 0;min-height:4px;"></div>
                                <div class="small text-muted mt-1" style="font-size:0.7rem;">{{ \Carbon\Carbon::parse($s['date'])->locale('fr')->isoFormat('ddd')[0] }}</div>
                                @if($s['n'] > 0)
                                    <div style="position:absolute;top:-18px;left:0;right:0;font-size:0.75rem;font-weight:700;">{{ $s['n'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Draft queue + quick nav --}}
        <div class="col-lg-5 col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Brouillons récents</h3>
                    <a href="{{ route('grimba.rss-drafts.index') }}" class="small">Voir la file</a>
                </div>
                <div class="card-body">
                    @if($latestDrafts->isEmpty())
                        <p class="text-muted mb-0">Aucun brouillon en attente.</p>
                    @else
                        <ul class="list-unstyled mb-3 grimba-draft-list">
                            @foreach($latestDrafts as $draft)
                                <li>
                                    <a href="{{ route('posts.edit', $draft->id) }}">{{ \Illuminate\Support\Str::limit($draft->name, 68) }}</a>
                                    <span>{{ $draft->source_name ?: 'Source inconnue' }} · {{ optional(\Carbon\Carbon::parse($draft->updated_at))->locale('fr')->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="grimba-quick-actions">
                        <a href="{{ route('posts.create') }}" class="btn btn-primary">+ Article</a>
                        <a href="{{ route('grimba.news-sources.create') }}" class="btn btn-outline-primary">+ Source</a>
                        <a href="{{ route('grimba.story-clusters.create') }}" class="btn btn-outline-primary">+ Dossier</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .grimba-cockpit__meta {
        font-family: var(--gn-font-mono);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--gn-ink-soft);
        margin-bottom: 1.25rem;
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .grimba-cockpit__kicker {
        background: var(--gn-ink);
        color: var(--gn-paper);
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        letter-spacing: 0.08em;
    }
    .grimba-cockpit__hero {
        display: flex;
        justify-content: space-between;
        gap: 1.5rem;
        padding: 1.4rem;
        border: 1px solid var(--gn-rule);
        border-radius: 20px;
        background:
            radial-gradient(circle at 12% 10%, rgba(220, 200, 160, 0.42), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,0.86), rgba(246,241,232,0.72));
        box-shadow: 0 18px 45px rgba(26, 23, 19, 0.07);
    }
    .grimba-cockpit__hero h1 {
        margin: 0.8rem 0 0.4rem;
        font-family: var(--gn-font-display);
        font-size: clamp(1.9rem, 3vw, 3rem);
        letter-spacing: -0.04em;
    }
    .grimba-cockpit__hero p {
        max-width: 680px;
        margin: 0;
        color: var(--gn-ink-soft);
    }
    .grimba-cockpit__hero-actions,
    .grimba-quick-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .grimba-cockpit__hero-actions {
        justify-content: flex-end;
        min-width: 330px;
    }
    .grimba-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }
    .grimba-kpi {
        padding: 1rem;
        border: 1px solid var(--gn-rule);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(26, 23, 19, 0.045);
    }
    .grimba-kpi span,
    .grimba-provider-row span {
        display: block;
        font-family: var(--gn-font-mono);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--gn-ink-soft);
    }
    .grimba-kpi strong {
        display: block;
        margin-top: 0.25rem;
        font-family: var(--gn-font-display);
        font-size: 2rem;
        line-height: 1;
        color: var(--gn-ink);
    }
    .grimba-kpi small {
        color: var(--gn-ink-soft);
    }
    .grimba-kpi--warn {
        border-color: rgba(232, 76, 61, 0.26);
        background: linear-gradient(135deg, #fff, rgba(232, 76, 61, 0.05));
    }
    .grimba-ops-board {
        overflow: hidden;
    }
    .grimba-runbook-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.45rem;
    }
    .grimba-runbook-actions form {
        margin: 0;
    }
    .grimba-ops-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 0.75rem;
    }
    .grimba-ops-tile {
        display: block;
        min-height: 132px;
        padding: 0.9rem;
        border: 1px solid var(--gn-rule);
        border-radius: 16px;
        background:
            radial-gradient(circle at 18% 0%, rgba(220, 200, 160, 0.24), transparent 38%),
            rgba(255, 255, 255, 0.72);
        color: var(--gn-ink);
        text-decoration: none;
        box-shadow: 0 8px 24px rgba(26, 23, 19, 0.045);
    }
    .grimba-ops-tile:hover,
    .grimba-ops-tile:focus {
        color: var(--gn-ink);
        border-color: rgba(26, 23, 19, 0.24);
        transform: translateY(-1px);
    }
    .grimba-ops-tile.is-warn {
        border-color: rgba(232, 76, 61, 0.28);
        background:
            radial-gradient(circle at 18% 0%, rgba(232, 76, 61, 0.12), transparent 38%),
            rgba(255, 255, 255, 0.78);
    }
    .grimba-ops-tile span {
        display: block;
        font-family: var(--gn-font-mono);
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--gn-ink-soft);
    }
    .grimba-ops-tile strong {
        display: block;
        margin: 0.45rem 0;
        font-family: var(--gn-font-display);
        font-size: 2rem;
        line-height: 1;
        color: var(--gn-ink);
    }
    .grimba-ops-tile small {
        color: var(--gn-ink-soft);
        line-height: 1.35;
    }
    html[data-bs-theme="dark"] body .grimba-ops-tile,
    body[data-bs-theme="dark"] .grimba-ops-tile {
        background:
            radial-gradient(circle at 18% 0%, rgba(201, 174, 118, 0.16), transparent 38%),
            rgba(36, 32, 22, 0.86);
        border-color: rgba(246, 241, 232, 0.14);
    }
    html[data-bs-theme="dark"] body .grimba-ops-tile.is-warn,
    body[data-bs-theme="dark"] .grimba-ops-tile.is-warn {
        background:
            radial-gradient(circle at 18% 0%, rgba(232, 76, 61, 0.16), transparent 38%),
            rgba(36, 32, 22, 0.9);
        border-color: rgba(232, 76, 61, 0.3);
    }
    .grimba-status-card {
        min-height: 100%;
    }
    .grimba-status-card__count {
        min-width: 82px;
        text-align: center;
        font-family: var(--gn-font-display);
        font-size: 3rem;
        line-height: 1;
        color: var(--gn-blind);
    }
    .grimba-status-card__count::after {
        content: 'angles morts';
        display: block;
        margin-top: 0.35rem;
        font-family: var(--gn-font-mono);
        font-size: 0.62rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--gn-ink-soft);
    }
    .grimba-provider-row {
        display: grid;
        grid-template-columns: 100px 1fr;
        gap: 0.75rem;
        padding: 0.65rem 0;
        border-top: 1px solid var(--gn-rule);
    }
    .grimba-provider-row strong {
        font-size: 0.85rem;
        color: var(--gn-ink);
        word-break: break-word;
    }
    .grimba-draft-list li {
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--gn-rule);
    }
    .grimba-draft-list a {
        display: block;
        color: var(--gn-ink);
        font-family: var(--gn-font-display);
        font-weight: 650;
        text-decoration: none;
        line-height: 1.2;
    }
    .grimba-draft-list span {
        display: block;
        margin-top: 0.25rem;
        color: var(--gn-ink-soft);
        font-size: 0.78rem;
    }
    @media (max-width: 991px) {
        .grimba-cockpit__hero {
            flex-direction: column;
        }
        .grimba-cockpit__hero-actions {
            min-width: 0;
            justify-content: flex-start;
        }
        .grimba-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .grimba-ops-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 575px) {
        .grimba-kpi-grid {
            grid-template-columns: 1fr;
        }
        .grimba-ops-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
