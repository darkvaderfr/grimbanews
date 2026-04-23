@php
    Theme::layout('grimba-chrome');
    /**
     * @var \Illuminate\Support\Collection $clusters
     */

    $biasColor = [
        'left'    => '#3b82f6',
        'center'  => '#b39152',
        'right'   => '#ef4444',
        'unknown' => '#9ca3af',
    ];
@endphp

<section class="grimba-comparison-index py-5">
    <div class="container">

        <header class="glass-panel p-4 p-md-5 mb-4">
            <span class="grimba-methodology__kicker">Comparer les sources</span>
            <h1 class="grimba-methodology__title mt-2 mb-2">
                {{ $clusters->count() }} {{ $clusters->count() === 1 ? 'dossier ouvert' : 'dossiers ouverts' }}
            </h1>
            <p class="mb-0 opacity-85">
                Chaque dossier regroupe la même histoire couverte par plusieurs médias.
                Ouvrez-en un pour voir les angles côte à côte — et
                <a href="{{ url('/methodologie') }}" class="text-decoration-underline">comment nous les classons</a>.
            </p>
        </header>

        @if($clusters->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">Aucun dossier actif pour l'instant.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($clusters as $c)
                    @php
                        $activeSides = 0;
                        foreach (['left', 'center', 'right'] as $k) if ($c->counts[$k] > 0) $activeSides++;
                        $label = match ($activeSides) {
                            3 => ['Couverture équilibrée', '#22c55e'],
                            2 => ['Couverture partielle',  '#eab308'],
                            1 => ['Couverture unilatérale', '#ef4444'],
                            default => ['En attente',       '#9ca3af'],
                        };
                        $pctTotal = max(1, $c->total);
                        $pct = [
                            'left'   => round($c->counts['left']   * 100 / $pctTotal),
                            'center' => round($c->counts['center'] * 100 / $pctTotal),
                            'right'  => round($c->counts['right']  * 100 / $pctTotal),
                        ];
                        $latest = $c->latestAt
                            ? \Carbon\Carbon::parse($c->latestAt)->locale('fr')->diffForHumans(['short' => false])
                            : null;
                    @endphp
                    <div class="col-lg-6 col-12">
                        <a href="{{ url('/comparatif/' . $c->id) }}" class="grimba-comparison-index__card">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="grimba-comparison-index__dossier">Dossier #{{ $c->id }}</span>
                                @if($latest)
                                    <span class="small opacity-75">Dernière mise à jour {{ $latest }}</span>
                                @endif
                            </div>

                            <h2 class="grimba-comparison-index__title">{{ $c->topic }}</h2>

                            <div class="grimba-comparison-index__bar">
                                <div style="width: {{ $pct['left'] }}%;background: {{ $biasColor['left'] }};"></div>
                                <div style="width: {{ $pct['center'] }}%;background: {{ $biasColor['center'] }};"></div>
                                <div style="width: {{ $pct['right'] }}%;background: {{ $biasColor['right'] }};"></div>
                            </div>

                            <div class="d-flex justify-content-between small mt-2 mb-3 opacity-85">
                                <span style="color: {{ $biasColor['left'] }};">● Gauche {{ $c->counts['left'] }}</span>
                                <span style="color: {{ $biasColor['center'] }};">● Centre {{ $c->counts['center'] }}</span>
                                <span style="color: {{ $biasColor['right'] }};">● Droite {{ $c->counts['right'] }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center small">
                                <span style="color: {{ $label[1] }};font-weight:600;">{{ $label[0] }}</span>
                                <span>{{ $c->total }} sources ·
                                    @foreach($c->posts->take(3) as $p)<em>{{ $p->source_name ?? '—' }}</em>@if(!$loop->last), @endif @endforeach
                                    @if($c->posts->count() > 3)…@endif
                                </span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
