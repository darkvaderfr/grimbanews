@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    @php
        $actionLabels = [
            'merge' => 'Fusion demandée',
            'split' => 'Scission demandée',
            'approve' => 'Approuvé',
        ];
    @endphp

    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <a href="{{ route('grimba.story-clusters.index') }}">Dossiers</a>
            <span>Revue dossiers</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">File de revue</span>
                <h1 class="grimba-admin-title">Revue dossiers</h1>
                <p class="grimba-admin-copy">
                    Traitez les dossiers unilatéraux trop denses et les dossiers tripartites trop minces avant publication éditoriale.
                </p>
            </div>
            <div class="grimba-admin-actions justify-content-end">
                <span class="grimba-admin-status">{{ $stats['one_sided'] }} unilatéraux</span>
                <span class="grimba-admin-status">{{ $stats['thin_split'] }} à scinder</span>
                <span class="grimba-admin-status">{{ $stats['acted'] }} décidés</span>
                <a href="{{ route('grimba.coverage-map.index') }}" class="btn btn-sm btn-outline-primary">Carte couverture</a>
            </div>
        </section>

        @if(! $hasReviewFields)
            <div class="alert alert-warning">
                Les champs de revue ne sont pas encore migrés. Lancez les migrations avant d'enregistrer des décisions.
            </div>
        @endif

        @if(session('success_msg'))
            <div class="alert alert-success">{{ session('success_msg') }}</div>
        @endif

        <x-core::card>
            <x-core::card.header class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <x-core::card.title>Dossiers à arbitrer</x-core::card.title>
                <span class="text-muted small">{{ $stats['total'] }} signal{{ $stats['total'] === 1 ? '' : 's' }}</span>
            </x-core::card.header>

            <x-core::card.body>
                <div class="table-responsive grimba-admin-table-responsive">
                    <table class="table table-striped align-middle grimba-admin-table">
                        <thead>
                            <tr>
                                <th>Dossier</th>
                                <th>Signal</th>
                                <th class="text-end">Articles</th>
                                <th>Spread L / C / D</th>
                                <th>Décision</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td data-label="Dossier" style="min-width: 260px;">
                                        <strong>{{ $row->topic }}</strong>
                                        @if($row->description)
                                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit($row->description, 110) }}</div>
                                        @endif
                                        @if($row->latest_article_at)
                                            <div class="text-muted small">Dernier article: {{ \Carbon\Carbon::parse($row->latest_article_at)->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td data-label="Signal">
                                        <span class="badge text-bg-{{ $row->signal_kind === 'merge' ? 'danger' : 'warning' }}">{{ $row->signal }}</span>
                                        <div class="text-muted small">priorité {{ $row->priority_score }}</div>
                                    </td>
                                    <td data-label="Articles" class="text-end fw-bold">{{ $row->total }}</td>
                                    <td data-label="Spread">
                                        <span style="color: var(--gn-left);">● {{ $row->left_count }}</span>
                                        <span style="color: var(--gn-center);" class="mx-2">● {{ $row->center_count }}</span>
                                        <span style="color: var(--gn-right);">● {{ $row->right_count }}</span>
                                        @if($row->unknown_count > 0)
                                            <span class="text-muted ms-2">+{{ $row->unknown_count }} inconnu</span>
                                        @endif
                                    </td>
                                    <td data-label="Décision">
                                        @if($row->review_action)
                                            <strong>{{ $actionLabels[$row->review_action] ?? $row->review_action }}</strong>
                                            @if($row->reviewed_at)
                                                <div class="text-muted small">{{ \Carbon\Carbon::parse($row->reviewed_at)->diffForHumans() }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">Non décidé</span>
                                        @endif
                                    </td>
                                    <td data-label="Actions" class="text-end grimba-admin-inline-actions">
                                        <form method="POST" action="{{ route('grimba.cluster-review.action', $row->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="merge">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Fusionner</button>
                                        </form>
                                        <form method="POST" action="{{ route('grimba.cluster-review.action', $row->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="split">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Scinder</button>
                                        </form>
                                        <form method="POST" action="{{ route('grimba.cluster-review.action', $row->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-primary">Approuver</button>
                                        </form>
                                        <a href="{{ route('grimba.story-clusters.edit', $row->id) }}" class="btn btn-sm btn-outline-secondary">Ouvrir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="grimba-admin-empty">
                                            <div class="grimba-admin-empty__icon">REV</div>
                                            <div class="grimba-admin-empty__title">Aucun dossier à arbitrer</div>
                                            <p class="grimba-admin-empty__copy">
                                                Les dossiers problématiques apparaîtront ici quand un signal de couverture devient ambigu.
                                            </p>
                                            <div class="grimba-admin-empty__actions">
                                                <a href="{{ route('grimba.story-clusters.index') }}" class="btn btn-sm btn-primary">Dossiers actifs</a>
                                                <a href="{{ route('grimba.coverage-map.index') }}" class="btn btn-sm btn-outline-primary">Carte couverture</a>
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
@endsection
