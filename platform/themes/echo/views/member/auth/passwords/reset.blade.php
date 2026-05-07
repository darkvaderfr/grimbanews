@php
    /** S166 — Steve-styled reset-password (set new password from token). */
    Theme::layout('grimba-chrome');
    Theme::set('pageTitle', __('Nouveau mot de passe'));
    $token = request()->route('token') ?? request()->input('token');
    $email = request()->input('email') ?? old('email');
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
                {{ __('Nouveau mot de passe') }}
            </h1>
            <p class="opacity-75 mb-4" style="font-size:15px; line-height:1.5;">
                {{ __('Choisissez un nouveau mot de passe pour votre compte.') }}
            </p>

            @if ($errors->any())
                <div role="alert" class="alert alert-danger mb-3" style="border-radius:10px; font-size:14px;">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('public.member.password.update') }}" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label for="grimba-rst-email" class="grimba-auth-label">
                    {{ __('Adresse email') }} <span class="grimba-auth-label__required">*</span>
                </label>
                <input type="email" id="grimba-rst-email" name="email" required value="{{ $email }}" autocomplete="email"
                       class="grimba-form-pill mb-3">

                <label for="grimba-rst-password" class="grimba-auth-label">
                    {{ __('Nouveau mot de passe') }} <span class="grimba-auth-label__required">*</span>
                </label>
                <input type="password" id="grimba-rst-password" name="password" required autocomplete="new-password" placeholder="••••••••"
                       class="grimba-form-pill mb-3">

                <label for="grimba-rst-pwc" class="grimba-auth-label">
                    {{ __('Confirmer le nouveau mot de passe') }} <span class="grimba-auth-label__required">*</span>
                </label>
                <input type="password" id="grimba-rst-pwc" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"
                       class="grimba-form-pill mb-4">

                <button type="submit" class="btn-grimba btn-grimba--solid btn-grimba--block">
                    {{ __('Mettre à jour mon mot de passe') }}
                </button>
            </form>
        </div>
    </div>
</section>
