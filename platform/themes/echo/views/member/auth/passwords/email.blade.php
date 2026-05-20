@php
    /** S166 — Steve-styled forgot-password (request reset link). */
    Theme::layout('grimba-chrome');
    Theme::set('pageTitle', __('Mot de passe oublié'));
    Theme::set('grimbaChromeAds', false);
@endphp

<section class="grimba-auth py-5">
    <div class="container" style="max-width: 460px;">
        <header class="text-center mb-4">
            {!! Theme::partial('auth-wordmark') !!}
        </header>

        <div class="glass-panel p-4 p-md-5">
            <h1 class="grimba-methodology__title m-0 mb-2" style="font-size: clamp(24px, 2.8vw, 30px); letter-spacing:-0.3px;">
                {{ __('Mot de passe oublié') }}
            </h1>
            <p class="grimba-auth__lede mb-4" style="font-size:15px; line-height:1.5;">
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
                <label for="grimba-pw-email" class="grimba-auth-label">
                    {{ __('Adresse email') }} <span class="grimba-auth-label__required">*</span>
                </label>
                <input type="email" id="grimba-pw-email" name="email" required value="{{ old('email') }}" autocomplete="email" placeholder="vous@exemple.fr"
                       class="grimba-form-pill mb-4">

                <button type="submit" class="btn-grimba btn-grimba--solid btn-grimba--block">
                    {{ __('Envoyer le lien de réinitialisation') }}
                </button>

                <p class="small mt-3 mb-0 text-center" style="opacity:0.75;">
                    <a href="{{ route('public.member.login') }}" class="text-decoration-underline grimba-auth-link-muted">
                        ← {{ __('Retour à la connexion') }}
                    </a>
                </p>
            </form>
        </div>
    </div>
</section>
