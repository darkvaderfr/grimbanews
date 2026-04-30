@php
    /**
     * S306 — Bias Comparison Summary (Ground-fidelity 3-column framing).
     *
     * Takes the same extractive synthesis items the standard insights
     * block consumes and groups them into 3 columns: Gauche / Centre /
     * Droite. Each column shows up to N representative source
     * paraphrases so the reader can compare framing at a glance.
     *
     * Renders only when at least 2 of the 3 sides have ≥1 item — a
     * 1-side "comparison" isn't a comparison.
     *
     * @var array<int, array{text:string, source:?string, bias:?string}> $items
     * @var int $perColumn  Optional. Max items per column (default 3).
     */
    use App\Ground\Bias;

    $perColumn = $perColumn ?? 3;

    $cols = ['left' => [], 'center' => [], 'right' => []];
    foreach (($items ?? []) as $it) {
        $bias = is_array($it) ? ($it['bias'] ?? null) : null;
        $side = match ($bias) {
            'left', 'far_left', 'lean_left' => 'left',
            'right', 'far_right', 'lean_right' => 'right',
            'center' => 'center',
            default => null,
        };
        if ($side && count($cols[$side]) < $perColumn) {
            $cols[$side][] = $it;
        }
    }

    $sidesWithItems = array_filter($cols, fn ($c) => count($c) > 0);
    if (count($sidesWithItems) < 2) {
        return;
    }

    $colMeta = [
        'left'   => ['label' => __('Côté gauche'),   'color' => '#3b82f6', 'bg' => 'rgba(59, 130, 246, 0.06)', 'short' => 'L'],
        'center' => ['label' => __('Côté centre'),   'color' => '#8a8a8a', 'bg' => 'rgba(138, 138, 138, 0.06)', 'short' => 'C'],
        'right'  => ['label' => __('Côté droit'),    'color' => '#e84c3d', 'bg' => 'rgba(232, 76, 61, 0.06)', 'short' => 'R'],
    ];
@endphp

<section class="grimba-bias-compare" aria-label="{{ __('Comparaison du cadrage par camp') }}"
         style="margin-top: 22px; padding-top: 18px; border-top: 1px dashed rgba(26,23,19,0.15);">

    <header class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <span style="
            display:inline-flex; align-items:center; gap:6px;
            padding:4px 10px; border-radius:9999px;
            background:linear-gradient(135deg,#1a1713,#3a342c);
            color:#f6f1e8;
            font-family:'Public Sans',system-ui,sans-serif;
            font-size:11.5px; font-weight:700; letter-spacing:0.5px;
        ">
            <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#f6f1e8;"></span>
            {{ __('Comparaison de cadrage') }}
        </span>
        <span class="small opacity-65" style="font-size:12.5px;">
            {{ __('Comment chaque côté du spectre couvre la même histoire.') }}
        </span>
    </header>

    <div class="row g-3">
        @foreach(['left', 'center', 'right'] as $side)
            @php
                $meta = $colMeta[$side];
                $col = $cols[$side];
            @endphp
            <div class="col-12 col-md-4">
                <article class="grimba-bias-compare__col"
                         data-side="{{ $side }}"
                         style="
                            height:100%;
                            padding:14px 14px 12px;
                            border:1px solid {{ $meta['color'] }}33;
                            border-top:3px solid {{ $meta['color'] }};
                            border-radius:12px;
                            background:{{ $meta['bg'] }};
                         ">

                    <header class="d-flex align-items-center gap-2 mb-2">
                        <span aria-hidden="true" style="
                            display:inline-flex; align-items:center; justify-content:center;
                            width:22px; height:22px; border-radius:50%;
                            background:{{ $meta['color'] }};
                            color:#fff; font-weight:800; font-size:11px;
                            box-shadow:0 0 0 2px {{ $meta['color'] }}33;
                        ">{{ $meta['short'] }}</span>
                        <strong style="
                            font-family:'Public Sans',system-ui,sans-serif;
                            font-size:13px; font-weight:700;
                            letter-spacing:0.4px; text-transform:uppercase;
                            color:{{ $meta['color'] }};
                        ">{{ $meta['label'] }}</strong>
                        <span class="ms-auto small opacity-55" style="font-size:11.5px;">
                            {{ trans_choice(':count source|:count sources', count($col), ['count' => count($col)]) }}
                        </span>
                    </header>

                    @if(empty($col))
                        <p class="small opacity-60 mb-0" style="font-style: italic; line-height:1.5;">
                            {{ __('Aucune couverture identifiée de ce côté.') }}
                        </p>
                    @else
                        <ul class="m-0" style="list-style:none; padding:0;">
                            @foreach($col as $item)
                                <li style="
                                    padding:10px 0;
                                    font-size:14px; line-height:1.5;
                                    color:var(--gn-ink,#1a1713);
                                    border-bottom:1px dashed rgba(26,23,19,0.08);
                                ">
                                    <p class="m-0">
                                        {{ is_array($item) ? ($item['text'] ?? '') : (string) $item }}
                                    </p>
                                    @if(! empty($item['source']))
                                        <small class="opacity-65" style="font-size:11.5px; font-weight:600;">
                                            — {{ $item['source'] }}
                                        </small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                </article>
            </div>
        @endforeach
    </div>

    <p class="small opacity-55 mb-0 mt-2" style="font-size:11.5px; line-height:1.5;">
        {{ __('Synthèse extractive multi-sources · vérification éditoriale en bouclage · NobuAI complète l\'analyse dès qu\'assez de sources couvrent l\'histoire.') }}
    </p>

</section>
