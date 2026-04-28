@php
    /**
     * GroundNews-style story breakdown.
     *
     * Computes coverage from the actual posts/sources inside a story
     * cluster: political bias, source factuality, and ownership.
     *
     * @var \Illuminate\Support\Collection $posts
     */

    $uid = 'gbd-' . substr(md5((string) ($posts->pluck('id')->join('-') ?: uniqid('', true))), 0, 10);

    $sourceIds = $posts->pluck('source_id')->filter()->unique()->values();
    $sourceRows = $sourceIds->isEmpty()
        ? collect()
        : \Illuminate\Support\Facades\DB::table('news_sources')
            ->whereIn('id', $sourceIds)
            ->get(['id', 'name', 'website', 'bias_rating', 'ownership_type', 'credibility_score', 'owner_name']);

    $sourcesById = $sourceRows->keyBy('id');
    $fallbackByName = \Illuminate\Support\Facades\DB::table('news_sources')
        ->whereIn('name', $posts->pluck('source_name')->filter()->unique()->values())
        ->get(['id', 'name', 'website', 'bias_rating', 'ownership_type', 'credibility_score', 'owner_name'])
        ->keyBy(fn ($row) => \Illuminate\Support\Str::lower((string) $row->name));

    $sources = $posts
        ->map(function ($post) use ($sourcesById, $fallbackByName) {
            $meta = $post->source_id ? $sourcesById->get($post->source_id) : null;
            $meta ??= $fallbackByName->get(\Illuminate\Support\Str::lower((string) $post->source_name));

            return (object) [
                'key' => $post->source_id ?: \Illuminate\Support\Str::lower((string) ($post->source_name ?: $post->id)),
                'name' => (string) ($meta->name ?? $post->source_name ?? __('Source inconnue')),
                'website' => (string) ($meta->website ?? ''),
                'bias' => (string) ($meta->bias_rating ?? $post->bias_rating ?? 'unknown'),
                'credibility' => $meta->credibility_score ?? $post->credibility_score ?? null,
                'ownership' => (string) ($meta->ownership_type ?? $post->ownership_type ?? 'unknown'),
                'owner' => (string) ($meta->owner_name ?? ''),
            ];
        })
        ->unique('key')
        ->values();

    $total = max(1, $sources->count());

    $biasConfig = [
        'left' => ['label' => __('Gauche'), 'color' => '#3b82f6'],
        'center' => ['label' => __('Centre'), 'color' => '#9ca3af'],
        'right' => ['label' => __('Droite'), 'color' => '#ef4444'],
        'unknown' => ['label' => __('Non classé'), 'color' => '#6b7280'],
    ];

    $biasBuckets = collect($biasConfig)->map(function ($meta, $key) use ($sources) {
        $items = $sources->filter(fn ($source) => ($source->bias ?: 'unknown') === $key)->values();

        return (object) [
            'key' => $key,
            'label' => $meta['label'],
            'color' => $meta['color'],
            'items' => $items,
            'count' => $items->count(),
        ];
    })->values();

    $knownBiasBuckets = $biasBuckets->filter(fn ($bucket) => in_array($bucket->key, ['left', 'center', 'right'], true));
    $weakestBias = $knownBiasBuckets->sortBy('count')->first();
    $weakestPct = $weakestBias ? (int) round($weakestBias->count * 100 / $total) : 0;

    $factBuckets = collect([
        'very-high' => (object) ['label' => __('Très factuel'), 'range' => __('85-100'), 'color' => '#16a34a', 'items' => collect()],
        'high' => (object) ['label' => __('Factuel'), 'range' => __('70-84'), 'color' => '#22c55e', 'items' => collect()],
        'mixed' => (object) ['label' => __('À vérifier'), 'range' => __('50-69'), 'color' => '#d97706', 'items' => collect()],
        'low' => (object) ['label' => __('Faible'), 'range' => __('< 50'), 'color' => '#dc2626', 'items' => collect()],
        'unknown' => (object) ['label' => __('Non coté'), 'range' => __('N/A'), 'color' => '#64748b', 'items' => collect()],
    ]);

    foreach ($sources as $source) {
        $score = is_numeric($source->credibility) ? (int) $source->credibility : null;
        $bucket = match (true) {
            $score === null => 'unknown',
            $score >= 85 => 'very-high',
            $score >= 70 => 'high',
            $score >= 50 => 'mixed',
            default => 'low',
        };
        $factBuckets[$bucket]->items->push($source);
    }

    $ownershipLabel = function (string $ownership): string {
        $normalized = \Illuminate\Support\Str::of($ownership)->lower()->replace(['_', '-'], ' ')->squish()->toString();

        return match (true) {
            str_contains($normalized, 'government') || str_contains($normalized, 'state') || str_contains($normalized, 'public') => __('Gouvernement / public'),
            str_contains($normalized, 'independent') => __('Indépendant'),
            str_contains($normalized, 'individual') || str_contains($normalized, 'family') => __('Individuel / familial'),
            str_contains($normalized, 'private') || str_contains($normalized, 'equity') => __('Private equity'),
            str_contains($normalized, 'conglomerate') || str_contains($normalized, 'corporate') || str_contains($normalized, 'company') => __('Conglomérat média'),
            $normalized === '' || $normalized === 'unknown' => __('Non classé'),
            default => \Illuminate\Support\Str::headline($ownership),
        };
    };

    $ownershipColors = ['#111827', '#2085c7', '#6254b2', '#174f47', '#d12854', '#ca9700', '#64748b', '#7c3aed'];
    $ownershipBuckets = $sources
        ->groupBy(fn ($source) => $ownershipLabel($source->ownership))
        ->map(function ($items, $label) use (&$ownershipColors) {
            return (object) [
                'label' => $label,
                'color' => array_shift($ownershipColors) ?: '#64748b',
                'items' => $items->values(),
                'count' => $items->count(),
            ];
        })
        ->sortByDesc('count')
        ->values();

    $donutStops = [];
    $cursor = 0;
    foreach ($ownershipBuckets as $bucket) {
        $slice = $bucket->count * 100 / $total;
        $gap = min(1.8, max(0.45, $slice * 0.08));
        $start = $cursor;
        $colorStart = min(100, $cursor + ($slice > 3 ? $gap / 2 : 0));
        $colorEnd = max($colorStart, min(100, $cursor + $slice - ($slice > 3 ? $gap / 2 : 0)));
        $end = min(100, $cursor + $slice);

        if ($colorStart > $start) {
            $donutStops[] = "transparent {$start}% {$colorStart}%";
        }
        $donutStops[] = "{$bucket->color} {$colorStart}% {$colorEnd}%";
        if ($end > $colorEnd) {
            $donutStops[] = "transparent {$colorEnd}% {$end}%";
        }
        $cursor += $slice;
    }
    $donutGradient = $donutStops ? implode(', ', $donutStops) : '#e5e7eb 0% 100%';
    $topOwner = $ownershipBuckets->first();
    $topOwnerPct = $topOwner ? (int) round($topOwner->count * 100 / $total) : 0;
