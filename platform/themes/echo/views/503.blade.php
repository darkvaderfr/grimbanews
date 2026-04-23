@php
    SeoHelper::setTitle('503 — Service indisponible');
    Theme::fireEventGlobalAssets();
@endphp
@extends(Theme::getThemeNamespace('layouts.grimba-chrome'))

@section('content')
    <section class="grimba-error grimba-error--503 py-5">
        <div class="container">
            <div class="glass-panel p-4 p-md-5 text-center" style="max-width: 720px; margin: 0 auto;">
                <span class="grimba-methodology__kicker">Maintenance</span>
                <h1 class="grimba-methodology__title mt-2 mb-3">
                    GrimbaNews est en maintenance.
                </h1>
                <p class="mb-4 opacity-85" style="max-width: 52ch; margin-left: auto; margin-right: auto;">
                    Nous améliorons la plateforme. Nous revenons très vite. Merci pour
                    votre patience.
                </p>
            </div>
        </div>
    </section>
@endsection
