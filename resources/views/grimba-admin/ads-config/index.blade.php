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
                <h1 class="grimba-admin-title">Config publicités</h1>
                <p class="grimba-admin-copy">
                    Adresse mailbox des leads, identifiants AdSense, et IDs par emplacement.
                    Les changements prennent effet immédiatement — pas de redéploiement requis.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a href="{{ url('/advertise') }}" target="_blank" class="btn btn-outline-secondary btn-sm">Voir /advertise</a>
                <a href="{{ route('grimba.advertiser-leads.index') }}" class="btn btn-outline-primary btn-sm">Leads annonceurs →</a>
            </div>
        </section>

        @if(session('success_msg'))
            <div class="alert alert-success">{{ session('success_msg') }}</div>
        @endif
        @if(session('error_msg'))
            <div class="alert alert-danger">{{ session('error_msg') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('grimba.ads-config.save') }}">
            @csrf

            <x-core::card class="mb-3">
                <x-core::card.header>
                    <x-core::card.title>Pipeline commercial</x-core::card.title>
                    <small class="text-muted">
                        L'adresse qui reçoit les notifications de nouveaux leads /advertise.
                        Vide = aucun email envoyé (le lead reste capturé dans la base + visible dans l'index).
                    </small>
                </x-core::card.header>
                <x-core::card.body>
                    <label class="form-label">{{ __('Mailbox équipe ventes') }}</label>
                    <input type="email" name="grimba_advertiser_leads_sales_mailbox"
                           class="form-control" maxlength="191"
                           value="{{ old('grimba_advertiser_leads_sales_mailbox', $salesMailbox) }}"
                           placeholder="sales@grimbanews.com">
                    <small class="form-text text-muted">
                        Reçoit le détail complet (id, email, société, budget, objectifs, slot d'origine, locale) + un bouton vers la fiche admin.
                    </small>
                </x-core::card.body>
            </x-core::card>

            <x-core::card class="mb-3">
                <x-core::card.header>
                    <x-core::card.title>AdSense global</x-core::card.title>
                    <small class="text-muted">
                        Client ID racine pour tous les emplacements. Format attendu :
                        <code>ca-pub-1234567890123456</code>.
                    </small>
                </x-core::card.header>
                <x-core::card.body>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Client ID AdSense') }}</label>
                            <input type="text" name="ads_google_adsense_unit_client_id"
                                   class="form-control" maxlength="191"
                                   value="{{ old('ads_google_adsense_unit_client_id', $clientId) }}"
                                   placeholder="ca-pub-XXXXXXXXXXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __("URL directe de repli") }}</label>
                            <input type="url" name="grimba_ads_direct_url"
                                   class="form-control" maxlength="255"
                                   value="{{ old('grimba_ads_direct_url', $directUrl) }}"
                                   placeholder="https://campaign.grimbanews.com/?placement={placement}">
                            <small class="form-text text-muted">
                                Substitution disponible : <code>{placement}</code> est remplacé par le nom de placement (home-top, article-mid, etc.).
                                Vide = lien vers /advertise.
                            </small>
                        </div>
                    </div>
                </x-core::card.body>
            </x-core::card>

            <x-core::card class="mb-3">
                <x-core::card.header>
                    <x-core::card.title>Slots AdSense par emplacement</x-core::card.title>
                    <small class="text-muted">
                        Slot IDs numériques fournis par Google AdSense. Format : 4+ chiffres.
                        Vide = repli direct (URL ci-dessus).
                    </small>
                </x-core::card.header>
                <x-core::card.body>
                    <div class="row g-3">
                        @foreach($slotKeys as $key => $label)
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">{{ $label }}</label>
                                <input type="text" name="slots[{{ $key }}]"
                                       class="form-control form-control-sm"
                                       pattern="\d{4,}"
                                       maxlength="24"
                                       value="{{ old('slots.' . $key, (string) setting('grimba_ads_slot_' . $key, '')) }}"
                                       placeholder="0000000000">
                                <small class="form-text text-muted" style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px;">{{ $key }}</small>
                            </div>
                        @endforeach
                    </div>
                </x-core::card.body>
            </x-core::card>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('Enregistrer') }}</button>
                <a href="{{ route('grimba.ads-config.index') }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
            </div>
        </form>
    </div>
@stop
