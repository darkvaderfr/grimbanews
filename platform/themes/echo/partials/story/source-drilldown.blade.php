@php
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Illuminate\Support\Str;

    /**
     * S226 — compact source-side drilldown. This lives below the hero
     * so the summary stays clean while readers can still inspect which
     * outlets support each angle.
     *
     * @var \Illuminate\Support\Collection $clusterPosts
     * @var \Illuminate\Support\Collection|null $sourceMeta
     */
    $biasMeta = [
        'left' => ['label' => __('Gauche'), 'color' => '#3b82f6'],
        'center' => ['label' => __('Centre'), 'color' => '#8a8a8a'],
        'right' => ['label' => __('Droite'), 'color' => '#e84c3d'],
        'unknown' => ['label' => __('Non classé'), 'color' => '#6b6459'],
    ];

    $sources = $sourceMeta ?? collect();
    $rows = collect($clusterPosts ?? [])
        ->map(function ($post) use ($sources, $biasMeta) {
            $bias = isset($biasMeta[$post->bias_rating ?? '']) ? $post->bias_rating : 'unknown';
            $source = $post->source_id && isset($sources[$post->source_id]) ? $sources[$post->source_id] : null;
            $excerpt = trim(strip_tags((string) (GnTr::description($post) ?: $post->description ?: $post->name)));
            $country = $source->country ?? $post->country ?? null;
            $originKey = \App\Support\GrimbaSourceBreakdown::originKeyForCountry($country);

            return [
                'id' => (int) $post->id,
                'bias' => $bias,
                'source' => $post->source_name ?: ($source->name ?? __('Source inconnue')),
                'title' => GnTr::title($post),
                'excerpt' => Str::limit($excerpt, 145),
                'owner' => $source->owner_name ?? null,
                'credibility' => $source->credibility_score ?? null,
                'country' => \App\Support\GrimbaSourceBreakdown::countryLabel($country),
                'origin' => \App\Support\GrimbaSourceBreakdown::originLabel($originKey),
                'origin_color' => \App\Support\GrimbaSourceBreakdown::originColor($originKey),
                'url' => $post->url ?? '#story-article-' . (int) $post->id,
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
    <section class="grimba-source-drilldown glass-panel p-3 p-md-4 mb-3">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
            <div>
                <span class="grimba-methodology__kicker">{{ __('Source drilldown') }}</span>
                <h2 class="h4 mt-2 mb-1" style="font-family:'Fraunces','Playfair Display',Georgia,serif; letter-spacing:0;">
                    {{ __('Qui soutient quel angle ?') }}
                </h2>
                <p class="small opacity-70 mb-0">
                    {{ __('Chaque ligne relie un camp, une source et le passage qui justifie le cadrage.') }}
                </p>
            </div>
            <a href="#grimba-cluster-panel" class="btn-grimba btn-grimba--ghost btn-grimba--sm">
                {{ __('Voir tous les articles') }}
            </a>
        </div>

        <div class="grimba-source-drilldown__grid">
            @foreach($rows as $row)
                @php $meta = $biasMeta[$row['bias']] ?? $biasMeta['unknown']; @endphp
                <article class="grimba-source-drilldown__row" style="--source-bias-color: {{ $meta['color'] }};">
                    <div class="grimba-source-drilldown__bias">
                        <span></span>
                        {{ $meta['label'] }}
                    </div>
                    <div class="grimba-source-drilldown__body">
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <strong>{{ $row['source'] }}</strong>
                            <a href="#story-article-{{ $row['id'] }}" class="small">
                                {{ __('Voir dans le dossier') }} ↓
                            </a>
                        </div>
                        <p class="mb-1">{{ $row['excerpt'] }}</p>
                        <div class="grimba-source-drilldown__meta">
                            @if($row['credibility'])
                                <span>{{ __('Crédibilité') }} {{ $row['credibility'] }}</span>
                            @endif
                            <span class="grimba-source-drilldown__origin" style="--origin-color: {{ $row['origin_color'] }};">
                                {{ __('Origine') }}: {{ $row['origin'] }} · {{ $row['country'] }}
                            </span>
                            @if($row['owner'])
                                <span>{{ __('Propriété') }}: {{ $row['owner'] }}</span>
                            @endif
                            <a href="{{ $row['url'] }}">{{ __('Lire cette source') }} ↗</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <style>
        .grimba-source-drilldown__grid {
            display: grid;
            gap: 10px;
        }
        .grimba-source-drilldown__row {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 12px;
            padding: 12px;
            border: 1px solid rgba(26, 23, 19, 0.1);
            border-left: 4px solid var(--source-bias-color);
            border-radius: 14px;
            background:
                linear-gradient(135deg, color-mix(in srgb, var(--source-bias-color) 8%, transparent), transparent 48%),
                rgba(255, 255, 255, 0.58);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .10);
        }
        .grimba-source-drilldown__bias {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--source-bias-color);
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }
        .grimba-source-drilldown__bias span {
            width: 9px;
            height: 9px;
            border-radius: 9999px;
            background: var(--source-bias-color);
        }
        .grimba-source-drilldown__body strong {
            color: var(--gn-ink, #1a1713);
            font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
            font-size: 17px;
        }
        .grimba-source-drilldown__body p {
            color: var(--gn-ink, #1a1713);
            font-size: 14px;
            line-height: 1.5;
            opacity: .86;
        }
        .grimba-source-drilldown__body a,
        .grimba-source-drilldown__meta a {
            color: #c0392b;
            font-weight: 750;
            text-decoration: none;
        }
        .grimba-source-drilldown__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 12px;
            color: var(--gn-ink-soft, #6b6459);
            font-size: 12px;
        }
        .grimba-source-drilldown__origin {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .grimba-source-drilldown__origin::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--origin-color);
            box-shadow: 0 0 0 5px color-mix(in srgb, var(--origin-color) 13%, transparent);
        }
        html[data-bs-theme="dark"] .grimba-source-drilldown__row,
        body[data-theme="dark"] .grimba-source-drilldown__row {
            background:
                linear-gradient(135deg, color-mix(in srgb, var(--source-bias-color) 13%, transparent), transparent 50%),
                rgba(246, 241, 232, 0.08);
            border-color: rgba(246, 241, 232, 0.14);
        }
        @media (max-width: 575.98px) {
            .grimba-source-drilldown__row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endif
