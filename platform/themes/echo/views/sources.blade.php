@php
    /**
     * @var array<string,\Illuminate\Support\Collection> $grouped  Sources grouped by bias_rating
     * @var int $total
     */

    $biasMeta = [
        'left'    => ['label' => 'Gauche',      'color' => '#3b82f6'],
        'center'  => ['label' => 'Centre',      'color' => '#22c55e'],
        'right'   => ['label' => 'Droite',      'color' => '#ef4444'],
        'unknown' => ['label' => 'Non évalué',  'color' => '#9ca3af'],
    ];

    $ownershipLabel = [
        'state'       => 'État',
        'corporate'   => 'Privé',
        'independent' => 'Indépendant',
        'nonprofit'   => 'Associatif',
    ];
@endphp

{!! Theme::partial('breadcrumbs', ['title' => 'Sources']) !!}

<section class="sources-page py-5">
    <div class="container">
        <header class="glass-panel p-4 mb-4">
            <h1 class="h2 mb-2">Sources classées</h1>
            <p class="mb-0 opacity-85">
                {{ $total }} médias suivis par GrimbaNews — biais éditorial, type de propriété,
                score de crédibilité et pays d'origine. Les classements sont ouverts et
                révisables.
            </p>
        </header>

        @foreach(['left','center','right','unknown'] as $biasKey)
            @php $bucket = $grouped[$biasKey] ?? collect(); @endphp
            @continue($bucket->isEmpty())

            <section class="mb-5">
                <h2 class="h4 d-flex align-items-center gap-2 mb-3">
                    <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:{{ $biasMeta[$biasKey]['color'] }};"></span>
                    {{ $biasMeta[$biasKey]['label'] }}
                    <span class="small opacity-75">({{ $bucket->count() }})</span>
                </h2>

                <div class="row g-3">
                    @foreach($bucket as $src)
                        <div class="col-lg-4 col-md-6 col-12">
                            <article class="glass-card p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h3 class="h6 mb-0">
                                        @if($src->website)
                                            <a href="https://{{ $src->website }}" target="_blank" rel="noopener" class="text-decoration-none">
                                                {{ $src->name }}
                                            </a>
                                        @else
                                            {{ $src->name }}
                                        @endif
                                    </h3>
                                    <span class="bias-badge bias-badge--sm"
                                          style="background: {{ $biasMeta[$biasKey]['color'] }}22;
                                                 color: {{ $biasMeta[$biasKey]['color'] }};
                                                 border: 1px solid {{ $biasMeta[$biasKey]['color'] }}44;">
                                        {{ $biasMeta[$biasKey]['label'] }}
                                    </span>
                                </div>

                                <div class="small opacity-85 d-flex flex-wrap gap-2">
                                    @if($src->ownership_type)
                                        <span>{{ $ownershipLabel[$src->ownership_type] ?? ucfirst($src->ownership_type) }}</span>
                                    @endif
                                    @if($src->country)
                                        <span class="opacity-70">·</span>
                                        <span>{{ $src->country }}</span>
                                    @endif
                                    @if($src->language)
                                        <span class="opacity-70">·</span>
                                        <span class="text-uppercase">{{ $src->language }}</span>
                                    @endif
                                </div>

                                @if($src->credibility_score)
                                    @php
                                        $score = (int) $src->credibility_score;
                                        $barColor = $score >= 85 ? '#22c55e' : ($score >= 70 ? '#eab308' : '#ef4444');
                                    @endphp
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="opacity-75">Crédibilité</span>
                                            <strong>{{ $score }}/100</strong>
                                        </div>
                                        <div style="height:6px;border-radius:9999px;background:rgba(0,0,0,0.08);overflow:hidden;">
                                            <div style="width:{{ $score }}%;height:100%;background:{{ $barColor }};"></div>
                                        </div>
                                    </div>
                                @endif
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</section>
