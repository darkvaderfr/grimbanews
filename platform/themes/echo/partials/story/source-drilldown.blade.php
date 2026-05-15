@php
    use App\Support\GrimbaSourceBreakdown;
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Illuminate\Support\Str;

    /**
     * Compact article provenance matrix. The full source analytics live in
     * the breakdown panel; this block keeps the article page readable while
     * still showing source origin, country bias, and the evidence line.
     *
     * @var \Illuminate\Support\Collection $clusterPosts
     * @var \Illuminate\Support\Collection|null $sourceMeta
     */
    $biasMeta = [
        'left' => ['label' => __('Gauche'), 'color' => '#3b82f6', 'short' => 'L'],
        'center' => ['label' => __('Centre'), 'color' => '#8a8a8a', 'short' => 'C'],
        'right' => ['label' => __('Droite'), 'color' => '#e84c3d', 'short' => 'R'],
        'unknown' => ['label' => __('Non classé'), 'color' => '#6b6459', 'short' => '·'],
    ];

    $cluster = collect($clusterPosts ?? []);
    GnTr::warm($cluster);

    $sources = $sourceMeta ?? collect();
    $breakdown = $cluster->isNotEmpty() ? GrimbaSourceBreakdown::fromPosts($cluster) : [];
    $sourceCount = (int) (($breakdown['sources'] ?? collect())->count());
    $knownBiasBuckets = collect($breakdown['knownBiasBuckets'] ?? []);
    $countryBiasBuckets = collect($breakdown['countryBiasBuckets'] ?? [])->take(4)->values();
    $topOrigin = $breakdown['topOrigin'] ?? null;
    $dominantBias = $breakdown['dominantBias'] ?? null;
    $dominantBiasPct = (int) ($breakdown['dominantBiasPct'] ?? 0);
    $biasBalanceScore = (int) ($breakdown['biasBalanceScore'] ?? 0);

    $rows = $cluster
        ->map(function ($post) use ($sources, $biasMeta) {
            $bias = isset($biasMeta[$post->bias_rating ?? '']) ? $post->bias_rating : 'unknown';
            $source = $post->source_id && isset($sources[$post->source_id]) ? $sources[$post->source_id] : null;
            $excerpt = trim(strip_tags((string) (GnTr::description($post) ?: $post->description ?: $post->name)));
            $country = $source->country ?? $post->country ?? null;
            $originKey = GrimbaSourceBreakdown::originKeyForCountry($country);

            return [
                'id' => (int) $post->id,
                'bias' => $bias,
                'source' => $post->source_name ?: ($source->name ?? __('Source inconnue')),
                'title' => GnTr::title($post),
                'excerpt' => Str::limit($excerpt, 170),
                'country' => GrimbaSourceBreakdown::countryLabel($country),
                'origin' => GrimbaSourceBreakdown::originLabel($originKey),
                'origin_color' => GrimbaSourceBreakdown::originColor($originKey),
                'published' => $post->created_at ? $post->created_at->locale(app()->getLocale())->diffForHumans() : null,
            ];
        })
        ->sortBy(fn ($row) => match ($row['bias']) {
            'left' => 1,
            'center' => 2,
            'right' => 3,
            default => 4,
        })
        ->values();
@endphp

