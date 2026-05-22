@php
    /**
     * S100 — reader's bias-mix widget. Reads the grimba_read cookie
     * (last 30 article ids, most-recent-first, set client-side in
     * post.blade.php) and renders an L/C/R distribution bar plus
     * (when $variant === 'full') a "rééquilibrer" CTA pointing at
     * the under-represented bias on /blog and a CSV export link.
     *
     * Variants:
     *   'compact' — homepage sidebar (S49 baseline)
     *   'full'    — /pour-vous: bigger bar, diversity hint, CTA, export
     *
     * Cookie is the only data source — never queried from server
     * state. Reading history is fully client-owned.
     */
    use Botble\Blog\Models\Post;

    $variant = $variant ?? 'compact';

    $readRaw = (string) request()->cookie('grimba_read', '');
    $readIds = array_filter(array_map('intval', explode(',', $readRaw)));

    $readPosts = collect();
    $biasCounts = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
    $readSources = [];

    if (! empty($readIds)) {
        $readPosts = Post::query()
            ->whereIn('id', $readIds)
            ->where('status', 'published')
            ->get(['id', 'name', 'bias_rating', 'source_name']);

        foreach ($readPosts as $rp) {
            $r = $rp->bias_rating ?? 'unknown';
            if (isset($biasCounts[$r])) $biasCounts[$r]++;
            if ($rp->source_name) $readSources[$rp->source_name] = ($readSources[$rp->source_name] ?? 0) + 1;
        }
    }

    $readTotal    = $readPosts->count();
    $known        = $biasCounts['left'] + $biasCounts['center'] + $biasCounts['right'];
    // S148 — when there's no read history, render an empty bar
    // (zeroed segments) instead of the 33/34/33 placeholder Vader
    // flagged. A pseudo-balanced default looked like real article
    // distribution and lied about coverage.
    $pct = [
        'left'   => $known ? round($biasCounts['left']   * 100 / $known) : 0,
        'center' => $known ? round($biasCounts['center'] * 100 / $known) : 0,
        'right'  => $known ? round($biasCounts['right']  * 100 / $known) : 0,
    ];
    $sourcesCount = count($readSources);

    // Identify the under-represented side. If two are tied for last,
    // prefer the one absent rather than the one merely small.
    $underBias = null;
    $underPct = 100;
    if ($known > 0) {
        foreach (['left', 'center', 'right'] as $b) {
            if ($pct[$b] < $underPct) {
                $underPct = $pct[$b];
                $underBias = $b;
            }
        }
    }
    $biasLabels = ['left' => __('Gauche'), 'center' => __('Centre'), 'right' => __('Droite')];

    // Diversity score: 0 when one side dominates; 100 when even.
    // Computed as 100 × (1 - max-deviation-from-third).
    $diversity = $known > 0
        ? max(0, 100 - max(abs($pct['left'] - 33), abs($pct['center'] - 34), abs($pct['right'] - 33)) * 2)
        : null;
@endphp

<section class="grimba-bias-profile @if($variant === 'full') grimba-bias-profile--full @endif">
    <h4 class="@if($variant === 'full') h5 @else h6 @endif mb-1">{{ __('Votre biais de lecture') }}</h4>
    <p class="small opacity-75 mb-2">
        @if($readTotal === 0)
            {{ __('0 source · 0 article ·') }} <em>{{ __('lisez quelques articles pour voir votre profil') }}</em>
        @else
            {{ trans_choice(':count source|:count sources', $sourcesCount, ['count' => $sourcesCount]) }} · {{ trans_choice(':count article lu|:count articles lus', $readTotal, ['count' => $readTotal]) }}
            @if($variant === 'full' && $diversity !== null)
                · {{ __('diversité') }} {{ $diversity }}%
            @endif
        @endif
    </p>
    <div style="display:flex;height:{{ $variant === 'full' ? '14px' : '8px' }};border-radius:9999px;overflow:hidden;background:rgba(0,0,0,.08);">
        <div style="width:{{ $pct['left'] }}%;background:#3b82f6;" title="{{ __('Gauche') }} {{ $pct['left'] }}%"></div>
        <div style="width:{{ $pct['center'] }}%;background:#a8a8a8;" title="{{ __('Centre') }} {{ $pct['center'] }}%"></div>
        <div style="width:{{ $pct['right'] }}%;background:#e84c3d;" title="{{ __('Droite') }} {{ $pct['right'] }}%"></div>
    </div>

    @if($readTotal > 0)
        <div class="d-flex justify-content-between small mt-2">
            <span style="color:#3b82f6;font-weight:600;">{{ __('Gauche') }} {{ $pct['left'] }}%</span>
            <span style="color:#a8a8a8;font-weight:600;">{{ __('Centre') }} {{ $pct['center'] }}%</span>
            <span style="color:#e84c3d;font-weight:600;">{{ __('Droite') }} {{ $pct['right'] }}%</span>
        </div>
    @else
        <a href="{{ url('/blog') }}" class="small text-decoration-underline mt-2 d-inline-block">{{ __('Commencer à lire') }}</a>
    @endif

    @if($variant === 'full' && $readTotal > 0)
        <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
            @if($underBias && $underPct < 25)
                <a href="{{ url('/blog?bias=' . $underBias) }}"
                   class="btn-grimba btn-grimba--solid btn-grimba--sm"
                   style="--gn-accent:{{ $underBias === 'left' ? '#3b82f6' : ($underBias === 'right' ? '#e84c3d' : '#a8a8a8') }};">
                    Rééquilibrer — voir {{ mb_strtolower($biasLabels[$underBias]) }} ({{ $underPct }}%)
                </a>
            @endif
            <a href="{{ url('/pour-vous/export.csv') }}"
               class="btn-grimba btn-grimba--ghost btn-grimba--sm"
               title="Exporter votre historique de lecture (jamais stocké côté serveur)">
                Exporter mon historique (.csv)
            </a>
        </div>

        @if($sourcesCount > 0)
            <details class="mt-3 small opacity-90">
                <summary style="cursor:pointer;">Vos sources les plus lues ({{ $sourcesCount }})</summary>
                <ul class="mt-2 mb-0" style="list-style:none; padding:0;">
                    @php arsort($readSources); @endphp
                    @foreach(array_slice($readSources, 0, 8, true) as $name => $count)
                        <li class="d-flex justify-content-between py-1" style="border-bottom:1px dashed rgba(0,0,0,0.06);">
                            <span>{{ $name }}</span>
                            <span class="opacity-75">{{ $count }}×</span>
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif
    @endif
</section>
