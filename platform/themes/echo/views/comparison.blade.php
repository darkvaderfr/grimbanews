@php
    Theme::layout('grimba-chrome');
    /**
     * @var int $clusterId
     * @var \Illuminate\Support\Collection $posts
     * @var string $storyTitle
     */
@endphp

{!! Theme::partial('breadcrumbs', ['title' => __('Comparaison des sources')]) !!}

<section class="comparison-page py-5">
    <div class="container">
        <header class="glass-panel grimba-editorial-ribbon p-4 p-md-5 mb-4">
            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap mb-2">
                <span class="small text-uppercase opacity-75">{{ __('Dossier') }} #{{ $clusterId }}</span>
                {{-- Wave LLLL (Vader 2026-05-26) — Middle Ground signal
                     badge surfaced when the cluster carries the mg_ tag
                     set by grimba:reclassify-clusters. Tells readers
                     this is a "consensus forming" story, not just a
                     comparison page. --}}
                @if(! empty($middleGroundSince) || $middleGroundSince === 0)
                    <a href="{{ url('/juste-milieu') }}" class="text-decoration-none"
                       style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:9999px;background:#a855f720;color:#a855f7;font-weight:600;font-size:12px;letter-spacing:.04em;"
                       aria-label="{{ __('Ce dossier est classé juste milieu — voir tous les dossiers juste milieu') }}">
                        ● {{ __('Juste milieu') }}
                        @if($middleGroundSince === 0)
                            · {{ __("aujourd'hui") }}
                        @else
                            · {{ trans_choice('depuis :n jour|depuis :n jours', $middleGroundSince, ['n' => $middleGroundSince]) }}
                        @endif
                    </a>
                @endif
            </div>
            <h1 class="h2 mb-2">{{ $storyTitle }}</h1>
            <p class="opacity-85 mb-0">
                {{ __('Même histoire, plusieurs angles. Comparez comment chaque média couvre le sujet — et repérez les silences.') }}
            </p>
        </header>

        @if($posts->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">{{ __("Aucune source n'a été trouvée pour ce dossier.") }}</p>
            </div>
        @else
            {!! Theme::partial('story-comparison', [
                'posts'      => $posts,
                'storyTitle' => $storyTitle,
            ]) !!}

            {{-- Wave WWWWWW (Vader 2026-05-19) — share-kit on cluster
                 page. Cluster pages are the "see how every side covers
                 this" surface, the unique GrimbaNews value prop, and
                 the highest-leverage share target. Article pages already
                 carry the share-kit; cluster pages didn't. Same partial,
                 same 7 channels (X / Bluesky / Facebook / WhatsApp /
                 LinkedIn / Email / copy-link). --}}
            @include(Theme::getThemeNamespace('partials.story.share-kit'), [
                'title' => $storyTitle,
            ])

            {{-- Wave NNNNNN — Grimba-native "Other dossiers" rail
                 mirrors the article detail page. The cluster's first
                 post (bias-sorted) often lacks a proper topic category,
                 so we scan for the first post that DOES have a topic
                 and feed THAT to the partial. Falls back to the first
                 post if no member of the cluster has a topic — the
                 partial then bails cleanly. --}}
            @php
                $__rdSeed = $posts->first(function ($__p) {
                    $__p->loadMissing('categories');
                    return \App\Support\GrimbaEditorialCategories::primaryTopicFor($__p) !== null;
                }) ?? $posts->first();
            @endphp
            @include(Theme::getThemeNamespace('partials.story.related-dossiers'), ['post' => $__rdSeed])
        @endif
    </div>
</section>
