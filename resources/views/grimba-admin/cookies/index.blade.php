@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="grimba-admin-screen max-width-1000">
        <nav class="grimba-admin-wayfinder" aria-label="GrimbaNews admin navigation">
            <a href="{{ route('grimba.cockpit') }}">GrimbaNews</a>
            <span>Cookies</span>
        </nav>

        <section class="grimba-admin-hero d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <span class="grimba-admin-kicker">Consent layer</span>
                <h1 class="grimba-admin-title">Bandeau cookies</h1>
                <p class="grimba-admin-copy">
                    Edit the public consent copy and keep the privacy banner readable across the GrimbaNews front-end.
                </p>
            </div>
            <span class="grimba-admin-status">{{ $active ? 'Active' : 'Disabled' }}</span>
        </section>

        <x-core::card>
            <x-core::card.header class="d-flex align-items-center justify-content-between">
                <x-core::card.title>GrimbaNews — Bandeau cookies</x-core::card.title>
                <div class="small text-muted">
                    {{ $active ? 'Actif' : 'Désactivé' }}
                </div>
            </x-core::card.header>

            @if(session('success_msg'))
                <div class="alert alert-success mx-3 mt-3 mb-0">{{ session('success_msg') }}</div>
            @endif

            <x-core::card.body>
                <div class="grimba-admin-section mb-4">
                    <p class="text-muted small mb-0">
                        Bandeau de consentement cookies — apparaît en bas à droite de chaque page jusqu'à
                        ce que le visiteur clique sur Accepter ou Refuser. Le choix est stocké dans le cookie
                        <code>grimba_cookie_consent</code> (1 an, non chiffré pour permettre la lecture côté client).
                        Endpoint : <code>POST /cookie-consent/{accept|reject}</code>.
                    </p>
                </div>

                <form method="POST" action="{{ route('grimba.cookies.save') }}" class="grimba-admin-form">
                    @csrf

                    <section class="grimba-admin-form-section">
                        <h2 class="grimba-admin-form-section__title">Activation</h2>
                        <div class="form-check form-switch">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" class="form-check-input" name="active" id="cookie-active" value="1" {{ $active ? 'checked' : '' }}>
                            <label class="form-check-label" for="cookie-active">
                                <strong>Bandeau actif</strong>
                                <span class="text-muted small d-block">
                                    Désactiver pour cacher le bandeau sur tout le site (pour les régions sans obligation, par exemple).
                                </span>
                            </label>
                        </div>
                    </section>

                    <section class="grimba-admin-form-section">
                        <h2 class="grimba-admin-form-section__title">Copie publique</h2>
                        <p class="grimba-admin-form-section__hint mb-3">
                            Gardez le texte court: ce bloc apparaît sur chaque page publique tant que le choix n'est pas enregistré.
                        </p>

                        <div class="mb-3">
                            <label class="form-label"><strong>Titre</strong></label>
                            <input type="text" name="title" class="form-control" value="{{ $title }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Corps du message</strong></label>
                            <textarea name="body" class="form-control" rows="4">{{ $body }}</textarea>
                            <div class="form-text">Sauts de ligne respectés.</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label"><strong>Bouton Accepter</strong></label>
                                <input type="text" name="accept_label" class="form-control" value="{{ $accept_label }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Bouton Refuser</strong></label>
                                <input type="text" name="reject_label" class="form-control" value="{{ $reject_label }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Lien "En savoir plus"</strong></label>
                                <input type="text" name="more_label" class="form-control" value="{{ $more_label }}">
                            </div>
                        </div>

                        <div>
                            <label class="form-label"><strong>URL "En savoir plus"</strong></label>
                            <input type="text" name="more_url" class="form-control" value="{{ $more_url }}" placeholder="/confidentialite">
                            <div class="form-text">Chemin relatif (recommandé) ou URL absolue.</div>
                        </div>
                    </section>

                    <div class="grimba-admin-form-actions">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>

                <hr class="my-4">

                <p class="small text-muted mb-0">
                    Pour tester l'apparition du bandeau, ouvrez le site dans une fenêtre privée
                    ou supprimez le cookie <code>grimba_cookie_consent</code> dans les outils
                    développeur du navigateur, puis rechargez n'importe quelle page.
                </p>
            </x-core::card.body>
        </x-core::card>
    </div>
@endsection