@if($rows->isNotEmpty())
    <section class="grimba-source-drilldown glass-panel p-3 p-md-4 mb-3" aria-labelledby="grimba-source-drilldown-title">
        <div class="grimba-source-drilldown__header">
            <div>
                <span class="grimba-methodology__kicker">{{ __('Provenance des sources') }}</span>
                <h2 id="grimba-source-drilldown-title" class="grimba-source-drilldown__title">
                    {{ __('Matrice des angles') }}
                </h2>
                <p class="grimba-source-drilldown__lede">
                    {{ __('Chaque source est reliée à son pays d’origine, son camp éditorial et l’extrait qui soutient le cadrage.') }}
                </p>
            </div>
            <a href="#grimba-cluster-panel" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                {{ __('Voir le dossier') }}
            </a>
        </div>

        <div class="grimba-source-drilldown__signals" aria-label="{{ __('Résumé de provenance') }}">
            <div class="grimba-source-drilldown__signal">
                <span>{{ __('Sources') }}</span>
                <strong>{{ $sourceCount ?: $rows->count() }}</strong>
            </div>
            @if($dominantBias)
                <div class="grimba-source-drilldown__signal">
                    <span>{{ __('Camp dominant') }}</span>
                    <strong>{{ $dominantBias->label }} · {{ $dominantBiasPct }}%</strong>
                </div>
            @endif
            <div class="grimba-source-drilldown__signal">
                <span>{{ __('Équilibre') }}</span>
                <strong>{{ $biasBalanceScore }}%</strong>
            </div>
            @if($topOrigin)
                <div class="grimba-source-drilldown__signal">
                    <span>{{ __('Origine dominante') }}</span>
                    <strong>{{ $topOrigin->label }}</strong>
                </div>
            @endif
        </div>

        @if($countryBiasBuckets->isNotEmpty())
            <div class="grimba-source-drilldown__countries">
                @foreach($countryBiasBuckets as $country)
                    <article class="grimba-source-drilldown__country" style="--origin-color: {{ $country->color }};">
                        <div>
                            <span class="grimba-source-drilldown__country-origin">{{ $country->origin_label }}</span>
                            <strong>{{ $country->label }}</strong>
                        </div>
                        <span class="grimba-source-drilldown__country-count">
                            {{ trans_choice(':count source|:count sources', $country->count, ['count' => $country->count]) }}
                        </span>
                        <div class="grimba-source-drilldown__country-bar" aria-hidden="true">
                            @foreach(['left', 'center', 'right'] as $key)
                                @php $bucket = $country->bias->get($key); @endphp
                                @if($bucket && $bucket->pct > 0)
                                    <span style="--bar-color: {{ $bucket->color }}; --bar-size: {{ $bucket->pct }}%;"></span>
                                @endif
                            @endforeach
                        </div>
                        <small>{{ __('Dominant') }}: {{ $country->dominant_bias }} · {{ $country->dominant_pct }}%</small>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="grimba-source-drilldown__bias-strip">
            @foreach($knownBiasBuckets as $bucket)
                @continue($bucket->count <= 0)
                <span style="--bias-color: {{ $bucket->color }};">
                    {{ $bucket->label }} · {{ $bucket->count }}
                </span>
            @endforeach
        </div>

        <div class="grimba-source-drilldown__grid">
            @foreach($rows as $row)
                @php $meta = $biasMeta[$row['bias']] ?? $biasMeta['unknown']; @endphp
                <article class="grimba-source-drilldown__row" style="--source-bias-color: {{ $meta['color'] }};">
                    <div class="grimba-source-drilldown__axis" aria-hidden="true">
                        <span>{{ $meta['short'] }}</span>
                    </div>
                    <div class="grimba-source-drilldown__body">
                        <div class="grimba-source-drilldown__row-top">
                            <strong>{{ $row['source'] }}</strong>
                            <span class="grimba-source-drilldown__bias">
                                <span></span>
                                {{ $meta['label'] }}
                            </span>
                        </div>
                        <h3>{{ $row['title'] }}</h3>
                        <p>{{ $row['excerpt'] }}</p>
                        <div class="grimba-source-drilldown__meta">
                            <span class="grimba-source-drilldown__origin" style="--origin-color: {{ $row['origin_color'] }};">
                                {{ $row['origin'] }} · {{ $row['country'] }}
                            </span>
                            @if($row['published'])
                                <span>{{ $row['published'] }}</span>
                            @endif
                            <a href="#story-article-{{ $row['id'] }}">{{ __('Ouvrir dans le dossier') }}</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
