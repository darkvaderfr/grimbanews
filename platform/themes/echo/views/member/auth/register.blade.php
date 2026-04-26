@php
    /** S166 — Steve-styled register page (mirrors login). */
    Theme::layout('grimba-chrome');
    Theme::set('pageTitle', __('Inscription'));

    // Common pill input style — repeated as a string so each field
    // stays self-contained.
    $pillInput = "
        width:100%;
        padding:11px 14px;
        border-radius:9999px;
        border:1px solid rgba(26,23,19,0.18);
        background:rgba(255,255,255,0.7);
        font-size:15px;
        color:var(--gn-ink,#1a1713);
        margin-bottom:14px;
    ";
@endphp

<section class="grimba-auth py-5">
    <div class="container" style="max-width: 520px;">

        <header class="text-center mb-4">
            <a href="{{ url('/') }}" aria-label="GrimbaNews">
                <img src="{{ asset('storage/main/general/grimba-logo.svg') }}"
                     alt="GrimbaNews" loading="eager" decoding="async" width="190" height="38" style="height:38px; width:auto;">
            </a>
        </header>

        <div class="glass-panel p-4 p-md-5">
            <h1 class="grimba-methodology__title m-0 mb-2" style="font-size: clamp(26px, 3vw, 34px); letter-spacing:-0.3px;">
                {{ __('Créer un compte') }}
            </h1>
            <p class="opacity-75 mb-4" style="font-size:15px; line-height:1.5;">
                {{ __('Suivez vos sujets, sauvegardez vos histoires, et personnalisez votre vue panoramique de l\'actualité.') }}
            </p>

            @if (session()->has('auth_error_message'))
                <div role="alert" class="alert alert-danger">{{ session('auth_error_message') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="alert alert-danger mb-3" style="border-radius:10px; font-size:14px;">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('public.member.register.post') }}" novalidate>
                @csrf

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label for="grimba-reg-first" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                            {{ __('Prénom') }} <span style="color:#c0392b;">*</span>
                        </label>
                        <input type="text" id="grimba-reg-first" name="first_name" required value="{{ old('first_name') }}" autocomplete="given-name" style="{{ $pillInput }}">
                    </div>
                    <div>
                        <label for="grimba-reg-last" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                            {{ __('Nom') }} <span style="color:#c0392b;">*</span>
                        </label>
                        <input type="text" id="grimba-reg-last" name="last_name" required value="{{ old('last_name') }}" autocomplete="family-name" style="{{ $pillInput }}">
                    </div>
                </div>

                <label for="grimba-reg-email" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                    {{ __('Adresse email') }} <span style="color:#c0392b;">*</span>
                </label>
                <input type="email" id="grimba-reg-email" name="email" required value="{{ old('email') }}" autocomplete="email" placeholder="vous@exemple.fr" style="{{ $pillInput }}">

                <label for="grimba-reg-password" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                    {{ __('Mot de passe') }} <span style="color:#c0392b;">*</span>
                </label>
                <input type="password" id="grimba-reg-password" name="password" required autocomplete="new-password" placeholder="••••••••" style="{{ $pillInput }}">

                <label for="grimba-reg-pwc" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                    {{ __('Confirmer le mot de passe') }} <span style="color:#c0392b;">*</span>
                </label>
                <input type="password" id="grimba-reg-pwc" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" style="{{ $pillInput }}">

                <p class="small opacity-65 mb-3" style="font-size:12px; line-height:1.5;">
                    {{ __("En vous inscrivant, vous acceptez nos") }}
                    <a href="{{ url('/conditions') }}" class="text-decoration-underline" style="color:inherit;">{{ __('conditions d\'utilisation') }}</a>
                    {{ __('et notre') }}
                    <a href="{{ url('/confidentialite') }}" class="text-decoration-underline" style="color:inherit;">{{ __('politique de confidentialité') }}</a>.
                </p>

                <button type="submit" class="btn-grimba btn-grimba--solid"
                        style="width:100%; padding:13px 18px; border-radius:9999px; background:var(--gn-ink,#1a1713); color:var(--gn-paper,#f6f1e8); font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; font-size:14px; border:none; cursor:pointer;">
                    {{ __('Créer mon compte') }} →
                </button>

                <p class="small mt-3 mb-0 text-center" style="opacity:0.75;">
                    {{ __('Vous avez déjà un compte ?') }}
                    <a href="{{ route('public.member.login') }}" class="text-decoration-underline" style="color:#c0392b; font-weight:600;">
                        {{ __('Se connecter') }}
                    </a>
                </p>
            </form>
        </div>
    </div>
</section>
