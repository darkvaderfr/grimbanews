@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Flux RSS</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Tour de contrôle RSS</span>
                <h1 class="grimba-admin-title">Flux RSS</h1>
                <p class="grimba-admin-copy">
                    Pilotez les sources syndiquées, repérez les flux malades, et déclenchez les polls sans quitter le cockpit éditorial.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <span class="grimba-admin-status">{{ $stats['active'] }} actifs</span>
                <span class="grimba-admin-status">{{ $stats['sick'] }} malades</span>
            </div>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <x-core::card.title>GrimbaNews — Flux RSS</x-core::card.title>

                <div class="grimba-admin-actions">
                    <form method="GET" action="{{ route('grimba.rss-feeds.index') }}" class="d-flex">
                        <input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Chercher…">
                    </form>
                    <form method="POST" action="{{ route('grimba.rss-feeds.poll-all') }}" class="d-inline"
                          onsubmit="return confirm('Lancer un poll de tous les flux actifs ?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">Poll tout</button>
                    </form>
                    <a href="{{ route('grimba.rss-feeds.create') }}" class="btn btn-primary btn-sm">+ Nouveau flux</a>
                </div>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <div class="row mb-3 g-2">
                    <div class="col">
                        <div class="grimba-admin-stat p-2 border rounded text-center">
                            <div class="text-muted small text-uppercase">Total</div>
                            <div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="grimba-admin-stat p-2 border rounded text-center">
                            <div class="text-muted small text-uppercase">Actifs</div>
                            <div class="fs-4 fw-semibold">{{ $stats['active'] }}</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="grimba-admin-stat p-2 border rounded text-center">
                            <div class="text-muted small text-uppercase">Malades (≥5 échecs)</div>
                            <div class="fs-4 fw-semibold" style="color: {{ $stats['sick'] > 0 ? '#e84c3d' : 'inherit' }}">
                                {{ $stats['sick'] }}
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="grimba-admin-stat p-2 border rounded text-center">
                            <div class="text-muted small text-uppercase">Articles ingérés</div>
                            <div class="fs-4 fw-semibold">{{ number_format($stats['ingested'], 0, ',', ' ') }}</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive grimba-admin-table-responsive">
                    <table class="table table-striped align-middle grimba-admin-table">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>URL</th>
                                <th>Format</th>
                                <th>État</th>
                                <th class="text-end">Ingestés</th>
                                <th>Dernier poll</th>
                                <th class="text-end" style="min-width: 290px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($feeds as $f)
                                @php
                                    $sick = $f->consecutive_failures >= 5;
                                    $warn = $f->consecutive_failures >= 1 && !$sick;
                                    $badgeColor = !$f->is_active ? '#9ca3af' : ($sick ? '#e84c3d' : ($warn ? '#eab308' : '#10b981'));
                                    $badgeText  = !$f->is_active ? 'Inactif' : ($sick ? 'Malade' : ($warn ? 'Instable' : 'OK'));
                                @endphp
                                <tr>
                                    <td data-label="Source">
                                        <strong>{{ $f->source_name ?? '—' }}</strong>
                                        @if($f->notes)
                                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($f->notes, 80) }}</div>
                                        @endif
                                    </td>
                                    <td data-label="URL">
                                        <a href="{{ $f->url }}" target="_blank" rel="noopener" class="small text-break" style="max-width: 280px; display:inline-block;">
                                            {{ \Illuminate\Support\Str::limit($f->url, 60) }}
                                        </a>
                                    </td>
                                    <td data-label="Format" class="text-uppercase small">{{ $f->feed_format }}</td>
                                    <td data-label="État">
                                        <span class="badge"
                                              style="background: {{ $badgeColor }}22; color: {{ $badgeColor }}; border:1px solid {{ $badgeColor }}44;">
                                            {{ $badgeText }}
                                        </span>
                                        @if($f->consecutive_failures > 0)
                                            <span class="text-muted small ms-1">×{{ $f->consecutive_failures }}</span>
                                        @endif
                                        @if($f->last_error)
                                            <div class="small text-danger" title="{{ $f->last_error }}">
                                                {{ \Illuminate\Support\Str::limit($f->last_error, 70) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td data-label="Ingestés" class="text-end">{{ $f->items_ingested }}</td>
                                    <td data-label="Dernier poll" class="small text-muted">
                                        @if($f->last_polled_at)
                                            {{ \Carbon\Carbon::parse($f->last_polled_at)->diffForHumans() }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td data-label="Actions" class="text-end grimba-admin-inline-actions">
                                        <form method="POST" action="{{ route('grimba.rss-feeds.poll-now', $f->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Poll</button>
                                        </form>
                                        <form method="POST" action="{{ route('grimba.rss-feeds.toggle', $f->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                {{ $f->is_active ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('grimba.rss-feeds.edit', $f->id) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
                                        <form method="POST" action="{{ route('grimba.rss-feeds.destroy', $f->id) }}" class="d-inline"
                                              onsubmit="return confirm('Supprimer ce flux et son historique ?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Aucun flux. <a href="{{ route('grimba.rss-feeds.create') }}">Ajoutez-en un</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-core::card.body>

            <x-core::card.footer>
                {!! $feeds->links() !!}
            </x-core::card.footer>
        </x-core::card>
    </div>
@endsection
