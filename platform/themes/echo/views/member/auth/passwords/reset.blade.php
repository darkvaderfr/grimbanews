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
                <img src="{{ asset('storage/main/general/grimba-logo.svg') }}" alt="GrimbaNews" style="height:38px; width:auto;">
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

                <label for="grimba-rst-email" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                    {{ __('Adresse email') }} <span style="color:#c0392b;">*</span>
                </label>
                <input type="email" id="grimba-rst-email" name="email" required value="{{ $email }}" autocomplete="email"
                       style="width:100%; padding:11px 14px; border-radius:9999px; border:1px solid rgba(26,23,19,0.18); background:rgba(255,255,255,0.7); font-size:15px; color:var(--gn-ink,#1a1713); margin-bottom:14px;">

                <label for="grimba-rst-password" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                    {{ __('Nouveau mot de passe') }} <span style="color:#c0392b;">*</span>
                </label>
                <input type="password" id="grimba-rst-password" name="password" required autocomplete="new-password" placeholder="••••••••"
                       style="width:100%; padding:11px 14px; border-radius:9999px; border:1px solid rgba(26,23,19,0.18); background:rgba(255,255,255,0.7); font-size:15px; color:var(--gn-ink,#1a1713); margin-bottom:14px;">

                <label for="grimba-rst-pwc" style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                    {{ __('Confirmer le nouveau mot de passe') }} <span style="color:#c0392b;">*</span>
                </label>
                <input type="password" id="grimba-rst-pwc" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"
                       style="width:100%; padding:11px 14px; border-radius:9999px; border:1px solid rgba(26,23,19,0.18); background:rgba(255,255,255,0.7); font-size:15px; color:var(--gn-ink,#1a1713); margin-bottom:18px;">

                <button type="submit" class="btn-grimba btn-grimba--solid"
                        style="width:100%; padding:13px 18px; border-radius:9999px; background:var(--gn-ink,#1a1713); color:var(--gn-paper,#f6f1e8); font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; font-size:14px; border:none; cursor:pointer;">
                    {{ __('Mettre à jour mon mot de passe') }}
                </button>
            </form>
        </div>
    </div>
</section>
