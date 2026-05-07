@php
    /**
     * S166 — GrimbaNews-styled login. Replaces the Botble Member
     * form-builder output (Bootstrap card, blue submit, "Archi Elite
     * JSC" footer leaking through the legacy Echo layout) with native
     * markup wrapped in grimba-chrome so the auth flow inherits the
     * urgency banner + main-header + topic chips + dark footer.
     *
     * Posts to public.member.login.post with the same field names
     * (email, password, remember) the LoginRequest validates.
     */
    Theme::layout('grimba-chrome');
    Theme::set('pageTitle', __('Connexion'));
@endphp

<section class="grimba-auth py-5">
    <div class="container" style="max-width: 460px;">

        <header class="text-center mb-4">
            <a href="{{ url('/') }}" aria-label="GrimbaNews">
                <img src="{{ asset('storage/main/general/grimba-logo.svg') }}"
                     alt="GrimbaNews" loading="eager" decoding="async" width="190" height="38" style="height:38px; width:auto;">
            </a>
        </header>

        <div class="glass-panel p-4 p-md-5">
            <h1 class="grimba-methodology__title m-0 mb-2" style="font-size: clamp(26px, 3vw, 34px); letter-spacing:-0.3px;">
                {{ __('Connexion') }}
            </h1>
            <p class="opacity-75 mb-4" style="font-size:15px; line-height:1.5;">
                {{ __('Suivez vos sujets, sauvegardez des histoires et accédez aux fonctionnalités d\'abonné.') }}
            </p>

            @if (session()->has('status'))
                <div role="alert" class="alert alert-success">{{ session('status') }}</div>
            @elseif (session()->has('auth_error_message'))
                <div role="alert" class="alert alert-danger">{{ session('auth_error_message') }}</div>
            @elseif (session()->has('auth_success_message'))
                <div role="alert" class="alert alert-success">{{ session('auth_success_message') }}</div>
            @elseif (session()->has('auth_warning_message'))
                <div role="alert" class="alert alert-warning">{{ session('auth_warning_message') }}</div>
            @endif

            @if ($errors->any())
                <div role="alert" class="alert alert-danger mb-3" style="border-radius:10px; font-size:14px;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('public.member.login.post') }}" novalidate>
                @csrf

                <label for="grimba-login-email" class="grimba-auth-label">
                    {{ __('Adresse email') }} <span class="grimba-auth-label__required">*</span>
                </label>
                <input type="email"
                       id="grimba-login-email"
                       name="email"
                       value="{{ old('email') }}"
                       autocomplete="email"
                       required
                       placeholder="vous@exemple.fr"
                       class="grimba-form-pill mb-3">

                <label for="grimba-login-password" class="grimba-auth-label">
                    {{ __('Mot de passe') }} <span class="grimba-auth-label__required">*</span>
                </label>
                <input type="password"
                       id="grimba-login-password"
                       name="password"
                       autocomplete="current-password"
                       required
                       placeholder="••••••••"
                       class="grimba-form-pill mb-3">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer; opacity:0.85;">
                        <input type="checkbox" name="remember" value="1" style="margin:0;">
                        <span>{{ __('Se souvenir de moi') }}</span>
                    </label>
                    <a href="{{ route('public.member.password.request') }}" class="small text-decoration-underline grimba-auth-link-muted">
                        {{ __('Mot de passe oublié ?') }}
                    </a>
                </div>

                <button type="submit"
                        class="btn-grimba btn-grimba--solid btn-grimba--block">
                    {{ __('Se connecter') }} →
                </button>

                @if(setting('member_enabled_registration', true))
                    <p class="small mt-3 mb-0 text-center" style="opacity:0.75;">
                        {{ __("Vous n'avez pas encore de compte ?") }}
                        <a href="{{ route('public.member.register') }}" class="text-decoration-underline grimba-auth-link-accent">
                            {{ __('Inscrivez-vous') }}
                        </a>
                    </p>
                @endif
            </form>
        </div>

        <p class="small text-center mt-4 opacity-60">
            {{ __('GrimbaNews — votre vue panoramique sur l\'actualité') }}
        </p>
    </div>
</section>
