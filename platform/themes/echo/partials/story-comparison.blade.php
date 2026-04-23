@php
    /**
     * Story Comparison Partial — GroundNews-inspired side-by-side.
     *
     * @var \Illuminate\Support\Collection $posts  Posts in the cluster (ordered L / C / R)
     * @var string|null $storyTitle
     */

    $storyTitle = $storyTitle ?? ($posts->first()->name ?? 'Comparaison');
@endphp

{!! Theme::partial('source-diversity-meter', ['posts' => $posts]) !!}

<div class="story-comparison">
    <div class="row g-3">
        @foreach($posts as $clusterPost)
            <div class="col-md-4 col-12">
                <article class="comparison-card glass-card h-100 p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-uppercase opacity-75">
                            {{ $clusterPost->source_name ?? '—' }}
                        </span>
                        {!! Theme::partial('bias-badge', [
                            'bias'      => $clusterPost->bias_rating ?? null,
                            'showLabel' => true,
                            'size'      => 'sm',
                        ]) !!}
                    </div>

                    @if($clusterPost->image)
                        <a href="{{ $clusterPost->url ?? '#' }}" class="d-block mb-3">
                            {{ RvMedia::image($clusterPost->image, $clusterPost->name, 'medium') }}
                        </a>
                    @endif

                    <h3 class="h5 mb-2">
                        <a href="{{ $clusterPost->url ?? '#' }}" class="text-decoration-none title-hover">
                            {{ $clusterPost->name }}
                        </a>
                    </h3>

                    @if($clusterPost->description)
                        <p class="small opacity-85 mb-3">
                            {!! BaseHelper::clean(\Illuminate\Support\Str::limit(strip_tags($clusterPost->description), 180)) !!}
                        </p>
                    @endif

                    <div class="mt-auto small opacity-75 d-flex flex-wrap gap-2">
                        @if($clusterPost->credibility_score)
                            <span title="Score de crédibilité">
                                <x-core::icon name="ti ti-shield-check" style="width:14px;height:14px;" />
                                {{ $clusterPost->credibility_score }}/100
                            </span>
                        @endif
                        @if($clusterPost->ownership_type)
                            <span title="Type de propriété">
                                <x-core::icon name="ti ti-building" style="width:14px;height:14px;" />
                                {{ ucfirst($clusterPost->ownership_type) }}
                            </span>
                        @endif
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</div>
