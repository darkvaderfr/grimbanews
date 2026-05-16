@php
    use App\Support\GrimbaStoryInsights;

    $items = GrimbaStoryInsights::buildHighlights($clusterPosts ?? collect());
@endphp

@if(count($items) >= 3)
    <aside class="glass-panel grimba-editorial-ribbon p-3 mb-3">
        <h2 class="h6 mb-2" style="font-family:'Public Sans',system-ui,sans-serif; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; font-size:13px; opacity:0.75;">
            {{ __('Temps forts') }}
        </h2>
        <p class="small opacity-75 mb-3">
            {{ __('Les noms qui reviennent le plus dans cette couverture.') }}
        </p>

        <ol class="m-0 ps-3" style="display:grid; gap:10px; font-size:14px; line-height:1.45;">
            @foreach($items as $item)
                <li>
                    <strong>{{ $item['label'] }}</strong>
                    <span class="opacity-60">· {{ trans_choice(':count mention|:count mentions', $item['count'], ['count' => $item['count']]) }}</span>
                </li>
            @endforeach
        </ol>
    </aside>
@endif
