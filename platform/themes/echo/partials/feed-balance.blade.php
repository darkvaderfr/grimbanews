@php
    /**
     * Feed Balance Meter — live L/C/R share across the current post collection.
     *
     * @var \Illuminate\Support\Collection $posts
     */

    $counts = ['left'=>0,'center'=>0,'right'=>0,'unknown'=>0];
    foreach ($posts as $p) {
        $r = $p->bias_rating ?? 'unknown';
        $counts[$r] = ($counts[$r] ?? 0) + 1;
    }
    $known = $counts['left'] + $counts['center'] + $counts['right'];
    $pct = [
        'left'   => $known ? round($counts['left']   * 100 / $known) : 0,
        'center' => $known ? round($counts['center'] * 100 / $known) : 0,
        'right'  => $known ? round($counts['right']  * 100 / $known) : 0,
    ];

    $active = array_filter(['left','center','right'], fn ($k) => $counts[$k] > 0);
    $label = match (count($active)) {
        3 => 'Fil équilibré',
        2 => 'Fil partiellement équilibré',
        1 => 'Fil unilatéral',
        default => 'En attente de classement',
    };
@endphp

<div class="feed-balance glass-panel p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="text-uppercase small">Équilibre du fil</strong>
        <span class="small opacity-75">{{ $label }}</span>
    </div>
    <div style="display:flex;height:8px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,0.06);">
        <div style="width: {{ $pct['left'] }}%;background:#3b82f6;"></div>
        <div style="width: {{ $pct['center'] }}%;background:#22c55e;"></div>
        <div style="width: {{ $pct['right'] }}%;background:#ef4444;"></div>
    </div>
    <div class="d-flex justify-content-between small mt-2 opacity-85">
        <span>Gauche {{ $counts['left'] }}</span>
        <span>Centre {{ $counts['center'] }}</span>
        <span>Droite {{ $counts['right'] }}</span>
        @if($counts['unknown'] > 0)
            <span class="opacity-50">Non classés {{ $counts['unknown'] }}</span>
        @endif
    </div>
</div>
