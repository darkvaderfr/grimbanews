@php
    /**
     * S148 — Coverage Details panel (right sidebar on the story
     * page). Mirrors the GroundNews "Coverage Details" block.
     *
     * @var \Illuminate\Database\Eloquent\Collection $clusterPosts  All posts in the same cluster
     * @var int $clusterId
     */
    $counts = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
    $latestUpdated = null;
    foreach ($clusterPosts as $cp) {
        $r = $cp->bias_rating ?? 'unknown';
        if (! isset($counts[$r])) $r = 'unknown';
        $counts[$r]++;
        if ($cp->updated_at && (! $latestUpdated || $cp->updated_at->gt($latestUpdated))) {
            $latestUpdated = $cp->updated_at;
        }
    }
    $total = array_sum($counts);
    $known = $counts['left'] + $counts['center'] + $counts['right'];
    $dominantPct = $known > 0
        ? max($counts['left'], $counts['center'], $counts['right']) * 100 / $known
        : 0;
    $dominantBias = $known > 0
        ? array_keys($counts, max($counts['left'], $counts['center'], $counts['right']))[0]
        : null;
    $dominantLabel = match ($dominantBias) {
        'left'   => 'Gauche',
        'center' => 'Centre',
        'right'  => 'Droite',
        default  => '—',
    };
@endphp

<aside class="grimba-story-coverage glass-panel p-3 mb-3">
    <h2 class="h6 mb-3" style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; font-size:13px; opacity:0.75;">
        Détails de la couverture
    </h2>

    <dl class="m-0" style="display:grid; grid-template-columns:1fr auto; gap:6px 16px; font-size:14px;">
        <dt class="opacity-75">Sources totales</dt>
        <dd class="m-0 fw-semibold">{{ $total }}</dd>

        <dt class="opacity-75">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#3b82f6; margin-right:6px;"></span>
            Gauche
        </dt>
        <dd class="m-0 fw-semibold">{{ $counts['left'] }}</dd>

        <dt class="opacity-75">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#a8a8a8; margin-right:6px;"></span>
            Centre
        </dt>
        <dd class="m-0 fw-semibold">{{ $counts['center'] }}</dd>

        <dt class="opacity-75">
            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#e84c3d; margin-right:6px;"></span>
            Droite
        </dt>
        <dd class="m-0 fw-semibold">{{ $counts['right'] }}</dd>

        @if($counts['unknown'] > 0)
            <dt class="opacity-50 small">Non classé</dt>
            <dd class="m-0 small opacity-50">{{ $counts['unknown'] }}</dd>
        @endif

        <dt class="opacity-75">Dernière mise à jour</dt>
        <dd class="m-0 fw-semibold small">
            {{ $latestUpdated ? $latestUpdated->locale('fr')->diffForHumans() : '—' }}
        </dd>

        @if($known > 0)
            <dt class="opacity-75">Distribution</dt>
            <dd class="m-0 fw-semibold">
                {{ round($dominantPct) }}%&nbsp;{{ $dominantLabel }}
            </dd>
        @endif
    </dl>
</aside>
