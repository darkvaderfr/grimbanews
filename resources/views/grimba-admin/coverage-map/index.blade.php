@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $filters = [
            'gaps' => 'Tous les gaps',
            'one-sided' => 'Unilatéraux',
            'missing-left' => 'Sans gauche',
            'missing-center' => 'Sans centre',
            'missing-right' => 'Sans droite',
            'empty' => 'Vides',
            'all' => 'Tous',
        ];

        $statusMeta = [
            'balanced' => ['label' => 'Équilibrée', 'class' => 'success'],
            'partial' => ['label' => 'Partielle', 'class' => 'warning'],
            'one-sided' => ['label' => 'Unilatérale', 'class' => 'danger'],
            'empty' => ['label' => 'Vide', 'class' => 'secondary'],
        ];
        $statusColors = [
            'balanced' => '#10b981',
            'partial' => '#f59e0b',
            'one-sided' => '#ef4444',
            'empty' => '#6b7280',
        ];

        $sideMeta = [
            'left' => ['label' => 'Gauche', 'color' => 'var(--gn-left)'],
            'center' => ['label' => 'Centre', 'color' => 'var(--gn-center)'],
            'right' => ['label' => 'Droite', 'color' => 'var(--gn-right)'],
        ];
    @endphp

    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <a href="{{ route('grimba.story-clusters.index') }}">Dossiers</a>
            <span>Carte couverture</span>
        </nav>

        <section class="grimba-admin-hero">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <span class="grimba-admin-kicker">Carte couverture</span>
                    <h1 class="grimba-admin-title">Carte de couverture</h1>
                    <p class="grimba-admin-copy">
                        Repérez les dossiers qui n'ont qu'un angle politique ou qui manquent une source gauche, centre ou droite avant publication.
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    <span class="grimba-admin-status">{{ $stats['one_sided'] }} unilatéraux</span>
                    <span class="grimba-admin-status">{{ $stats['partial'] }} partiels</span>
                    <span class="grimba-admin-status">{{ $stats['completion_rate'] }}% complets</span>
                    <a href="{{ route('grimba.story-clusters.index') }}" class="btn btn-outline-primary btn-sm">Dossiers</a>
                </div>
            </div>
        </section>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="text-muted text-uppercase fw-bold small">Dossiers</div>
                    <div class="display-6 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="text-muted text-uppercase fw-bold small">Équilibrés</div>
                    <div class="display-6 fw-bold">{{ $stats['balanced'] }}</div>
                    <div class="text-muted small">{{ $stats['completion_rate'] }}% de complétion</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="text-muted text-uppercase fw-bold small">Partiels</div>
                    <div class="display-6 fw-bold">{{ $stats['partial'] }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="text-muted text-uppercase fw-bold small">Unilatéraux</div>
                    <div class="display-6 fw-bold">{{ $stats['one_sided'] }}</div>
                </div>
            </div>
        </div>

        <x-core::card>
            <x-core::card.header class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <x-core::card.title>Gaps éditoriaux</x-core::card.title>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($filters as $key => $label)
                        <a href="{{ route('grimba.coverage-map.index', ['filter' => $key]) }}"
                           class="btn btn-sm {{ $filter === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </x-core::card.header>

            <x-core::card.body>
                <div class="table-responsive grimba-admin-table-responsive">
                    <table class="table table-striped align-middle grimba-admin-table">
                        <thead>
                            <tr>
                                <th>Dossier</th>
                                <th class="text-end">Articles</th>
                                <th>Score</th>
                                <th>Balance</th>
                                <th>Manque</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                @php
                                    $total = max(1, $row->left_count + $row->center_count + $row->right_count + $row->unknown_count);
                                    $meta = $statusMeta[$row->status] ?? $statusMeta['empty'];
                                @endphp
                                <tr>
                                    <td data-label="Dossier" style="min-width: 280px;">
                                        <strong>{{ $row->topic }}</strong>
                                        @if($row->description)
                                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit($row->description, 120) }}</div>
                                        @endif
                                        @if($row->latest_article_at)
                                            <div class="text-muted small">Dernier article: {{ \Carbon\Carbon::parse($row->latest_article_at)->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td data-label="Articles" class="text-end fw-bold">{{ $row->total }}</td>
                                    <td data-label="Score" style="min-width: 130px;">
                                        <div class="grimba-coverage-score" style="--score: {{ $row->coverage_score }}%; --score-color: {{ $statusColors[$row->status] ?? '#6b7280' }};">
                                            <span></span>
                                        </div>
                                        <strong style="color: {{ $statusColors[$row->status] ?? '#6b7280' }};">{{ $row->coverage_score }}%</strong>
                                    </td>
                                    <td data-label="Balance" style="min-width: 260px;">
                                        <div class="grimba-coverage-bar" aria-label="Balance gauche centre droite">
                                            <span style="width: {{ ($row->left_count / $total) * 100 }}%; background: var(--gn-left);"></span>
                                            <span style="width: {{ ($row->center_count / $total) * 100 }}%; background: var(--gn-center);"></span>
                                            <span style="width: {{ ($row->right_count / $total) * 100 }}%; background: var(--gn-right);"></span>
                                            @if($row->unknown_count > 0)
                                                <span style="width: {{ ($row->unknown_count / $total) * 100 }}%; background: var(--gn-ink-soft);"></span>
                                            @endif
                                        </div>
                                        <div class="d-flex gap-3 flex-wrap mt-2 small">
                                            @foreach($sideMeta as $side => $sideInfo)
                                                <span style="color: {{ $sideInfo['color'] }};">● {{ $sideInfo['label'] }} {{ $row->{$side . '_count'} }}</span>
                                            @endforeach
                                            @if($row->unknown_count > 0)
                                                <span class="text-muted">● Inconnu {{ $row->unknown_count }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td data-label="Manque">
                                        @if(count($row->missing))
                                            <div class="d-flex gap-1 flex-wrap">
                                                @foreach($row->missing as $missing)
                                                    <span class="grimba-coverage-gap-chip">{{ $sideMeta[$missing]['label'] }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">Aucun</span>
                                        @endif
                                    </td>
                                    <td data-label="Statut">
                                        <span class="badge text-bg-{{ $meta['class'] }}">{{ $meta['label'] }}</span>
                                        <div class="text-muted small">priorité {{ $row->priority_score }}</div>
                                    </td>
                                    <td data-label="Actions" class="text-end grimba-admin-inline-actions">
                                        <a href="{{ route('grimba.story-clusters.edit', $row->id) }}" class="btn btn-sm btn-outline-primary">Corriger</a>
                                        @if($row->total > 0)
                                            <a href="{{ url('/comparatif/' . $row->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Voir</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="grimba-admin-empty">
                                            <div class="grimba-admin-empty__icon">MAP</div>
                                            <div class="grimba-admin-empty__title">Aucun dossier pour ce filtre</div>
                                            <p class="grimba-admin-empty__copy">
                                                Changez le filtre ou revenez aux dossiers actifs pour revoir la couverture.
                                            </p>
                                            <div class="grimba-admin-empty__actions">
                                                <a href="{{ route('grimba.coverage-map.index', ['filter' => 'all']) }}" class="btn btn-sm btn-primary">Tous les dossiers</a>
                                                <a href="{{ route('grimba.story-clusters.index') }}" class="btn btn-sm btn-outline-primary">Dossiers actifs</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>

    <style>
        .grimba-coverage-bar {
            display: flex;
            width: 100%;
            min-width: 180px;
            height: 0.62rem;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(26, 23, 19, 0.08);
        }

        .grimba-coverage-bar span {
            display: block;
            min-width: 0;
        }

        .grimba-coverage-score {
            width: 100%;
            height: 7px;
            border-radius: 999px;
            background: rgba(26, 23, 19, 0.10);
            overflow: hidden;
            margin-bottom: 0.35rem;
        }

        .grimba-coverage-score span {
            display: block;
            width: var(--score);
            height: 100%;
            border-radius: inherit;
            background: var(--score-color);
        }

        .grimba-coverage-gap-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.22rem 0.5rem;
            border-radius: 999px;
            background: rgba(26, 23, 19, 0.08);
            color: var(--gn-ink, #1a1713);
            border: 1px solid rgba(26, 23, 19, 0.12);
            font-size: 0.74rem;
            font-weight: 700;
        }

        html[data-bs-theme="dark"] .grimba-coverage-bar,
        body[data-bs-theme="dark"] .grimba-coverage-bar,
        html[data-bs-theme="dark"] .grimba-coverage-score,
        body[data-bs-theme="dark"] .grimba-coverage-score {
            background: rgba(246, 241, 232, 0.12);
        }

        html[data-bs-theme="dark"] .grimba-coverage-gap-chip,
        body[data-bs-theme="dark"] .grimba-coverage-gap-chip {
            background: rgba(246, 241, 232, 0.10);
            color: #f6f1e8;
            border-color: rgba(246, 241, 232, 0.18);
        }
    </style>
@endsection
