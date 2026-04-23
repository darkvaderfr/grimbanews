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
    $showBar = count($sides) >= 2;

    $pct = [
        'left'   => $showBar ? round($counts['left']   * 100 / $total) : 0,
        'center' => $showBar ? round($counts['center'] * 100 / $total) : 0,
        'right'  => $showBar ? round($counts['right']  * 100 / $total) : 0,
    ];

    $fallbackLabel = match ($post->bias_rating ?? null) {
        'left'   => 'Gauche',
        'center' => 'Centre',
        'right'  => 'Droite',
        default  => null,
    };
    $source = $post->source_name ?? null;
@endphp

@if($showBar)
    <div @class(['grimba-coverage', 'grimba-coverage--compact' => $compact, 'grimba-coverage--on-dark' => $onDark])>
        <div class="grimba-coverage__bar" aria-hidden="true">
            <div class="grimba-coverage__seg grimba-coverage__seg--l" style="width: {{ $pct['left'] }}%;"></div>
            <div class="grimba-coverage__seg grimba-coverage__seg--c" style="width: {{ $pct['center'] }}%;"></div>
            <div class="grimba-coverage__seg grimba-coverage__seg--r" style="width: {{ $pct['right'] }}%;"></div>
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
