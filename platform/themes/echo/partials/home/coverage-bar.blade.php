@php
    /**
     * GrimbaNews coverage bar.
     *
     * Shows L/C/R share ONLY when the post belongs to a story_cluster with
     * ≥2 bias sides represented. Otherwise shows a compact Centre/Source
     * label — we don't fabricate a bar from one data point.
     *
     * @var \Botble\Blog\Models\Post $post
     * @var bool $compact
     * @var bool $onDark
     */

    use Botble\Blog\Models\Post;

    $compact = $compact ?? false;
    $onDark  = $onDark ?? false;

    $counts = ['left'=>0,'center'=>0,'right'=>0];
    $total  = 0;

    if ($post->story_cluster_id) {
        $cluster = Post::query()
            ->where('story_cluster_id', $post->story_cluster_id)
            ->where('status', 'published')
            ->get();

        foreach ($cluster as $cp) {
            $r = $cp->bias_rating ?? 'unknown';
            if (isset($counts[$r])) {
                $counts[$r]++;
            }
        }
        $total = array_sum($counts);
    }

    $sides = array_filter($counts, fn ($c) => $c > 0);
    $sideCount = count($sides);
    // S304: bar still renders for ≥2 sides. For exactly 1 side OR exactly
    // 1 source we render a "single-segment" bar so every card surfaces a
    // visible coverage indicator (Ground does this on 100% of cards). We
    // never fabricate a 2-side split out of a single source — single-side
    // bars take the full width of the dominant side.
    $showBar       = $sideCount >= 2;
    $showSingleBar = ! $showBar && ($sideCount === 1 || ($post->bias_rating ?? null) !== null) && in_array(($post->bias_rating ?? null), ['left', 'center', 'right'], true);

    $pct = [
        'left'   => $showBar ? round($counts['left']   * 100 / $total) : 0,
        'center' => $showBar ? round($counts['center'] * 100 / $total) : 0,
        'right'  => $showBar ? round($counts['right']  * 100 / $total) : 0,
    ];

    $singleSide = null;
    if ($showSingleBar) {
        $singleSide = ($sideCount === 1)
            ? array_key_first($sides)
            : ($post->bias_rating ?? null);
    }

    $tooltipBar = $showBar
        ? sprintf('Gauche %d · Centre %d · Droite %d (%d sources)', $counts['left'], $counts['center'], $counts['right'], $total)
        : null;
    $tooltipSingle = $showSingleBar ? sprintf('Couverture observée d\'une seule perspective : %s', match ($singleSide) {
        'left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite', default => '—',
    }) : null;

    $fallbackLabel = match ($post->bias_rating ?? null) {
        'left'   => 'Gauche',
        'center' => 'Centre',
        'right'  => 'Droite',
        default  => null,
    };
    $source = $post->source_name ?? null;
@endphp

@if($showBar)
    <div
        @class(['grimba-coverage', 'grimba-coverage--compact' => $compact, 'grimba-coverage--on-dark' => $onDark])
        title="{{ $tooltipBar }}"
        data-coverage-mode="multi"
    >
        <div class="grimba-coverage__bar" aria-hidden="true">
            <div class="grimba-coverage__seg grimba-coverage__seg--l" style="width: {{ $pct['left'] }}%;" data-side-count="{{ $counts['left'] }}"></div>
            <div class="grimba-coverage__seg grimba-coverage__seg--c" style="width: {{ $pct['center'] }}%;" data-side-count="{{ $counts['center'] }}"></div>
            <div class="grimba-coverage__seg grimba-coverage__seg--r" style="width: {{ $pct['right'] }}%;" data-side-count="{{ $counts['right'] }}"></div>
        </div>
        @unless($compact)
            <div class="grimba-coverage__legend">
                <span class="grimba-coverage__chip grimba-coverage__chip--l">Gauche {{ $pct['left'] }}%</span>
                <span class="grimba-coverage__chip grimba-coverage__chip--c">Centre {{ $pct['center'] }}%</span>
                <span class="grimba-coverage__chip grimba-coverage__chip--r">Droite {{ $pct['right'] }}%</span>
                <span class="grimba-coverage__sources">{{ $total }} sources</span>
            </div>
        @endunless
    </div>
@elseif($showSingleBar)
    <div
        @class(['grimba-coverage grimba-coverage--single', 'grimba-coverage--compact' => $compact, 'grimba-coverage--on-dark' => $onDark])
        title="{{ $tooltipSingle }}"
        data-coverage-mode="single"
        data-coverage-side="{{ $singleSide }}"
    >
        <div class="grimba-coverage__bar" aria-hidden="true">
            <div class="grimba-coverage__seg grimba-coverage__seg--{{ substr($singleSide, 0, 1) }}" style="width: 100%;"></div>
        </div>
        @unless($compact)
            <div class="grimba-coverage__legend">
                <span class="grimba-coverage__chip grimba-coverage__chip--{{ substr($singleSide, 0, 1) }}">
                    @if($singleSide === 'left') Gauche
                    @elseif($singleSide === 'center') Centre
                    @else Droite
                    @endif
                </span>
                <span class="grimba-coverage__sources">{{ $total > 0 ? $total : 1 }} source{{ $total > 1 ? 's' : '' }}</span>
            </div>
        @endunless
    </div>
@elseif($fallbackLabel || $source)
    <div @class(['grimba-coverage grimba-coverage--label', 'grimba-coverage--on-dark' => $onDark])>
        @if($fallbackLabel)
            <span class="grimba-coverage__dot grimba-coverage__dot--{{ $post->bias_rating }}"></span>
            <span>{{ $fallbackLabel }}</span>
        @endif
        @if($source)
            <span class="opacity-75">· {{ $source }}</span>
        @endif
    </div>
@endif
