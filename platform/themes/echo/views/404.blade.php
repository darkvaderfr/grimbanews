@php
    SeoHelper::setTitle(__('404 — Page introuvable'));
    Theme::fireEventGlobalAssets();

    // S337 — recent stories rail as a safety-net escape hatch.
    use App\Support\GrimbaTranslationPresenter as GnTr;
    $__recentPosts = \Botble\Blog\Models\Post::query()
        ->where('status', 'published')
        ->tap(fn ($q) => GnTr::orderForTargetLocale($q))
        ->limit(4)
        ->get(['id', 'name', 'translated_name', 'translated_to', 'image', 'source_name', 'bias_rating', 'created_at']);
@endphp
@extends(Theme::getThemeNamespace('layouts.grimba-chrome'))

@section('content')
    <section class="grimba-error grimba-error--404 py-5">
        <div class="container">
            <div class="glass-panel p-4 p-md-5 text-center mb-4" style="max-width: 760px; margin: 0 auto;">
                <span class="grimba-methodology__kicker">{{ __('Erreur 404') }}</span>
                <h1 class="grimba-methodology__title mt-2 mb-3">
                    {{ __('Cette page a disparu du radar.') }}
                </h1>
                <p class="mb-4 opacity-85" style="max-width: 52ch; margin-left: auto; margin-right: auto;">
                    {{ __("Le lien que vous avez suivi n'existe plus, a été déplacé, ou n'a jamais existé. Ça arrive. Voici par où repartir.") }}
                </p>

                {{-- S337 — search escape hatch. --}}
                <form action="{{ url('/search') }}" method="get" class="d-flex justify-content-center mb-3" role="search" aria-label="{{ __('Recherche depuis la page 404') }}">
                    <div class="input-group" style="max-width: 460px;">
                        <input type="search" name="q" class="form-control"
                               placeholder="{{ __('Chercher une histoire, un sujet, une source…') }}"
                               aria-label="{{ __('Recherche') }}"
                               autofocus>
                        <button type="submit" class="btn-grimba btn-grimba--solid" style="border-radius: 0 999px 999px 0;">
                            {{ __('Rechercher') }}
                        </button>
                    </div>
                </form>

                <div class="d-flex gap-2 flex-wrap justify-content-center">
                    <a href="{{ url('/') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __("Retour à l'accueil") }}</a>
                    <a href="{{ url('/comparatif') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Tous les dossiers') }}</a>
                    <a href="{{ url('/angles-morts') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Angles morts') }}</a>
                    <a href="{{ url('/methodologie') }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Méthodologie') }}</a>
                </div>
            </div>

            {{-- S337 — recent stories rail. Concrete next-step content
                  when the bookmark / stale link missed. --}}
            @if($__recentPosts->isNotEmpty())
                <div style="max-width: 1080px; margin: 0 auto;">
                    <h2 class="h6 mb-3 text-center" style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; font-size:13px; opacity:0.7;">
                        {{ __('Dernières publications') }}
                    </h2>
                    <div class="row g-3">
                        @foreach($__recentPosts as $rp)
                            <div class="col-md-6 col-lg-3">
                                @include(Theme::getThemeNamespace('partials.blog.post.partials.items.grid'), ['post' => $rp])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
