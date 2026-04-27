@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Audience command</span>
                <h1 class="grimba-admin-title">Abonnés infolettre</h1>
                <p class="grimba-admin-copy">
                    Suivez les inscriptions, exportez les segments, et gardez le signal biais du lectorat visible pour l'équipe éditoriale.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <span class="grimba-admin-status">{{ $total }} total</span>
                <span class="grimba-admin-status">+{{ $last7d }} sur 7 j</span>
            </div>
        </section>

        <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value">{{ $total }}</div>
                    <div class="grimba-admin-metric-label">Total abonnés</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value" style="color:#166534;">{{ $activeCount }}</div>
                    <div class="grimba-admin-metric-label">Actifs</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value" style="color:var(--gn-ink-soft);">{{ $unsubCount }}</div>
                    <div class="grimba-admin-metric-label">Désabonnés</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value" style="color:var(--gn-left);">+{{ $last7d }}</div>
                    <div class="grimba-admin-metric-label">7 derniers jours</div>
                </div>
            </div>
        </div>
        <x-core::card>
            <x-core::card.header class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <x-core::card.title>Abonnés à l'infolettre</x-core::card.title>
                <div class="grimba-admin-actions">
                    <form method="GET" action="{{ route('grimba.subscribers.index') }}" class="d-flex gap-2">
                        <input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Chercher email, locale…" style="min-width:200px;">
                        <select name="active" class="form-select form-select-sm">
                            <option value="" @selected($active === null)>Tous</option>
                            <option value="1" @selected($active === '1')>Actifs</option>
                            <option value="0" @selected($active === '0')>Désabonnés</option>
                        </select>
                        <button type="submit" class="btn btn-outline-primary btn-sm">Filtrer</button>
                    </form>
                    <a href="{{ route('grimba.subscribers.export') }}" class="btn btn-primary btn-sm">Exporter CSV</a>
                </div>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Locale</th>
                                <th>Source</th>
                                <th>Signal biais</th>
                                <th>Inscrit le</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subs as $s)
                                <tr>
                                    <td><strong>{{ $s->email }}</strong></td>
                                    <td class="text-uppercase">{{ $s->locale }}</td>
                                    <td><span class="text-muted small font-monospace">{{ $s->source_key ?? '—' }}</span></td>
                                    <td>
                                        <span class="badge text-bg-light border">{{ $s->digest_variant ?? '—' }}</span>
                                        <div class="small text-muted">
                                            L {{ $s->reader_bias_left ?? 0 }} · C {{ $s->reader_bias_center ?? 0 }} · R {{ $s->reader_bias_right ?? 0 }}
                                        </div>
                                    </td>
                                    <td><span class="text-muted">{{ \Carbon\Carbon::parse($s->created_at)->locale('fr')->isoFormat('D MMM YYYY') }}</span></td>
                                    <td>
                                        @if($s->unsubscribed_at)
                                            <span class="badge text-bg-secondary">Désabonné</span>
                                        @else
                                            <span class="badge text-bg-success">Actif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('grimba.subscribers.toggle', $s->id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary">
                                                {{ $s->unsubscribed_at ? 'Réactiver' : 'Désabonner' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('grimba.subscribers.destroy', $s->id) }}" class="d-inline"
                                              onsubmit="return confirm('Supprimer définitivement {{ addslashes($s->email) }} ?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Aucun abonné pour ces filtres.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-core::card.body>

            <x-core::card.footer>
                {!! $subs->links() !!}
            </x-core::card.footer>
        </x-core::card>
    </div>
@endsection
