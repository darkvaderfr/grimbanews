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
