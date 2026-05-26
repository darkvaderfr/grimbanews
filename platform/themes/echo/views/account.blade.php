@php
    /**
     * S168 — Steve-styled member dashboard "Mon compte" landing.
     * Replaces the Botble default sidebar+widgets layout (built for
     * authoring sites where members write blog posts) with a reader-
     * focused welcome card. GrimbaNews members are READERS, not
     * authors — published-posts / pending / draft stat widgets are
     * meaningless to them.
     *
     * Surfaces what readers actually do here: jump to /pour-vous,
     * see their bias profile, export their reading history, log out.
     */
    Theme::layout('grimba-chrome');
    Theme::set('pageTitle', __('Mon compte'));

    $user = $user ?? auth('member')->user();
    $firstName = $user?->first_name ?? trim(explode(' ', (string) ($user?->name ?? ''))[0] ?? '');
    $vaultIds = $vaultIds ?? \App\Support\GrimbaVault::parseIds((string) request()->cookie(\App\Support\GrimbaVault::COOKIE, ''));
    $vaultCount = count($vaultIds);
    $digestEnabled = \App\Support\GrimbaVault::memberDigestEnabled($user);
    $digestSnapshotCount = count(\App\Support\GrimbaVault::memberDigestIds($user));
    $savedSearches = $savedSearches ?? \App\Support\GrimbaSavedSearches::forMember($user);
    $savedSearchCount = $savedSearches->count();
@endphp

