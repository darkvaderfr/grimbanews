@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Revenue</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Commande revenue</span>
                <h1 class="grimba-admin-title">Leads annonceurs</h1>
                <p class="grimba-admin-copy">
                    Suivez les demandes d'accès anticipé arrivées via /advertise, marquez le statut, exportez pour le CRM.
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
                    <div class="grimba-admin-metric-label">Total leads</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value" style="color:#c0392b;">{{ $newCount }}</div>
                    <div class="grimba-admin-metric-label">Nouveaux à traiter</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value" style="color:var(--gn-ink-soft);">{{ $contacted }}</div>
                    <div class="grimba-admin-metric-label">Contactés</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="grimba-admin-stat rounded-3 p-3 h-100 text-center">
                    <div class="grimba-admin-metric-value" style="color:#166534;">{{ $won }}</div>
                    <div class="grimba-admin-metric-label">Gagnés</div>
                </div>
            </div>
        </div>

        <x-core::card>
            <x-core::card.header class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <x-core::card.title>Leads annonceurs</x-core::card.title>
                <div class="grimba-admin-actions">
                    <form method="GET" action="{{ route('grimba.advertiser-leads.index') }}" class="d-flex gap-2">
                        <input name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Chercher email, société, objectifs…" style="min-width:240px;">
                        <select name="status" class="form-select form-select-sm">
                            <option value="" @selected($status === '')>Tous statuts</option>
                            <option value="new" @selected($status === 'new')>Nouveaux</option>
                            <option value="contacted" @selected($status === 'contacted')>Contactés</option>
                            <option value="won" @selected($status === 'won')>Gagnés</option>
                            <option value="closed" @selected($status === 'closed')>Fermés</option>
                            <option value="spam" @selected($status === 'spam')>Spam</option>
                        </select>
                        <button type="submit" class="btn btn-outline-primary btn-sm">Filtrer</button>
                    </form>
                    <a href="{{ route('grimba.advertiser-leads.export') }}" class="btn btn-primary btn-sm">Exporter CSV</a>
                </div>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif
            @if(session('error_msg'))
                <div class="alert alert-danger mx-3 mt-3 mb-0">{{ session('error_msg') }}</div>
            @endif

            <x-core::card.body>
                <div class="table-responsive grimba-admin-table-responsive">
                    <table class="table table-striped align-middle grimba-admin-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Société</th>
                                <th>Budget</th>
                                <th>Objectifs</th>
                                <th>Locale</th>
                                <th>Reçu le</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                                <tr>
                                    <td>
                                        <a href="mailto:{{ $lead->email }}" style="color: var(--gn-left); text-decoration: underline;">{{ $lead->email }}</a>
                                        @if($lead->source_slot)
                                            <div style="font-size: 11px; opacity: .6;">slot: {{ $lead->source_slot }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $lead->company ?? '—' }}</td>
                                    <td>
                                        @if($lead->budget_band)
                                            <span class="badge bg-light text-dark">{{ $lead->budget_band }}</span>
                                        @else
                                            <span style="opacity: .4;">—</span>
                                        @endif
                                    </td>
                                    <td style="max-width: 260px; font-size: 12.5px;">
                                        @if($lead->goals)
                                            <div style="white-space: pre-wrap; max-height: 64px; overflow: hidden;">{{ \Illuminate\Support\Str::limit($lead->goals, 160) }}</div>
                                        @else
                                            <span style="opacity: .4;">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $lead->locale ?? '—' }}</td>
                                    <td style="font-size: 12px;">{{ \Illuminate\Support\Carbon::parse($lead->created_at)->diffForHumans() }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('grimba.advertiser-leads.status', $lead->id) }}" class="d-inline-flex">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto;">
                                                <option value="new" @selected($lead->status === 'new')>Nouveau</option>
                                                <option value="contacted" @selected($lead->status === 'contacted')>Contacté</option>
                                                <option value="won" @selected($lead->status === 'won')>Gagné</option>
                                                <option value="closed" @selected($lead->status === 'closed')>Fermé</option>
                                                <option value="spam" @selected($lead->status === 'spam')>Spam</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('grimba.advertiser-leads.destroy', $lead->id) }}" class="d-inline" onsubmit="return confirm('Supprimer ce lead ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">×</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5" style="opacity: .6;">
                                        Aucun lead pour le moment. Le formulaire /advertise les fera apparaître ici.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($leads->hasPages())
                    <div class="mt-3">
                        {{ $leads->links() }}
                    </div>
                @endif
            </x-core::card.body>
        </x-core::card>
    </div>
@stop
