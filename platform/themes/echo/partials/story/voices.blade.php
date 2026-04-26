@php
    use App\Support\GrimbaStoryInsights;

    $quotes = GrimbaStoryInsights::buildVoices($clusterPosts ?? collect());
    $biasColors = [
        'left' => '#3b82f6',
        'center' => '#a8a8a8',
        'right' => '#e84c3d',
        'unknown' => 'rgba(26,23,19,0.45)',
    ];
@endphp

@if(count($quotes) >= 2)
    <aside class="glass-panel p-3 mb-3">
        <h2 class="h6 mb-2" style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; font-size:13px; opacity:0.75;">
            Voix citées
        </h2>
        <div style="display:grid; gap:12px;">
            @foreach($quotes as $entry)
                @php $color = $biasColors[$entry['bias'] ?? 'unknown'] ?? $biasColors['unknown']; @endphp
                <blockquote class="m-0" style="padding:12px 14px; border-radius:14px; border-left:3px solid {{ $color }}; background:rgba(255,255,255,0.38);">
                    <p class="mb-2" style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-size:16px; line-height:1.45;">
                        “{{ $entry['quote'] }}”
                    </p>
                    @if($entry['source'])
                        <footer class="small opacity-60">— {{ $entry['source'] }}</footer>
                    @endif
                </blockquote>
            @endforeach
        </div>
    </aside>
@endif
