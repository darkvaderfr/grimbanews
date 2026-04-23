@php
    /**
     * Source Diversity Meter — GroundNews-inspired
     *
     * Visual balance bar showing Left / Center / Right coverage of a story.
     *
     * @var \Illuminate\Support\Collection $posts  Posts in the cluster
     */

    $counts = [
        'left'   => 0,
        'center' => 0,
        'right'  => 0,
    ];

    foreach ($posts as $clusterPost) {
        $rating = $clusterPost->bias_rating ?? 'unknown';
        if (isset($counts[$rating])) {
            $counts[$rating]++;
        }
    }

    $total = array_sum($counts);
    $pct = [
        'left'   => $total ? round($counts['left']   * 100 / $total) : 0,
        'center' => $total ? round($counts['center'] * 100 / $total) : 0,
        'right'  => $total ? round($counts['right']  * 100 / $total) : 0,
    ];

    $sides = array_filter($counts, fn ($c) => $c > 0);
    $balanceLabel = match (count($sides)) {
        3 => 'Couverture équilibrée',
        2 => 'Couverture partielle',
        1 => 'Couverture unilatérale',
        default => 'Aucune source classée',
    };
@endphp

<div class="diversity-meter glass-panel p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="text-uppercase small">Diversité des sources</strong>
        <span class="small opacity-75">{{ $balanceLabel }} — {{ $total }} {{ $total > 1 ? 'sources' : 'source' }}</span>
    </div>

    <div class="diversity-bar" style="display:flex;height:10px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,0.06);">
        <div style="width: {{ $pct['left'] }}%;background:#3b82f6;" title="Gauche — {{ $counts['left'] }}"></div>
        <div style="width: {{ $pct['center'] }}%;background:#22c55e;" title="Centre — {{ $counts['center'] }}"></div>
        <div style="width: {{ $pct['right'] }}%;background:#ef4444;" title="Droite — {{ $counts['right'] }}"></div>
    </div>

    <div class="d-flex justify-content-between small mt-2">
        <span style="color:#3b82f6;font-weight:600;">● Gauche {{ $pct['left'] }}%</span>
        <span style="color:#22c55e;font-weight:600;">● Centre {{ $pct['center'] }}%</span>
        <span style="color:#ef4444;font-weight:600;">● Droite {{ $pct['right'] }}%</span>
    </div>
</div>