<section class="grimba-account py-5">
    <div class="container" style="max-width: 720px;">

        <header class="text-center mb-4">
            <span class="grimba-methodology__kicker">Mon compte</span>
            <h1 class="grimba-methodology__title mt-2 mb-2" style="font-size: clamp(28px, 3.4vw, 40px); letter-spacing:-0.4px;">
                {{ __('Bonjour') }}@if($firstName), {{ $firstName }}@endif
            </h1>
            <p class="opacity-75 mb-0" style="font-size:16px; line-height:1.5;">
                {{ __('Votre vue panoramique sur l\'actualité.') }}
            </p>
        </header>

        @if(session('status'))
            <div class="grimba-account-alert mb-4">
                {{ session('status') }}
            </div>
        @endif

        {{-- Reading bias mix — borrows the /pour-vous full widget --}}
        <div class="glass-panel p-4 p-md-5 mb-4">
            {!! Theme::partial('bias-mix', ['variant' => 'full']) !!}
        </div>

        {{-- Action grid --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">

            <a href="{{ url('/pour-vous') }}" class="glass-panel p-4" style="display:block; text-decoration:none; color:var(--gn-ink,#1a1713); transition:transform .15s ease;"
               onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
                <div style="font-size:24px; margin-bottom:8px;">📰</div>
                <h3 style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:18px; margin:0 0 4px;">
                    {{ __('Pour vous') }}
                </h3>
                <p class="small opacity-75 m-0">{{ __('Le fil de vos sujets suivis.') }}</p>
            </a>

            <a href="{{ url('/local') }}" class="glass-panel p-4" style="display:block; text-decoration:none; color:var(--gn-ink,#1a1713);"
               onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
                <div style="font-size:24px; margin-bottom:8px;">🌍</div>
                <h3 style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:18px; margin:0 0 4px;">
                    {{ __('Local') }}
                </h3>
                <p class="small opacity-75 m-0">{{ __('Actualité de votre ville.') }}</p>
            </a>

            <a href="{{ url('/angles-morts') }}" class="glass-panel p-4" style="display:block; text-decoration:none; color:var(--gn-ink,#1a1713);"
               onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
                <div style="font-size:24px; margin-bottom:8px;">🕳️</div>
                <h3 style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:18px; margin:0 0 4px;">
                    {{ __('Angles morts') }}
                </h3>
                <p class="small opacity-75 m-0">{{ __('Stories couvertes par un seul côté.') }}</p>
            </a>

            {{-- Wave UUU (Vader 2026-05-26) — /juste-milieu account tile parity. --}}
            <a href="{{ url('/juste-milieu') }}" class="glass-panel p-4" style="display:block; text-decoration:none; color:var(--gn-ink,#1a1713);"
               onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''"
               aria-label="{{ __('Tableau Juste milieu — stories couvertes équitablement par la gauche et la droite') }}">
                <div style="font-size:24px; margin-bottom:8px; color:#a855f7;" aria-hidden="true">⊕</div>
                <h3 style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:18px; margin:0 0 4px;">
                    {{ __('Juste milieu') }}
                </h3>
                <p class="small opacity-75 m-0">{{ __('Stories où gauche et droite se rejoignent.') }}</p>
            </a>

            <a href="{{ url('/pour-vous/export.csv') }}" class="glass-panel p-4" style="display:block; text-decoration:none; color:var(--gn-ink,#1a1713);"
               onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
                <div style="font-size:24px; margin-bottom:8px;">📊</div>
                <h3 style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:18px; margin:0 0 4px;">
                    {{ __('Mon historique') }}
                </h3>
                <p class="small opacity-75 m-0">{{ __('Exporter mes lectures en CSV.') }}</p>
            </a>

            {{-- S173 — saved articles vault --}}
            <a href="{{ url('/coffre') }}" class="glass-panel p-4" style="display:block; text-decoration:none; color:var(--gn-ink,#1a1713);"
               onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
                <div style="font-size:24px; margin-bottom:8px;">★</div>
                <h3 style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-weight:600; font-size:18px; margin:0 0 4px;">
                    {{ __('Mon coffre') }}
                </h3>
                <p class="small opacity-75 m-0">{{ __('Articles sauvegardés pour plus tard.') }}</p>
            </a>
        </div>

        <form method="POST" action="{{ route('public.account.vault-digest') }}" class="glass-panel grimba-account-digest p-4 mt-4">
            @csrf
            <input type="hidden" name="weekly_vault_digest" value="{{ $digestEnabled ? '0' : '1' }}">

            <div class="grimba-account-digest__header">
                <div>
                    <span class="grimba-account-digest__kicker">{{ __('Digest coffre') }}</span>
                    <h2>{{ __('Alerte email hebdomadaire') }}</h2>
                </div>
                <span class="grimba-account-digest__status {{ $digestEnabled ? 'is-on' : '' }}">
                    {{ $digestEnabled ? __('Activé') : __('Inactif') }}
                </span>
            </div>

            <p class="grimba-account-digest__body">
                @if($digestEnabled)
                    {{ __('Chaque lundi, GrimbaNews vous envoie les articles synchronisés depuis votre coffre.') }}
                @else
                    {{ __('Recevez une fois par semaine les articles que vous sauvegardez dans votre coffre.') }}
                @endif
            </p>

            <div class="grimba-account-digest__footer">
                <span>
                    @if($digestEnabled)
                        {{ trans_choice(':count article synchronisé|:count articles synchronisés', $digestSnapshotCount, ['count' => $digestSnapshotCount]) }}
                    @else
                        {{ trans_choice(':count article dans ce navigateur|:count articles dans ce navigateur', $vaultCount, ['count' => $vaultCount]) }}
                    @endif
                </span>
                <button type="submit" class="btn-grimba {{ $digestEnabled ? 'btn-grimba--ghost' : 'btn-grimba--solid' }} btn-grimba--sm">
                    {{ $digestEnabled ? __('Désactiver') : __('Activer le digest') }}
                </button>
            </div>
        </form>

        <div class="glass-panel grimba-account-searches p-4 mt-4">
            <div class="grimba-account-searches__header">
                <div>
                    <span class="grimba-account-digest__kicker">{{ __('Alertes recherche') }}</span>
                    <h2>{{ __('Recherches suivies') }}</h2>
                </div>
                <span class="grimba-account-digest__status {{ $savedSearchCount ? 'is-on' : '' }}">
                    {{ trans_choice(':count active|:count actives', $savedSearchCount, ['count' => $savedSearchCount]) }}
                </span>
            </div>

            @if($savedSearches->isEmpty())
                <p class="grimba-account-searches__empty">
                    {{ __('Aucune alerte active pour le moment.') }}
                </p>
                <a href="{{ url('/search') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                    {{ __('Créer une alerte') }}
                </a>
            @else
                <div class="grimba-account-searches__list">
                    @foreach($savedSearches as $savedSearch)
                        <div class="grimba-account-searches__row">
                            <div class="grimba-account-searches__meta">
                                <a href="{{ \App\Support\GrimbaSavedSearches::searchUrl($savedSearch) }}">
                                    {{ \App\Support\GrimbaSavedSearches::label($savedSearch) }}
                                </a>
                                <span>
                                    @if($savedSearch->last_sent_at)
                                        {{ __('Dernier envoi : :date', ['date' => \Carbon\Carbon::parse($savedSearch->last_sent_at)->format('d/m/Y')]) }}
                                    @elseif($savedSearch->last_checked_at)
                                        {{ __('Dernière vérification : :date', ['date' => \Carbon\Carbon::parse($savedSearch->last_checked_at)->format('d/m/Y')]) }}
                                    @else
                                        {{ __('En attente du prochain digest hebdomadaire.') }}
                                    @endif
                                </span>
                            </div>

                            <form method="POST" action="{{ route('public.saved-searches.destroy', $savedSearch->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                                    {{ __('Retirer') }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Account meta + logout --}}
        <div class="text-center mt-4">
            <p class="small opacity-65 mb-2">
                {{ __('Connecté en tant que') }} <strong>{{ $user?->email }}</strong>
            </p>
            <form method="POST" action="{{ route('public.member.logout') }}" class="d-inline">
                @csrf
                <button type="submit"
                        class="btn-grimba btn-grimba--ghost btn-grimba--sm"
                        style="padding:8px 18px; border-radius:9999px; border:1px solid rgba(26,23,19,0.2); background:transparent; color:var(--gn-ink,#1a1713); font-weight:600; font-size:13px; cursor:pointer;">
                    {{ __('Déconnexion') }}
                </button>
            </form>
        </div>
    </div>
</section>
