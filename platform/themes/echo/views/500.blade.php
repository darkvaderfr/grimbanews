@php
    SeoHelper::setTitle(__('500 — Erreur interne'));
    Theme::fireEventGlobalAssets();
@endphp
@extends(Theme::getThemeNamespace('layouts.grimba-chrome'))

@section('content')
    <section class="grimba-error grimba-error--500 py-5">
        <div class="container">
            <div class="glass-panel p-4 p-md-5 text-center" style="max-width: 720px; margin: 0 auto;">
                <span class="grimba-methodology__kicker">{{ __('Erreur 500') }}</span>
                <h1 class="grimba-methodology__title mt-2 mb-3">
                    {{ __("Quelque chose s'est cassé côté serveur.") }}
                </h1>
                <p class="mb-4 opacity-85" style="max-width: 52ch; margin-left: auto; margin-right: auto;">
                    {{ __("Une erreur interne nous empêche de servir cette page pour l'instant. L'équipe a été notifiée. Réessayez dans quelques minutes, ou revenez à la liste des dernières histoires.") }}
                </p>
                <div class="d-flex gap-2 flex-wrap justify-content-center">
                    <a href="{{ url('/') }}" class="btn-grimba btn-grimba--solid">{{ __("Retour à l'accueil") }}</a>
                    <a href="{{ url('/blog') }}" class="btn-grimba btn-grimba--ghost">{{ __('Voir le fil') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
