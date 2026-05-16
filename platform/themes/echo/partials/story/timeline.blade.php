@php
    /**
     * S180 — Story timeline panel. Chronological view of how a cluster
     * unfolded: one entry per source, sorted by published-at ascending,
     * with date + outlet name + bias dot.
     *
     * Skipped when the cluster has fewer than 3 posts — at that point
     * the cluster article list above already reads as a chronology.
     *
     * @var \Illuminate\Database\Eloquent\Collection $clusterPosts
     */
    use Illuminate\Support\Str;

    $entries = collect($clusterPosts)
        ->filter(fn ($cp) => $cp->created_at)
        ->sortBy('created_at')
        ->values();

    $biasColor = [
        'left'    => '#3b82f6',
        'center'  => '#a8a8a8',
        'right'   => '#e84c3d',
        'unknown' => '#6b6459',
    ];
@endphp

@if($entries->count() >= 3)
    <aside class="grimba-story-timeline glass-panel p-3 mb-3">
        <h2 class="h6 mb-3" style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; font-size:13px; opacity:0.75;">
            {{ __('Chronologie') }}
        </h2>

        <ol class="list-unstyled m-0" style="position:relative; padding-left:18px;">
            {{-- Vertical rail --}}
            <div aria-hidden="true" style="position:absolute; left:5px; top:6px; bottom:6px; width:1px; background:rgba(26,23,19,0.12);"></div>

            @foreach($entries as $cp)
                @php
                    $bias = $cp->bias_rating ?? 'unknown';
                    if (! isset($biasColor[$bias])) $bias = 'unknown';
                    $color = $biasColor[$bias];
                    $when  = $cp->created_at->locale('fr');
                @endphp
                <li style="position:relative; padding:6px 0 12px 0;">
                    {{-- Dot --}}
                    <span aria-hidden="true" style="
                        position:absolute; left:-18px; top:10px;
                        width:11px; height:11px; border-radius:50%;
                        background:{{ $color }};
                        box-shadow:0 0 0 3px var(--gn-paper, #f6f1e8);
                    "></span>

                    <div class="small opacity-75" style="font-size:12px; letter-spacing:0.2px;">
                        {{ $when->isoFormat('D MMM · HH:mm') }}
                    </div>
                    <div style="font-size:13.5px; line-height:1.35; margin-top:1px;">
                        <strong>{{ $cp->source_name ?? '—' }}</strong>
                    </div>
                    <div class="opacity-75" style="font-size:12.5px; line-height:1.35; margin-top:2px;">
                        {{ Str::limit(strip_tags((string) $cp->name), 80) }}
                    </div>
                </li>
            @endforeach
        </ol>
    </aside>
@endif
