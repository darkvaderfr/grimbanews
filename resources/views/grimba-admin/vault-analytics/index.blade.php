@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Analytics coffre</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Coffre lecteurs</span>
                <h1 class="grimba-admin-title">Analytics coffre</h1>
                <p class="grimba-admin-copy">
                    Suivez les articles sauvegardés, les retours vers le coffre, et la conversion hebdomadaire sans données personnelles.
                </p>
            </div>
            <div class="grimba-admin-actions justify-content-end">
                <a href="{{ route('grimba.vault-analytics.index', ['week' => $previousWeek]) }}" class="btn btn-sm btn-outline-primary">Semaine précédente</a>
                <a href="{{ route('grimba.vault-analytics.index', ['week' => now()->startOfWeek()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary">Semaine actuelle</a>
                <a href="{{ route('grimba.vault-analytics.index', ['week' => $nextWeek]) }}" class="btn btn-sm btn-outline-primary">Semaine suivante</a>
            </div>
        </section>

        <form method="GET" action="{{ route('grimba.vault-analytics.index') }}" class="grimba-admin-actions mb-3">
            <input name="week" type="date" class="form-control form-control-sm" value="{{ $weekStart->toDateString() }}">
            <button type="submit" class="btn btn-sm btn-primary">Appliquer</button>
            <span class="grimba-admin-status">{{ $weekStart->isoFormat('D MMM') }} - {{ $weekEnd->isoFormat('D MMM YYYY') }}</span>
        </form>

        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-xl-2">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="grimba-admin-metric-label">Sauvegardes</div>
                    <div class="grimba-admin-metric-value">{{ number_format($stats['saves']) }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="grimba-admin-metric-label">Retraits</div>
                    <div class="grimba-admin-metric-value">{{ number_format($stats['unsaves']) }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="grimba-admin-metric-label">Retours coffre</div>
                    <div class="grimba-admin-metric-value">{{ number_format($stats['return_visits']) }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="grimba-admin-metric-label">Sauveurs uniques</div>
                    <div class="grimba-admin-metric-value">{{ number_format($stats['unique_savers']) }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="grimba-admin-metric-label">Retours convertis</div>
                    <div class="grimba-admin-metric-value">{{ number_format($stats['converted_returners']) }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="grimba-admin-stat rounded-3 p-3 h-100">
                    <div class="grimba-admin-metric-label">Conversion</div>
                    <div class="grimba-admin-metric-value">{{ $stats['conversion_rate'] }}%</div>
                </div>
            </div>
        </div>

        <x-core::card class="mb-3">
            <x-core::card.header>
                <x-core::card.title>Rythme hebdomadaire</x-core::card.title>
            </x-core::card.header>
            <x-core::card.body>
                <div class="grimba-vault-week-bars" aria-label="Sauvegardes et retours coffre par jour">
                    @foreach($dailyRows as $day)
                        <div class="grimba-vault-week-bars__day">
                            <div class="grimba-vault-week-bars__tracks">
                                <span style="height: {{ max(6, round($day->saves * 100 / $maxDaily)) }}%;" title="{{ $day->saves }} sauvegardes"></span>
                                <span style="height: {{ max(6, round($day->return_visits * 100 / $maxDaily)) }}%;" title="{{ $day->return_visits }} retours"></span>
                            </div>
                            <strong>{{ $day->label }}</strong>
                            <small>{{ $day->saves }} sauv. · {{ $day->return_visits }} retour</small>
                        </div>
                    @endforeach
                </div>
            </x-core::card.body>
        </x-core::card>

        <x-core::card>
            <x-core::card.header class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <x-core::card.title>Articles les plus sauvegardés</x-core::card.title>
                <span class="text-muted small">Comptage hebdomadaire par événement sauvegarde</span>
            </x-core::card.header>
            <x-core::card.body>
                <div class="table-responsive grimba-admin-table-responsive">
                    <table class="table table-striped align-middle grimba-admin-table">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th>Source</th>
                                <th class="text-end">Sauvegardes</th>
                                <th class="text-end">Lecteurs uniques</th>
                                <th>Dernière sauvegarde</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPosts as $row)
                                <tr>
                                    <td data-label="Article">
                                        <strong>{{ $row->name ?: 'Article #' . $row->post_id }}</strong>
                                        <div class="text-muted small">post #{{ $row->post_id }}</div>
                                    </td>
                                    <td data-label="Source">{{ $row->source_name ?: '—' }}</td>
                                    <td data-label="Sauvegardes" class="text-end fw-bold">{{ $row->saves }}</td>
                                    <td data-label="Lecteurs uniques" class="text-end">{{ $row->unique_savers }}</td>
                                    <td data-label="Dernière sauvegarde" class="text-muted small">
                                        {{ $row->latest_save_at ? \Carbon\Carbon::parse($row->latest_save_at)->diffForHumans() : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="grimba-admin-empty">
                                            <div class="grimba-admin-empty__icon">VAU</div>
                                            <div class="grimba-admin-empty__title">Aucune sauvegarde cette semaine</div>
                                            <p class="grimba-admin-empty__copy">
                                                Les événements apparaîtront ici après les premières sauvegardes du coffre.
                                            </p>
                                            <div class="grimba-admin-empty__actions">
                                                <a href="{{ route('grimba.vault-analytics.index', ['week' => now()->startOfWeek()->toDateString()]) }}" class="btn btn-sm btn-primary">Semaine actuelle</a>
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
        .grimba-vault-week-bars {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 0.75rem;
            align-items: end;
            min-height: 190px;
        }

        .grimba-vault-week-bars__day {
            display: grid;
            gap: 0.45rem;
            min-width: 0;
        }

        .grimba-vault-week-bars__tracks {
            display: flex;
            align-items: end;
            justify-content: center;
            gap: 0.28rem;
            height: 128px;
            padding: 0.5rem;
            border-radius: 12px;
            background: rgba(26, 23, 19, 0.05);
            border: 1px solid rgba(26, 23, 19, 0.08);
        }

        .grimba-vault-week-bars__tracks span {
            display: block;
            width: 30%;
            min-height: 6px;
            border-radius: 999px 999px 4px 4px;
        }

        .grimba-vault-week-bars__tracks span:first-child {
            background: var(--gn-left);
        }

        .grimba-vault-week-bars__tracks span:last-child {
            background: var(--gn-right);
        }

        .grimba-vault-week-bars__day strong,
        .grimba-vault-week-bars__day small {
            overflow-wrap: anywhere;
        }

        @media (max-width: 767.98px) {
            .grimba-vault-week-bars {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                min-height: 0;
            }
        }

        html[data-bs-theme="dark"] .grimba-vault-week-bars__tracks,
        body[data-bs-theme="dark"] .grimba-vault-week-bars__tracks {
            background: rgba(246, 241, 232, 0.08);
            border-color: rgba(246, 241, 232, 0.12);
        }
    </style>
@endsection
