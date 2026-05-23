@php
    /**
     * Source Diversity Meter
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
        3 => __('Couverture équilibrée'),
        2 => __('Couverture partielle'),
        1 => __('Couverture unilatérale'),
        default => __('Aucune source classée'),
    };

    // Wave DDDDDDDDDDD (Vader 2026-05-23) — flag Middle Ground via the
    // shared GrimbaClusterBias resolver. L=R tie that meets/beats
    // center = Middle Ground (different signal from balanced).
    $resolvedSignal = \App\Support\GrimbaClusterBias::resolve($counts);
    $isMiddleGround = $resolvedSignal['key'] === 'middle_ground';
@endphp

<div class="diversity-meter glass-panel p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="text-uppercase small">{{ __('Diversité des sources') }}</strong>
        <span class="small opacity-75">
            {{ $balanceLabel }} — {{ trans_choice(':count source|:count sources', $total, ['count' => $total]) }}
            @if($isMiddleGround)
                · <span style="color:#a855f7;font-weight:600;">{{ __('Juste milieu') }}</span>
            @endif
        </span>
    </div>

    <div class="diversity-bar" style="display:flex;height:10px;border-radius:9999px;overflow:hidden;background:rgba(0,0,0,0.06);">
        <div style="width: {{ $pct['left'] }}%;background:#3b82f6;" title="{{ __('Gauche') }} — {{ $counts['left'] }}"></div>
        <div style="width: {{ $pct['center'] }}%;background:#a8a8a8;" title="{{ __('Centre') }} — {{ $counts['center'] }}"></div>
        <div style="width: {{ $pct['right'] }}%;background:#e84c3d;" title="{{ __('Droite') }} — {{ $counts['right'] }}"></div>
    </div>

    <div class="d-flex justify-content-between small mt-2">
        <span style="color:#3b82f6;font-weight:600;">● {{ __('Gauche') }} {{ $pct['left'] }}%</span>
        <span style="color:#a8a8a8;font-weight:600;">● {{ __('Centre') }} {{ $pct['center'] }}%</span>
        <span style="color:#e84c3d;font-weight:600;">● {{ __('Droite') }} {{ $pct['right'] }}%</span>
    </div>
</div>
