@php
    /** S166 — Steve-styled register page (mirrors login). */
    Theme::layout('grimba-chrome');
    Theme::set('pageTitle', __('Inscription'));
    Theme::set('grimbaChromeAds', false);
@endphp

<section class="grimba-auth py-5">
    <div class="container" style="max-width: 520px;">

        <header class="text-center mb-4">
            {!! Theme::partial('auth-wordmark') !!}
        </header>

        <div class="glass-panel p-4 p-md-5">
            <h1 class="grimba-methodology__title m-0 mb-2" style="font-size: clamp(26px, 3vw, 34px); letter-spacing:-0.3px;">
                {{ __('Créer un compte') }}
            </h1>
            <p class="grimba-auth__lede mb-4" style="font-size:15px; line-height:1.5;">
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

                <div class="grimba-auth-grid">
                    <div>
                        <label for="grimba-reg-first" class="grimba-auth-label">
                            {{ __('Prénom') }} <span class="grimba-auth-label__required">*</span>
                        </label>
                        <input type="text" id="grimba-reg-first" name="first_name" required value="{{ old('first_name') }}" autocomplete="given-name" class="grimba-form-pill mb-3">
                    </div>
                    <div>
                        <label for="grimba-reg-last" class="grimba-auth-label">
                            {{ __('Nom') }} <span class="grimba-auth-label__required">*</span>
                        </label>
                        <input type="text" id="grimba-reg-last" name="last_name" required value="{{ old('last_name') }}" autocomplete="family-name" class="grimba-form-pill mb-3">
                    </div>
                </div>

                <label for="grimba-reg-email" class="grimba-auth-label">
                    {{ __('Adresse email') }} <span class="grimba-auth-label__required">*</span>
                </label>
                <input type="email" id="grimba-reg-email" name="email" required value="{{ old('email') }}" autocomplete="email" placeholder="vous@exemple.fr" class="grimba-form-pill mb-3">

                <label for="grimba-reg-password" class="grimba-auth-label">
                    {{ __('Mot de passe') }} <span class="grimba-auth-label__required">*</span>
                </label>
                <input type="password" id="grimba-reg-password" name="password" required autocomplete="new-password" placeholder="••••••••" class="grimba-form-pill mb-3">

                <label for="grimba-reg-pwc" class="grimba-auth-label">
                    {{ __('Confirmer le mot de passe') }} <span class="grimba-auth-label__required">*</span>
                </label>
                <input type="password" id="grimba-reg-pwc" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" class="grimba-form-pill mb-3">

                <p class="small opacity-65 mb-3" style="font-size:12px; line-height:1.5;">
                    {{ __("En vous inscrivant, vous acceptez nos") }}
                    <a href="{{ url('/conditions') }}" class="text-decoration-underline" style="color:inherit;">{{ __('conditions d\'utilisation') }}</a>
                    {{ __('et notre') }}
                    <a href="{{ url('/confidentialite') }}" class="text-decoration-underline" style="color:inherit;">{{ __('politique de confidentialité') }}</a>.
                </p>

                <button type="submit" class="btn-grimba btn-grimba--solid btn-grimba--block">
                    {{ __('Créer mon compte') }} →
                </button>

                <p class="small mt-3 mb-0 text-center" style="opacity:0.75;">
                    {{ __('Vous avez déjà un compte ?') }}
                    <a href="{{ route('public.member.login') }}" class="text-decoration-underline grimba-auth-link-accent">
                        {{ __('Se connecter') }}
                    </a>
                </p>
            </form>
        </div>
    </div>
</section>
