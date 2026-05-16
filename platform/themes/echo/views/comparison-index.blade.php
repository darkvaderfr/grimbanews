@php
    Theme::layout('grimba-chrome');
    /**
     * @var \Illuminate\Support\Collection $clusters
     * @var object $pagination  {currentPage, lastPage, totalCount, perPage}
     * @var string $diversityFilter
     */

    $biasColor = [
        'left'    => '#3b82f6',
        'center'  => '#a8a8a8',
        'right'   => '#ef4444',
        'unknown' => '#9ca3af',
    ];

    $diversityTabs = [
        'all'       => ['label' => __('Tous'),                    'color' => '#1a1713'],
        'balanced'  => ['label' => __('Couverture équilibrée'),   'color' => '#22c55e'],
        'partial'   => ['label' => __('Couverture partielle'),    'color' => '#eab308'],
        'one_sided' => ['label' => __('Couverture unilatérale'),  'color' => '#ef4444'],
    ];

    $buildPageUrl = function (int $page) use ($diversityFilter): string {
        $base = url('/comparatif');
        $qs = [];
        if ($diversityFilter !== 'all') $qs['diversity'] = $diversityFilter;
        if ($page > 1) $qs['page'] = $page;
        return $qs ? $base . '?' . http_build_query($qs) : $base;
    };
@endphp

<section class="grimba-comparison-index py-5">
    <div class="container">

        <header class="glass-panel grimba-editorial-ribbon p-4 p-md-5 mb-4">
            <span class="grimba-methodology__kicker">{{ __('Comparer les sources') }}</span>
            <h1 class="grimba-methodology__title mt-2 mb-2">
                {{ $pagination->totalCount }} {{ $pagination->totalCount === 1 ? __('dossier ouvert') : __('dossiers ouverts') }}
            </h1>
            <p class="grimba-comparison-index__lede mb-3">
                {{ __('Chaque dossier regroupe la même histoire couverte par plusieurs médias. Ouvrez-en un pour voir les angles côte à côte — et') }}
                <a href="{{ url('/methodologie') }}" class="text-decoration-underline">{{ __('comment nous les classons') }}</a>.
            </p>

            {{-- S324 — diversity filter pills. --}}
            <div class="d-flex gap-2 flex-wrap" role="tablist" aria-label="{{ __('Filtrer par couverture') }}">
                @foreach($diversityTabs as $key => $meta)
                    @php $active = $diversityFilter === $key; @endphp
                    <a href="{{ url('/comparatif') . ($key === 'all' ? '' : '?diversity=' . $key) }}"
                       class="btn-grimba btn-grimba--sm {{ $active ? 'btn-grimba--solid' : 'btn-grimba--ghost' }}"
                       role="tab"
                       aria-selected="{{ $active ? 'true' : 'false' }}"
                       @if(! $active) style="border-color:{{ $meta['color'] }}55;color:{{ $meta['color'] }};" @endif>
                        @if($key !== 'all')
                            <span aria-hidden="true" style="display:inline-block;width:7px;height:7px;border-radius:50%;background:{{ $meta['color'] }};margin-right:6px;"></span>
                        @endif
                        {{ $meta['label'] }}
                    </a>
                @endforeach
            </div>
        </header>

        @if($clusters->isEmpty())
            <div class="glass-panel p-4 text-center">
                <p class="mb-0">{{ __("Aucun dossier ne correspond à ce filtre.") }}</p>
            </div>
        @else
            <div class="row g-3">
                @foreach($clusters as $c)
                    @php
                        $label = $diversityTabs[$c->diversity] ?? $diversityTabs['one_sided'];
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
                                <span class="grimba-comparison-index__dossier">{{ __('Dossier') }} #{{ $c->id }}</span>
                                @if($latest)
                                    <span class="small opacity-75">{{ __('mis à jour') }} {{ $latest }}</span>
                                @endif
                            </div>

                            <h2 class="grimba-comparison-index__title">{{ $c->topic }}</h2>

                            <div class="grimba-comparison-index__bar">
                                <div style="width: {{ $pct['left'] }}%;background: {{ $biasColor['left'] }};"></div>
                                <div style="width: {{ $pct['center'] }}%;background: {{ $biasColor['center'] }};"></div>
                                <div style="width: {{ $pct['right'] }}%;background: {{ $biasColor['right'] }};"></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center small mt-2">
                                <span class="d-flex gap-2 flex-wrap opacity-85">
                                    <span style="color: {{ $biasColor['left'] }};">● {{ $c->counts['left'] }}</span>
                                    <span style="color: {{ $biasColor['center'] }};">● {{ $c->counts['center'] }}</span>
                                    <span style="color: {{ $biasColor['right'] }};">● {{ $c->counts['right'] }}</span>
                                </span>
                                <span style="color: {{ $label['color'] }};font-weight:700;">{{ $label['label'] }}</span>
                                <span class="opacity-70">{{ trans_choice(':count source|:count sources', $c->total, ['count' => $c->total]) }}</span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- S324 — pagination. --}}
            @if($pagination->lastPage > 1)
                <nav class="d-flex justify-content-between align-items-center mt-4" aria-label="{{ __('Navigation des pages') }}">
                    <a href="{{ $buildPageUrl(max(1, $pagination->currentPage - 1)) }}"
                       class="btn-grimba btn-grimba--sm btn-grimba--ghost {{ $pagination->currentPage <= 1 ? 'opacity-50 pe-none' : '' }}"
                       @if($pagination->currentPage <= 1) aria-disabled="true" @endif>
                        ← {{ __('Précédent') }}
                    </a>
                    <span class="small opacity-75">
                        {{ __('Page :current sur :last', ['current' => $pagination->currentPage, 'last' => $pagination->lastPage]) }}
                    </span>
                    <a href="{{ $buildPageUrl(min($pagination->lastPage, $pagination->currentPage + 1)) }}"
                       class="btn-grimba btn-grimba--sm btn-grimba--ghost {{ $pagination->currentPage >= $pagination->lastPage ? 'opacity-50 pe-none' : '' }}"
                       @if($pagination->currentPage >= $pagination->lastPage) aria-disabled="true" @endif>
                        {{ __('Suivant') }} →
                    </a>
                </nav>
            @endif
        @endif
    </div>
</section>
