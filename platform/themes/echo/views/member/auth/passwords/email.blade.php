@php
    /** S166 — Steve-styled forgot-password (request reset link). */
    Theme::layout('grimba-chrome');
    Theme::set('pageTitle', __('Mot de passe oublié'));
@endphp

<section class="grimba-auth py-5">
    <div class="container" style="max-width: 460px;">
        <header class="text-center mb-4">
            <a href="{{ url('/') }}" aria-label="GrimbaNews">
                <img src="{{ asset('storage/main/general/grimba-logo.svg') }}" alt="GrimbaNews" loading="eager" decoding="async" width="190" height="38" style="height:38px; width:auto;">
            </a>
        </header>

        <div class="glass-panel p-4 p-md-5">
            <h1 class="grimba-methodology__title m-0 mb-2" style="font-size: clamp(24px, 2.8vw, 30px); letter-spacing:-0.3px;">
                {{ __('Mot de passe oublié') }}
            </h1>
            <p class="opacity-75 mb-4" style="font-size:15px; line-height:1.5;">
                {{ __("Entrez l'email associé à votre compte. Nous vous enverrons un lien pour le réinitialiser.") }}
            </p>

            @if (session()->has('status'))
                <div role="alert" class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="alert alert-danger mb-3" style="border-radius:10px; font-size:14px;">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('public.member.password.email') }}" novalidate>
                @csrf
                <label for="grimba-pw-email" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                    {{ __('Adresse email') }} <span style="color:#c0392b;">*</span>
                </label>
                <input type="email" id="grimba-pw-email" name="email" required value="{{ old('email') }}" autocomplete="email" placeholder="vous@exemple.fr"
                       style="width:100%; padding:11px 14px; border-radius:9999px; border:1px solid rgba(26,23,19,0.18); background:rgba(255,255,255,0.7); font-size:15px; color:var(--gn-ink,#1a1713); margin-bottom:18px;">

                <button type="submit" class="btn-grimba btn-grimba--solid"
                        style="width:100%; padding:13px 18px; border-radius:9999px; background:var(--gn-ink,#1a1713); color:var(--gn-paper,#f6f1e8); font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; font-size:14px; border:none; cursor:pointer;">
                    {{ __('Envoyer le lien de réinitialisation') }}
                </button>

                <p class="small mt-3 mb-0 text-center" style="opacity:0.75;">
                    <a href="{{ route('public.member.login') }}" class="text-decoration-underline" style="color:var(--gn-ink,#1a1713);">
                        ← {{ __('Retour à la connexion') }}
                    </a>
                </p>
            </form>
        </div>
    </div>
</section>
