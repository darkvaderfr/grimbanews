@php
    /**
     * "Couvert par tous les côtés" rail. Cards sourced from
     * GrimbaHomeFeed so cluster-head posts never collide with the
     * sections below.
     */

    use App\Support\GrimbaHomeFeed;
    use App\Support\GrimbaTranslationPresenter as GnTr;

    $cards = GrimbaHomeFeed::allSides();
    if (empty($cards)) return;

    $biasMeta = [
        'left'   => '#3b82f6',
        'center' => '#a8a8a8',
        'right'  => '#e84c3d',
    ];
@endphp

<section class="grimba-all-sides container-xxl py-3 py-md-4">
    <header class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <span class="grimba-methodology__kicker">{{ __('Couvert par tous les côtés') }}</span>
            <h2 class="grimba-methodology__title grimba-all-sides__title m-0 mt-1">
                {{ __('Histoires que gauche, centre et droite couvrent en même temps') }}
            </h2>
        </div>
        <span class="small grimba-all-sides__count">{{ trans_choice(':count histoire ce moment|:count histoires ce moment', count($cards), ['count' => count($cards)]) }}</span>
    </header>

    <div class="grimba-all-sides__rail">
        @foreach($cards as $__cardIdx => $card)
            @php
                $head = $card['head'];
                $url = url('/comparatif/' . $card['cluster_id']);
                $title = GnTr::title($head);
                $isTranslated = GnTr::isTranslated($head);
                $__totalSides = max(1, ($card['counts']['left'] ?? 0) + ($card['counts']['center'] ?? 0) + ($card['counts']['right'] ?? 0));
                $__lPct = (int) round(($card['counts']['left']   ?? 0) * 100 / $__totalSides);
                $__cPct = (int) round(($card['counts']['center'] ?? 0) * 100 / $__totalSides);
                $__rPct = (int) round(($card['counts']['right']  ?? 0) * 100 / $__totalSides);
            @endphp
            <a href="{{ $url }}"
               class="grimba-all-sides__card grimba-all-sides__card--cinematic"
               style="--gn-rail-delay: {{ $__cardIdx * 70 }}ms;">
                <span class="grimba-all-sides__spectrum" aria-hidden="true">
                    <span style="width: {{ $__lPct }}%; background: #3b82f6;"></span>
                    <span style="width: {{ $__cPct }}%; background: #a8a8a8;"></span>
                    <span style="width: {{ $__rPct }}%; background: #e84c3d;"></span>
                </span>

                {{-- S329 — match post-hero-img.blade.php's safety: skip
                      RvMedia's 1920×1080 generic placeholder fallback by
                      pre-resolving and comparing against the default URL. --}}
                @php
                    $__rsResolved = $card['image']
                        ? \Botble\Media\Facades\RvMedia::getImageUrl($card['image'])
                        : null;
                    $__rsDefault = \Botble\Media\Facades\RvMedia::getDefaultImage();
                    $__rsUsable = $__rsResolved !== null && $__rsResolved !== $__rsDefault;
                @endphp
                @if($__rsUsable)
                    <div class="ratio ratio-16x9 grimba-all-sides__media">
                        <img src="{{ $__rsResolved }}"
                             alt="{{ $title }}"
                             loading="lazy"
                             decoding="async"
                             width="640"
                             height="360"
                             data-grimba-post-id="{{ $head->id }}"
                             class="grimba-all-sides__image">
                    </div>
                @else
                    <img src="{{ url('/og/placeholder/' . $head->id . '.svg') }}"
                         alt="{{ $title }}"
                         loading="lazy"
                         decoding="async"
                         width="640"
                         height="360"
                         data-grimba-post-id="{{ $head->id }}"
                         class="grimba-all-sides__image grimba-all-sides__image--standalone">
                @endif

                <div class="grimba-all-sides__body">
                    <div class="d-flex align-items-center gap-2 mb-2 small">
                        @foreach(['left','center','right'] as $b)
                            @if($card['counts'][$b] > 0)
                                <span style="
                                    display:inline-flex; align-items:center; gap:4px;
                                    padding:2px 8px; border-radius:9999px;
                                    background:{{ $biasMeta[$b] }}1a; color:{{ $biasMeta[$b] }};
                                    font-size:11px; font-weight:700; letter-spacing:0.4px; text-transform:uppercase;
                                ">
                                    <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:{{ $biasMeta[$b] }};"></span>
                                    {{ $card['counts'][$b] }}
                                </span>
                            @endif
                        @endforeach
                        <span class="ms-auto grimba-all-sides__source-count">
                            {{ trans_choice(':count source|:count sources', $card['articles'], ['count' => $card['articles']]) }}
                        </span>
                    </div>
                    <h3 class="grimba-all-sides__headline">
                        {{ \Illuminate\Support\Str::limit($title, 110) }}
                    </h3>
                    @if($isTranslated)
                        <div class="mt-2">{!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}</div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</section>

<style>
    .grimba-all-sides__card--cinematic {
        position: relative;
        transition: transform .3s cubic-bezier(.22, 1, .36, 1), box-shadow .3s ease;
        will-change: transform;
        opacity: 0;
        animation: grimbaRailEnter .55s cubic-bezier(.22, 1, .36, 1) forwards;
        animation-delay: var(--gn-rail-delay, 0ms);
    }

    @keyframes grimbaRailEnter {
        from { opacity: 0; transform: translateY(8px) scale(.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .grimba-all-sides__card--cinematic:hover {
        transform: translateY(-3px);
        box-shadow: 0 24px 54px rgba(26, 23, 19, .14);
    }

    .grimba-all-sides__spectrum {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 3;
        display: flex;
        height: 3px;
        border-radius: 0 0 999px 999px;
        overflow: hidden;
        pointer-events: none;
    }

    .grimba-all-sides__spectrum > span {
        display: block;
        height: 100%;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .22);
        transition: filter .25s ease;
    }

    .grimba-all-sides__card--cinematic:hover .grimba-all-sides__spectrum > span {
        filter: saturate(1.18) brightness(1.05);
    }

    @media (prefers-reduced-motion: reduce) {
        .grimba-all-sides__card--cinematic {
            opacity: 1;
            transform: none;
            animation: none;
        }

        .grimba-all-sides__card--cinematic:hover {
            transform: none;
        }
    }
</style>
