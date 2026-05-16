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
        @foreach($cards as $card)
            @php
                $head = $card['head'];
                $url = url('/comparatif/' . $card['cluster_id']);
                $title = GnTr::title($head);
                $isTranslated = GnTr::isTranslated($head);
            @endphp
            <a href="{{ $url }}"
               class="grimba-all-sides__card">

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
