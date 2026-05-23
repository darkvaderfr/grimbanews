@php
    Theme::layout('grimba-chrome');
    /**
     * Middle Ground Feed — histoires couvertes équitablement par
     * la gauche et la droite (L=R, both ≥ center).
     *
     * Wave DDDDDDDDDDD (Vader 2026-05-23) — mirror of /angles-morts
     * for the Juste milieu / Middle Ground editorial signal. Driven
     * by clusters tagged in story_clusters.review_action with the
     * 'mg_' prefix by grimba:reclassify-clusters.
     *
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $posts
     * @var int $middleGroundClusterCount
     */
@endphp

{!! Theme::partial('breadcrumbs', ['title' => __('Juste milieu')]) !!}

<section class="middle-ground-page py-5">
    <div class="container">
        <header class="glass-panel grimba-editorial-ribbon p-4 mb-4">
            <span class="middle-ground-badge mb-2" style="display:inline-block;padding:4px 10px;border-radius:9999px;background:#a855f720;color:#a855f7;font-weight:600;font-size:13px;letter-spacing:.04em;">
                ● {{ __('Juste milieu') }}
            </span>
            <h1 class="h2 mt-2 mb-2 d-inline-flex align-items-center gap-2 flex-wrap">
                <span>{{ __('Les histoires couvertes des deux côtés') }}</span>
                @include(Theme::getThemeNamespace('partials.info-pill'), [
                    'size' => 'sm',
                    'body' => __("Le Juste milieu signale les histoires où la gauche et la droite couvrent en proportions égales — un signal éditorial distinct de l'angle mort. Quand les deux camps convergent, c'est souvent que l'événement est consensuel ou qu'il transcende le clivage politique."),
                ])
            </h1>
            <p class="middle-ground-page__lede mb-3">
                {{ __("Le Juste milieu est l'inverse de l'angle mort : au lieu d'une histoire que seul un côté du spectre couvre, c'est une histoire qui réunit les deux extrêmes en proportions égales. GrimbaNews les signale pour que vous voyiez ce qui rassemble.") }}
            </p>

            <p class="small opacity-75 mb-0">
                {{ trans_choice(':count cluster en Juste milieu|:count clusters en Juste milieu', $middleGroundClusterCount, ['count' => $middleGroundClusterCount]) }}
                · <a href="{{ url('/angles-morts') }}" class="text-decoration-underline">{{ __('Voir aussi les angles morts') }} →</a>
            </p>
        </header>

        @if($posts->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">{{ __("Aucune histoire « Juste milieu » identifiée pour le moment.") }}</p>
                <p class="small opacity-75 mt-2 mb-0">
                    {{ __('Exécutez') }}
                    <code>php artisan grimba:reclassify-clusters --persist</code>
                    {{ __('pour reclassifier les clusters récents.') }}
                </p>
            </div>
        @else
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-lg-4 col-md-6 col-12" id="cluster-{{ (int) $post->story_cluster_id }}">
                        @include(Theme::getThemeNamespace('partials.blog.post.partials.items.card'), ['post' => $post])
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {!! $posts->links() !!}
            </div>
        @endif
    </div>
</section>
