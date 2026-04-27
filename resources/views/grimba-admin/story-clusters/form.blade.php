@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    use Illuminate\Support\Str;

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
    $sources = $attachedSourceMeta ?? collect();
@endphp

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <a href="{{ route('grimba.story-clusters.index') }}">Dossiers</a>
            <span>{{ $isEdit ? 'Modifier' : 'Créer' }}</span>
        </nav>

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

        <form method="POST" action="{{ $action }}" class="grimba-admin-form mb-4">
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
                    <section class="grimba-admin-form-section">
                        <h2 class="grimba-admin-form-section__title">Signal éditorial du dossier</h2>
                        <p class="grimba-admin-form-section__hint mb-3">
                            Le titre public doit rester court et lisible; la description sert aux notes internes de regroupement.
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Titre du dossier<span class="text-danger">*</span></label>
                            <input name="topic" class="form-control"
                                   value="{{ old('topic', $cluster->topic ?? '') }}" required maxlength="200">
                        </div>

                        <div>
                            <label class="form-label">Description (interne)</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $cluster->description ?? '') }}</textarea>
                        </div>
                    </section>
                </x-core::card.body>

                <x-core::card.footer class="grimba-admin-form-actions">
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
                    <form method="POST" action="{{ route('grimba.story-clusters.nobuai-summary', $cluster->id) }}" class="grimba-admin-form-actions">
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
                            @if($summaryIsStale ?? false)
                                <div class="alert alert-warning py-2 mb-2">
                                    Insight stale : une couverture plus récente est arrivée. Régénérez NobuAI avant publication.
                                </div>
                            @endif
                            <pre class="bg-light p-3 rounded mb-0" style="white-space:pre-wrap;">{{ $summaryInfo->summary_nobuai }}</pre>
                        </div>
                    @else
                        <p class="text-muted mb-0">Aucun insight généré pour ce dossier.</p>
                    @endif
                </x-core::card.body>
            </x-core::card>

            <x-core::card class="mb-4">
                <x-core::card.header>
                    <x-core::card.title>Diagnostic sources</x-core::card.title>
                    <p class="text-muted small mb-0">
                        Vérifiez les angles, les métadonnées source et la crédibilité avant publication.
                    </p>
                </x-core::card.header>
                <x-core::card.body>
                    @if($attached->isEmpty())
                        <p class="text-muted mb-0">Aucun article attaché à diagnostiquer.</p>
                    @else
                        <div class="grimba-cluster-drilldown">
                            @foreach($attached as $p)
                                @php
                                    $bias = isset($biasLabel[$p->bias_rating ?? '']) ? $p->bias_rating : 'unknown';
                                    $bc = $biasColor[$bias] ?? $biasColor['unknown'];
                                    $source = $p->source_id && isset($sources[$p->source_id]) ? $sources[$p->source_id] : null;
                                    $sourceName = $p->source_name ?: ($source->name ?? 'Source inconnue');
                                    $excerpt = trim(strip_tags((string) ($p->description ?: $p->name)));
                                    $flags = collect();

                                    if (! $source) {
                                        $flags->push(['label' => 'Métadonnées source manquantes', 'tone' => 'danger']);
                                    }

                                    if ($bias === 'unknown') {
                                        $flags->push(['label' => 'Biais inconnu', 'tone' => 'warning']);
                                    }

                                    if ($source && $source->credibility_score !== null && (int) $source->credibility_score < 60) {
                                        $flags->push(['label' => 'Crédibilité basse', 'tone' => 'danger']);
                                    }
                                @endphp
                                <article class="grimba-cluster-drilldown__row" style="--cluster-bias-color: {{ $bc }};">
                                    <div class="grimba-cluster-drilldown__bias">
                                        <span></span>
                                        {{ $biasLabel[$bias] ?? '—' }}
                                    </div>
                                    <div class="grimba-cluster-drilldown__body">
                                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                            <div>
                                                <strong>{{ $sourceName }}</strong>
                                                <p class="mb-1">{{ Str::limit($excerpt, 150) }}</p>
                                            </div>
                                            <a href="{{ route('posts.edit', $p->id) }}" class="btn btn-sm btn-outline-secondary">
                                                Modifier l'article
                                            </a>
                                        </div>
                                        <div class="grimba-cluster-drilldown__meta">
                                            @if($source && $source->credibility_score !== null)
                                                <span>Crédibilité {{ (int) $source->credibility_score }}/100</span>
                                            @endif
                                            @if($source && ! empty($source->ownership_type))
                                                <span>Propriété {{ $source->ownership_type }}</span>
                                            @endif
                                            @if($source && ! empty($source->owner_name))
                                                <span>Owner {{ $source->owner_name }}</span>
                                            @endif
                                            @if($source && ! empty($source->website))
                                                <span>{{ $source->website }}</span>
                                            @endif
                                        </div>
                                        @if($flags->isNotEmpty())
                                            <div class="grimba-cluster-drilldown__flags">
                                                @foreach($flags as $flag)
                                                    <span class="grimba-cluster-drilldown__flag grimba-cluster-drilldown__flag--{{ $flag['tone'] }}">
                                                        {{ $flag['label'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
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
                                        <td class="text-end grimba-admin-inline-actions">
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
                    <form method="POST" action="{{ route('grimba.story-clusters.attach', $cluster->id) }}" class="grimba-admin-form-actions">
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

    <style>
        .grimba-cluster-drilldown {
            display: grid;
            gap: 12px;
        }
        .grimba-cluster-drilldown__row {
            display: grid;
            grid-template-columns: minmax(92px, 140px) 1fr;
            gap: 14px;
            padding: 14px;
            border: 1px solid rgba(26, 23, 19, 0.12);
            border-left: 4px solid var(--cluster-bias-color);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.88);
        }
        .grimba-cluster-drilldown__bias {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--cluster-bias-color);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .grimba-cluster-drilldown__bias span {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--cluster-bias-color);
        }
        .grimba-cluster-drilldown__body strong {
            color: #17130f;
            font-size: 16px;
        }
        .grimba-cluster-drilldown__body p {
            color: #5f584f;
            line-height: 1.5;
        }
        .grimba-cluster-drilldown__meta,
        .grimba-cluster-drilldown__flags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .grimba-cluster-drilldown__meta span {
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(26, 23, 19, 0.06);
            color: #5f584f;
            font-size: 12px;
            font-weight: 700;
        }
        .grimba-cluster-drilldown__flag {
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }
        .grimba-cluster-drilldown__flag--danger {
            background: rgba(220, 38, 38, 0.1);
            color: #b91c1c;
        }
        .grimba-cluster-drilldown__flag--warning {
            background: rgba(217, 119, 6, 0.12);
            color: #92400e;
        }
        [data-bs-theme="dark"] .grimba-cluster-drilldown__row,
        .theme-dark .grimba-cluster-drilldown__row,
        body.dark .grimba-cluster-drilldown__row {
            border-color: rgba(255, 255, 255, 0.16);
            background: rgba(25, 25, 23, 0.88);
        }
        [data-bs-theme="dark"] .grimba-cluster-drilldown__body strong,
        .theme-dark .grimba-cluster-drilldown__body strong,
        body.dark .grimba-cluster-drilldown__body strong {
            color: #f7f2e8;
        }
        [data-bs-theme="dark"] .grimba-cluster-drilldown__body p,
        [data-bs-theme="dark"] .grimba-cluster-drilldown__meta span,
        .theme-dark .grimba-cluster-drilldown__body p,
        .theme-dark .grimba-cluster-drilldown__meta span,
        body.dark .grimba-cluster-drilldown__body p,
        body.dark .grimba-cluster-drilldown__meta span {
            color: #d6cfc3;
        }
        [data-bs-theme="dark"] .grimba-cluster-drilldown__meta span,
        .theme-dark .grimba-cluster-drilldown__meta span,
        body.dark .grimba-cluster-drilldown__meta span {
            background: rgba(255, 255, 255, 0.08);
        }
        @media (max-width: 720px) {
            .grimba-cluster-drilldown__row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
