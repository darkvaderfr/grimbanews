@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1200">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Revenue</span>
            <a href="{{ route('grimba.advertiser-leads.index') }}">Leads annonceurs</a>
            <span>#{{ $lead->id }}</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Lead annonceur #{{ $lead->id }}</span>
                <h1 class="grimba-admin-title">{{ $lead->company ?? __('Société non renseignée') }}</h1>
                <p class="grimba-admin-copy">
                    <a href="mailto:{{ $lead->email }}" style="color: var(--gn-left); text-decoration: underline;">{{ $lead->email }}</a>
                    @if($lead->budget_band)
                        · <span class="badge bg-light text-dark">{{ $lead->budget_band }}</span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                @if($prevId)
                    <a href="{{ route('grimba.advertiser-leads.show', $prevId) }}" class="btn btn-outline-secondary btn-sm">← #{{ $prevId }}</a>
                @endif
                <a href="{{ route('grimba.advertiser-leads.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Retour à l\'index') }}</a>
                @if($nextId)
                    <a href="{{ route('grimba.advertiser-leads.show', $nextId) }}" class="btn btn-outline-secondary btn-sm">#{{ $nextId }} →</a>
                @endif
            </div>
        </section>

        @if(session('success_msg'))
            <div class="alert alert-success">{{ session('success_msg') }}</div>
        @endif
        @if(session('error_msg'))
            <div class="alert alert-danger">{{ session('error_msg') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-lg-7">
                <x-core::card class="mb-3">
                    <x-core::card.header>
                        <x-core::card.title>Notes opérateur</x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        <form method="POST" action="{{ route('grimba.advertiser-leads.notes', $lead->id) }}">
                            @csrf
                            <textarea name="admin_notes" class="form-control" rows="10" maxlength="8000" placeholder="{{ __('Notes internes : appels, objections, état des négociations, créatifs reçus, etc. Visible uniquement par l\'équipe.') }}">{{ old('admin_notes', $lead->admin_notes ?? '') }}</textarea>
                            <div class="d-flex justify-content-end mt-2">
                                <button type="submit" class="btn btn-primary">{{ __('Enregistrer les notes') }}</button>
                            </div>
                        </form>
                    </x-core::card.body>
                </x-core::card>

                <x-core::card class="mb-3">
                    <x-core::card.header>
                        <x-core::card.title>Objectifs (saisis par l'annonceur)</x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        @if(! empty($lead->goals))
                            <div style="white-space: pre-wrap; line-height: 1.55;">{{ $lead->goals }}</div>
                        @else
                            <em style="opacity: .6;">{{ __('Aucun objectif saisi.') }}</em>
                        @endif
                    </x-core::card.body>
                </x-core::card>
            </div>

            <div class="col-lg-5">
                <x-core::card class="mb-3">
                    <x-core::card.header>
                        <x-core::card.title>Statut</x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        <form method="POST" action="{{ route('grimba.advertiser-leads.status', $lead->id) }}" class="d-flex gap-2 align-items-center">
                            @csrf
                            <select name="status" class="form-select form-select-sm" style="flex: 1;">
                                <option value="new" @selected($lead->status === 'new')>{{ __('Nouveau') }}</option>
                                <option value="contacted" @selected($lead->status === 'contacted')>{{ __('Contacté') }}</option>
                                <option value="won" @selected($lead->status === 'won')>{{ __('Gagné') }}</option>
                                <option value="closed" @selected($lead->status === 'closed')>{{ __('Fermé') }}</option>
                                <option value="spam" @selected($lead->status === 'spam')>{{ __('Spam') }}</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Mettre à jour') }}</button>
                        </form>
                    </x-core::card.body>
                </x-core::card>

                <x-core::card class="mb-3">
                    <x-core::card.header>
                        <x-core::card.title>Métadonnées</x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        <dl class="m-0" style="display:grid; grid-template-columns:auto 1fr; gap: 8px 14px; font-size:13.5px;">
                            <dt style="opacity:.62;">{{ __('Reçu') }}</dt>
                            <dd class="m-0">{{ \Illuminate\Support\Carbon::parse($lead->created_at)->format('d/m/Y H:i') }} ({{ \Illuminate\Support\Carbon::parse($lead->created_at)->diffForHumans() }})</dd>

                            @if($lead->last_admin_action_at ?? null)
                                <dt style="opacity:.62;">{{ __('Dernière action ops') }}</dt>
                                <dd class="m-0">{{ \Illuminate\Support\Carbon::parse($lead->last_admin_action_at)->diffForHumans() }}</dd>
                            @endif

                            <dt style="opacity:.62;">{{ __('Locale') }}</dt>
                            <dd class="m-0">{{ $lead->locale ?? '—' }}</dd>

                            <dt style="opacity:.62;">{{ __('Slot d\'origine') }}</dt>
                            <dd class="m-0">{{ $lead->source_slot ?? '—' }}</dd>

                            <dt style="opacity:.62;">{{ __('Référent') }}</dt>
                            <dd class="m-0" style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size:11.5px; word-break:break-all;">
                                {{ $lead->source_referrer ?: '—' }}
                            </dd>

                            <dt style="opacity:.62;">{{ __('IP') }}</dt>
                            <dd class="m-0" style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size:11.5px;">{{ $lead->ip ?? '—' }}</dd>
                        </dl>
                    </x-core::card.body>
                </x-core::card>

                <x-core::card>
                    <x-core::card.header>
                        <x-core::card.title>Actions</x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body class="d-flex gap-2 flex-wrap">
                        <a href="mailto:{{ $lead->email }}" class="btn btn-outline-primary btn-sm">{{ __('Envoyer un email') }}</a>
                        <form method="POST" action="{{ route('grimba.advertiser-leads.destroy', $lead->id) }}" onsubmit="return confirm('Supprimer ce lead définitivement ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('Supprimer le lead') }}</button>
                        </form>
                    </x-core::card.body>
                </x-core::card>
            </div>
        </div>
    </div>
@stop
