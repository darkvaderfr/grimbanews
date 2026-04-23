@php
    SeoHelper::setTitle('404 — Page introuvable');
    Theme::fireEventGlobalAssets();
@endphp
@extends(Theme::getThemeNamespace('layouts.grimba-chrome'))

@section('content')
    <section class="grimba-error grimba-error--404 py-5">
        <div class="container">
            <div class="glass-panel p-4 p-md-5 text-center" style="max-width: 720px; margin: 0 auto;">
                <span class="grimba-methodology__kicker">Erreur 404</span>
                <h1 class="grimba-methodology__title mt-2 mb-3">
                    Cette page a disparu du radar.
                </h1>
                <p class="mb-4 opacity-85" style="max-width: 52ch; margin-left: auto; margin-right: auto;">
                    Le lien que vous avez suivi n'existe plus, a été déplacé, ou n'a
                    jamais existé. Ça arrive. Voici par où repartir.
                </p>
                <div class="d-flex gap-2 flex-wrap justify-content-center">
                    <a href="{{ url('/') }}" class="btn-grimba btn-grimba--solid">Retour à l'accueil</a>
                    <a href="{{ url('/blog') }}" class="btn-grimba btn-grimba--ghost">Voir le fil</a>
                    <a href="{{ url('/angles-morts') }}" class="btn-grimba btn-grimba--ghost">Angles morts</a>
                    <a href="{{ url('/methodologie') }}" class="btn-grimba btn-grimba--ghost">Méthodologie</a>
                </div>
            </div>
        </div>
    </section>
@endsection
