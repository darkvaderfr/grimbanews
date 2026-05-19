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
            <span class="small text-uppercase opacity-75">{{ __('Dossier') }} #{{ $clusterId }}</span>
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
