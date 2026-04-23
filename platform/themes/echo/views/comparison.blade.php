@php
    /**
     * @var int $clusterId
     * @var \Illuminate\Support\Collection $posts
     * @var string $storyTitle
     */
@endphp

{!! Theme::partial('breadcrumbs', ['title' => 'Comparaison des sources']) !!}

<section class="comparison-page py-5">
    <div class="container">
        <header class="mb-4">
            <span class="small text-uppercase opacity-75">Dossier #{{ $clusterId }}</span>
            <h1 class="h2 mb-2">{{ $storyTitle }}</h1>
            <p class="opacity-85 mb-0">
                Même histoire, plusieurs angles. Comparez comment chaque média couvre le sujet —
                et repérez les silences.
            </p>
        </header>

        @if($posts->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">Aucune source n'a été trouvée pour ce dossier.</p>
            </div>
        @else
            {!! Theme::partial('story-comparison', [
                'posts'      => $posts,
                'storyTitle' => $storyTitle,
            ]) !!}
        @endif
    </div>
</section>