@endphp

<section class="grimba-breakdown glass-panel p-3 p-md-4 mb-4" id="{{ $uid }}">
    <style>
        #{{ $uid }} {
            --gbd-ink: var(--gn-ink, #171717);
            --gbd-muted: rgba(23, 23, 23, .64);
            --gbd-line: rgba(23, 23, 23, .12);
            --gbd-paper: rgba(255, 255, 255, .86);
            --gbd-surface: rgba(255, 255, 255, .62);
            --gbd-shadow: 0 24px 70px rgba(22, 18, 12, .10);
            color: var(--gbd-ink);
            overflow: hidden;
        }

        [data-bs-theme="dark"] #{{ $uid }} {
            --gbd-ink: #f8f3ea;
            --gbd-muted: rgba(248, 243, 234, .72);
            --gbd-line: rgba(248, 243, 234, .16);
            --gbd-paper: rgba(15, 14, 11, .88);
            --gbd-surface: rgba(24, 22, 17, .78);
            --gbd-shadow: 0 24px 70px rgba(0, 0, 0, .36);
        }

        #{{ $uid }}::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 18% 8%, rgba(59, 130, 246, .12), transparent 28%),
                radial-gradient(circle at 84% 18%, rgba(209, 40, 84, .10), transparent 32%),
                linear-gradient(135deg, rgba(255, 255, 255, .18), transparent 42%);
        }

        #{{ $uid }} > * {
            position: relative;
            z-index: 1;
        }

        @keyframes gbd-panel-in {
            from { opacity: 0; transform: translateY(10px) scale(.985); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes gbd-rise {
            from { opacity: 0; transform: translateY(16px) scale(.94); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes gbd-fill {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }

        @keyframes gbd-donut {
            from { opacity: 0; transform: rotate(-24deg) scale(.88); filter: saturate(.75); }
            to { opacity: 1; transform: rotate(-90deg) scale(1); filter: saturate(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            #{{ $uid }} *,
            #{{ $uid }} *::before,
            #{{ $uid }} *::after {
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .001ms !important;
            }
        }

        #{{ $uid }} .grimba-breakdown__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 12px;
        }

        #{{ $uid }} .grimba-breakdown__title {
            margin: 0;
            font: 700 clamp(22px, 2.4vw, 30px)/1.05 "Fraunces", Georgia, serif;
            letter-spacing: -.02em;
        }

        #{{ $uid }} .grimba-breakdown__tabs {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            position: relative;
            padding: 4px;
            border: 1px solid var(--gbd-line);
            border-radius: 18px;
            background: linear-gradient(180deg, var(--gbd-paper), var(--gbd-surface));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18), var(--gbd-shadow);
            overflow: hidden;
        }

        #{{ $uid }} .grimba-breakdown__tabs::before {
            content: "";
            position: absolute;
            top: 7px;
            bottom: 7px;
            left: 7px;
            width: calc((100% - 14px) / 3);
            border-radius: 14px;
            background: #15130f;
            box-shadow: 0 16px 34px rgba(0, 0, 0, .22);
            transform: translateX(var(--tab-x, 0));
            transition: transform .24s cubic-bezier(.2,.8,.2,1);
        }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs { --tab-x: 0; }
        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs { --tab-x: 100%; }
        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs { --tab-x: 200%; }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #111827, #2f6fe9);
        }

        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #111827, #18a058);
        }

        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs::before {
            background: linear-gradient(135deg, #111827, #a06a00);
        }

        #{{ $uid }} input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        #{{ $uid }} .grimba-breakdown__tab {
            margin: 0;
            padding: 9px 12px;
            border-radius: 14px;
            text-align: center;
            font-weight: 800;
            color: var(--gbd-muted);
            cursor: pointer;
            position: relative;
            z-index: 1;
            transition: background .16s ease, color .16s ease, box-shadow .16s ease;
        }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-bias"],
        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-fact"],
        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__tabs label[for="{{ $uid }}-owner"] {
            color: #fff;
            text-shadow: 0 1px 10px rgba(0, 0, 0, .42);
        }

        #{{ $uid }} .grimba-breakdown__panel {
            display: none;
            padding-top: 16px;
        }

        #{{ $uid }} #{{ $uid }}-bias:checked ~ .grimba-breakdown__panels [data-panel="bias"],
        #{{ $uid }} #{{ $uid }}-fact:checked ~ .grimba-breakdown__panels [data-panel="fact"],
        #{{ $uid }} #{{ $uid }}-owner:checked ~ .grimba-breakdown__panels [data-panel="owner"] {
            display: block;
            animation: gbd-panel-in .26s ease both;
        }

        #{{ $uid }} .grimba-breakdown__callout {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            color: var(--gbd-muted);
            font-size: clamp(16px, 1.6vw, 20px);
            line-height: 1.25;
        }

        #{{ $uid }} .grimba-breakdown__callout strong {
            color: var(--gbd-ink);
        }

        #{{ $uid }} .grimba-breakdown__icon {
            display: inline-flex;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #15130f;
            color: #fff;
            flex: 0 0 auto;
            box-shadow: 0 14px 28px rgba(0, 0, 0, .18);
        }

        #{{ $uid }} .grimba-breakdown__bias-lanes {
            display: grid;
            grid-template-columns: repeat(4, minmax(76px, 1fr));
            gap: 12px;
            align-items: end;
            margin: 14px 0;
        }

        #{{ $uid }} .grimba-breakdown__lane {
            min-height: 156px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
            padding: 10px 8px;
            border: 1px solid var(--gbd-line);
            border-radius: 999px;
            background:
                linear-gradient(180deg, color-mix(in srgb, var(--lane-color) 14%, transparent), transparent),
                linear-gradient(180deg, var(--gbd-surface), transparent);
            box-shadow: inset 0 -18px 34px color-mix(in srgb, var(--lane-color) 12%, transparent);
        }

        #{{ $uid }} .grimba-breakdown__logo-pop {
            display: inline-flex;
            animation: gbd-rise .34s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--delay, 0ms);
        }

        #{{ $uid }} .grimba-breakdown__more {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1px solid var(--gbd-line);
            background: var(--gbd-paper);
            color: var(--gbd-muted);
            font-weight: 800;
        }

        #{{ $uid }} .grimba-breakdown__bias-bar {
            display: flex;
            height: 36px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(127, 127, 127, .14);
            box-shadow: inset 0 0 0 1px var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__bias-bar span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: var(--w);
            color: #fff;
            font-weight: 900;
            font-size: 13px;
            text-shadow: 0 1px 8px rgba(0, 0, 0, .34);
            transform-origin: left;
            animation: gbd-fill .7s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--delay, 0ms);
        }

        #{{ $uid }} .grimba-breakdown__rows {
            display: grid;
            gap: 10px;
        }

        #{{ $uid }} .grimba-breakdown__row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 14px;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__legend {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            color: var(--gbd-muted);
            font-weight: 700;
        }

        #{{ $uid }} .grimba-breakdown__dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--dot);
            flex: 0 0 auto;
            box-shadow: 0 0 0 6px color-mix(in srgb, var(--dot) 12%, transparent);
        }

        #{{ $uid }} .grimba-breakdown__logos {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 6px;
        }

        #{{ $uid }} .grimba-breakdown__metric {
            display: grid;
            grid-template-columns: minmax(110px, 220px) auto;
            gap: 10px;
            align-items: center;
        }

        #{{ $uid }} .grimba-breakdown__mini-track {
            height: 9px;
            border-radius: 999px;
            background: rgba(127, 127, 127, .14);
            overflow: hidden;
            box-shadow: inset 0 0 0 1px var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__mini-fill {
            display: block;
            width: var(--w);
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, color-mix(in srgb, var(--dot) 62%, #fff), var(--dot));
            transform-origin: left;
            animation: gbd-fill .72s cubic-bezier(.2,.8,.2,1) both;
            animation-delay: var(--delay, 0ms);
        }

        #{{ $uid }} .grimba-breakdown__donut {
            width: min(300px, 100%);
            aspect-ratio: 1;
            margin: 0 auto;
            border-radius: 50%;
            background: conic-gradient({{ $donutGradient }});
            position: relative;
            transform: rotate(-90deg);
            animation: gbd-donut .72s cubic-bezier(.2,.8,.2,1) both;
            box-shadow:
                inset 0 0 0 18px rgba(255, 255, 255, .9),
                0 0 0 1px var(--gbd-line),
                0 24px 60px rgba(0, 0, 0, .16);
        }

        [data-bs-theme="dark"] #{{ $uid }} .grimba-breakdown__donut {
            box-shadow: inset 0 0 0 18px rgba(15, 14, 11, .9), 0 22px 48px rgba(0, 0, 0, .42);
        }

        #{{ $uid }} .grimba-breakdown__donut::after {
            content: "";
            position: absolute;
            inset: 31%;
            border-radius: 50%;
            background: var(--gbd-paper);
            box-shadow: 0 0 0 1px var(--gbd-line);
        }

        #{{ $uid }} .grimba-breakdown__donut-center {
            position: absolute;
            inset: 34%;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--gbd-ink);
            transform: rotate(90deg);
        }

        #{{ $uid }} .grimba-breakdown__donut-center strong {
            display: block;
            font-size: clamp(30px, 5vw, 48px);
            line-height: 1;
        }

        #{{ $uid }} .grimba-breakdown__owner-grid {
            display: grid;
            grid-template-columns: minmax(220px, 320px) 1fr;
            gap: 24px;
            align-items: center;
        }

        #{{ $uid }} .grimba-breakdown__insight-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 16px;
        }

        #{{ $uid }} .grimba-breakdown__stat {
            padding: 12px;
            border: 1px solid var(--gbd-line);
            border-radius: 18px;
            background: var(--gbd-surface);
        }

        #{{ $uid }} .grimba-breakdown__stat strong {
            display: block;
            font: 800 24px/1 "Fraunces", Georgia, serif;
        }

        #{{ $uid }} .grimba-breakdown__stat span {
            color: var(--gbd-muted);
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            #{{ $uid }} .grimba-breakdown__top {
                align-items: flex-start;
                flex-direction: column;
            }

            #{{ $uid }} .grimba-breakdown__bias-lanes {
                grid-template-columns: repeat(2, 1fr);
            }

            #{{ $uid }} .grimba-breakdown__lane {
                min-height: 168px;
            }

            #{{ $uid }} .grimba-breakdown__donut {
                width: min(320px, 100%);
            }

            #{{ $uid }} .grimba-breakdown__row,
            #{{ $uid }} .grimba-breakdown__metric {
                grid-template-columns: 1fr;
            }

            #{{ $uid }} .grimba-breakdown__owner-grid {
                grid-template-columns: 1fr;
            }

            #{{ $uid }} .grimba-breakdown__logos {
                justify-content: flex-start;
            }

            #{{ $uid }} .grimba-breakdown__insight-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="grimba-breakdown__top">
        <div>
            <span class="grimba-methodology__kicker">{{ __('Analyse des sources') }}</span>
            <h2 class="grimba-breakdown__title">{{ __('Breakdown') }}</h2>
        </div>
        <span class="small opacity-75">
            {{ trans_choice(':count source analysée|:count sources analysées', $sources->count(), ['count' => $sources->count()]) }}
        </span>
    </div>

    <input type="radio" id="{{ $uid }}-bias" name="{{ $uid }}-tab" checked>
    <input type="radio" id="{{ $uid }}-fact" name="{{ $uid }}-tab">
    <input type="radio" id="{{ $uid }}-owner" name="{{ $uid }}-tab">

    <div class="grimba-breakdown__tabs" role="tablist" aria-label="{{ __('Analyse du dossier') }}">
        <label class="grimba-breakdown__tab" for="{{ $uid }}-bias" role="tab">{{ __('Biais') }}</label>
        <label class="grimba-breakdown__tab" for="{{ $uid }}-fact" role="tab">{{ __('Factualité') }}</label>
        <label class="grimba-breakdown__tab" for="{{ $uid }}-owner" role="tab">{{ __('Propriété') }}</label>
    </div>

    <div class="grimba-breakdown__panels">
        <div class="grimba-breakdown__panel" data-panel="bias">
            <div class="grimba-breakdown__callout">
                <span class="grimba-breakdown__icon">◎</span>
                <span>
                    {{ __('Cette histoire n’a que') }}
                    <strong>{{ $weakestPct }}% {{ $weakestBias?->label }}</strong>
                    {{ __('de couverture politique.') }}
                </span>
            </div>

            <div class="grimba-breakdown__bias-lanes">
                @foreach($biasBuckets as $bucket)
                    <div class="grimba-breakdown__lane" style="--lane-color: {{ $bucket->color }};">
                        @foreach($bucket->items->take(5) as $source)
                            <span class="grimba-breakdown__logo-pop" style="--delay: {{ $loop->index * 55 }}ms;">
                                {!! Theme::partial('source-logo', [
                                    'name' => $source->name,
                                    'website' => $source->website,
                                    'size' => 44,
                                    'color' => $bucket->color,
                                ]) !!}
                            </span>
                        @endforeach
                        @if($bucket->count > 5)
                            <span class="grimba-breakdown__more">+{{ $bucket->count - 5 }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="grimba-breakdown__bias-bar">
                <span style="--w: {{ max(1, (int) round(($biasBuckets->firstWhere('key', 'left')?->count ?? 0) * 100 / $total)) }}%; --delay: 60ms; background:#3b82f6;">L {{ (int) round(($biasBuckets->firstWhere('key', 'left')?->count ?? 0) * 100 / $total) }}%</span>
                <span style="--w: {{ max(1, (int) round(($biasBuckets->firstWhere('key', 'center')?->count ?? 0) * 100 / $total)) }}%; --delay: 140ms; background:#9ca3af;">C {{ (int) round(($biasBuckets->firstWhere('key', 'center')?->count ?? 0) * 100 / $total) }}%</span>
                <span style="--w: {{ max(1, (int) round(($biasBuckets->firstWhere('key', 'right')?->count ?? 0) * 100 / $total)) }}%; --delay: 220ms; background:#ef4444;">R {{ (int) round(($biasBuckets->firstWhere('key', 'right')?->count ?? 0) * 100 / $total) }}%</span>
            </div>

            <div class="grimba-breakdown__insight-grid">
                @foreach($knownBiasBuckets as $bucket)
                    @php($pct = (int) round($bucket->count * 100 / $total))
                    <div class="grimba-breakdown__stat">
                        <strong style="color: {{ $bucket->color }};">{{ $pct }}%</strong>
                        <span>{{ $bucket->label }} · {{ trans_choice(':count source|:count sources', $bucket->count, ['count' => $bucket->count]) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grimba-breakdown__panel" data-panel="fact">
            <div class="grimba-breakdown__callout">
                <span class="grimba-breakdown__icon">✓</span>
                <span>{{ __('Factualité estimée depuis le score de crédibilité de chaque source.') }}</span>
            </div>

            <div class="grimba-breakdown__rows">
                @foreach($factBuckets as $bucket)
                    @php($pct = (int) round($bucket->items->count() * 100 / $total))
                    <div class="grimba-breakdown__row" style="--dot: {{ $bucket->color }};">
                        <div class="grimba-breakdown__legend">
                            <span class="grimba-breakdown__dot" style="--dot: {{ $bucket->color }};"></span>
                            <span>{{ $bucket->label }}</span>
                            <span class="opacity-65">{{ $bucket->range }}</span>
                        </div>
                        <div class="grimba-breakdown__metric">
                            <div class="grimba-breakdown__mini-track">
                                <span class="grimba-breakdown__mini-fill" style="--dot: {{ $bucket->color }}; --w: {{ $pct }}%; --delay: {{ $loop->index * 70 }}ms;"></span>
                            </div>
                            <strong>{{ $pct }}%</strong>
                            <div class="grimba-breakdown__logos">
                                @foreach($bucket->items->take(6) as $source)
                                    {!! Theme::partial('source-logo', [
                                        'name' => $source->name,
                                        'website' => $source->website,
                                        'size' => 30,
                                        'color' => $bucket->color,
                                    ]) !!}
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grimba-breakdown__panel" data-panel="owner">
            <div class="grimba-breakdown__owner-grid">
                <div class="grimba-breakdown__donut" aria-label="{{ __('Répartition par type de propriété') }}">
                    <div class="grimba-breakdown__donut-center">
                        <strong>{{ $topOwnerPct }}%</strong>
                        <span>{{ $topOwner?->label ?? __('Non classé') }}</span>
                    </div>
                </div>

                <div class="grimba-breakdown__rows">
                    @foreach($ownershipBuckets as $bucket)
                        @php($pct = (int) round($bucket->count * 100 / $total))
                        <div class="grimba-breakdown__row" style="--dot: {{ $bucket->color }};">
                            <div class="grimba-breakdown__legend">
                                <span class="grimba-breakdown__dot" style="--dot: {{ $bucket->color }};"></span>
                                <span>{{ $bucket->label }}</span>
                            </div>
                            <div class="grimba-breakdown__metric">
                                <div class="grimba-breakdown__mini-track">
                                    <span class="grimba-breakdown__mini-fill" style="--dot: {{ $bucket->color }}; --w: {{ $pct }}%; --delay: {{ $loop->index * 70 }}ms;"></span>
                                </div>
                                <strong>{{ $pct }}%</strong>
                                <div class="grimba-breakdown__logos">
                                    @foreach($bucket->items->take(6) as $source)
                                        {!! Theme::partial('source-logo', [
                                            'name' => $source->name,
                                            'website' => $source->website,
                                            'size' => 30,
                                            'color' => $bucket->color,
                                        ]) !!}
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
