@php
    use App\Support\GrimbaHomeFeed;
    use App\Support\GrimbaTranslationPresenter as GnTr;

    $hero          = GrimbaHomeFeed::hero();
    $briefing      = GrimbaHomeFeed::heroBriefingColumn();
    $blindspots    = GrimbaHomeFeed::heroBlindspots();
    $briefingStats = GrimbaHomeFeed::heroStats();

    GnTr::warm(collect([$hero])->filter()->concat($briefing)->concat($blindspots)->concat($briefingStats));

    $totalArticles = $briefingStats->sum(fn ($p) => max(1, $p->views ?? 1));
    $readMinutes   = max(1, (int) round($briefingStats->count() * 1.2));
@endphp

<section class="grimba-hero-grid">
    <div class="row g-4">

        {{-- Left: Daily Briefing --}}
        <aside class="col-xl-3 col-lg-4 col-12 grimba-briefing">
            <header class="grimba-briefing__head">
                <h2 class="grimba-briefing__title">{{ __('Briefing du jour') }}</h2>
                <p class="grimba-briefing__sub">
                    {{ trans_choice(':count histoire|:count histoires', $briefingStats->count(), ['count' => $briefingStats->count()]) }} ·
                    {{ trans_choice(':count article|:count articles', $totalArticles, ['count' => $totalArticles]) }} ·
                    {{ trans_choice(':count min de lecture|:count min de lecture', $readMinutes, ['count' => $readMinutes]) }}
                </p>
            </header>

            <ol class="grimba-briefing__list grimba-briefing__list--threaded" data-grimba-briefing-list>
                @foreach($briefing->take(5) as $__i => $p)
                    @php
                        $title = GnTr::title($p);
                        $isTranslated = GnTr::isTranslated($p);
                        $publishedAt = GnTr::publishedAt($p);
                        $__pBias = $p->bias_rating ?? null;
                        $__pBiasColor = match ($__pBias) {
                            'left' => '#3b82f6',
                            'center' => '#a8a8a8',
                            'right' => '#e84c3d',
                            default => null,
                        };
                    @endphp
                    <li class="grimba-briefing__item grimba-briefing__item--threaded"
                        style="--gn-thread-color: {{ $__pBiasColor ?: 'rgba(26,23,19,0.18)' }}; --gn-thread-delay: {{ $__i * 60 }}ms;">
                        <span class="grimba-briefing__medallion" aria-hidden="true">{{ $__i + 1 }}</span>
                        <a href="{{ $p->url }}" class="grimba-briefing__thumb">
                            {!! Theme::partial('post-hero-img', ['post' => $p, 'size' => 'small']) !!}
                        </a>
                        <div class="grimba-briefing__body">
                            <a href="{{ $p->url }}" class="grimba-briefing__headline">{{ $title }}</a>
                            @if($isTranslated)
                                {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                            @endif
                            @if($publishedAt)
                                <span class="small opacity-75">{{ $publishedAt->locale('fr')->diffForHumans(['short' => false]) }}</span>
                            @endif
                            {!! Theme::partial('home.coverage-bar', ['post' => $p, 'compact' => true]) !!}
                        </div>
                    </li>
                @endforeach
            </ol>

            <style>
                .grimba-briefing__list--threaded {
                    position: relative;
                    list-style: none;
                    padding-left: 38px;
                    margin: 0;
                }

                .grimba-briefing__list--threaded::before {
                    content: "";
                    position: absolute;
                    left: 18px;
                    top: 14px;
                    bottom: 14px;
                    width: 1px;
                    background: linear-gradient(
                        180deg,
                        transparent 0%,
                        rgba(26, 23, 19, .18) 12%,
                        rgba(26, 23, 19, .18) 88%,
                        transparent 100%
                    );
                }

                [data-bs-theme="dark"] .grimba-briefing__list--threaded::before {
                    background: linear-gradient(
                        180deg,
                        transparent 0%,
                        rgba(255, 250, 240, .18) 12%,
                        rgba(255, 250, 240, .18) 88%,
                        transparent 100%
                    );
                }

                .grimba-briefing__item--threaded {
                    position: relative;
                    padding: 10px 0;
                    opacity: 0;
                    transform: translateX(-6px);
                    animation: grimbaBriefingThread .5s cubic-bezier(.22, 1, .36, 1) forwards;
                    animation-delay: var(--gn-thread-delay, 0ms);
                }

                @keyframes grimbaBriefingThread {
                    to { opacity: 1; transform: translateX(0); }
                }

                .grimba-briefing__medallion {
                    position: absolute;
                    left: -32px;
                    top: 14px;
                    width: 28px;
                    height: 28px;
                    display: grid;
                    place-items: center;
                    border-radius: 50%;
                    background: var(--gn-paper, #fffaf0);
                    border: 1.5px solid var(--gn-thread-color, rgba(26, 23, 19, .18));
                    color: var(--gn-ink, #1a1713);
                    font-family: 'Fraunces', Georgia, serif;
                    font-size: 14px;
                    font-weight: 800;
                    box-shadow: 0 4px 14px rgba(26, 23, 19, .08), inset 0 0 0 2px rgba(255, 255, 255, .6);
                    z-index: 1;
                }

                [data-bs-theme="dark"] .grimba-briefing__medallion,
                body[data-theme="dark"] .grimba-briefing__medallion {
                    background: rgba(28, 24, 17, .92);
                    color: #fffaf0;
                    box-shadow: 0 6px 18px rgba(0, 0, 0, .42), inset 0 0 0 2px rgba(255, 255, 255, .08);
                }

                .grimba-briefing__item--threaded:hover .grimba-briefing__medallion {
                    box-shadow: 0 6px 18px rgba(26, 23, 19, .14),
                                0 0 0 4px color-mix(in srgb, var(--gn-thread-color, #a8a8a8) 28%, transparent),
                                inset 0 0 0 2px rgba(255, 255, 255, .6);
                }

                @media (prefers-reduced-motion: reduce) {
                    .grimba-briefing__item--threaded {
                        opacity: 1;
                        transform: none;
                        animation: none;
                    }
                }
            </style>

            <a href="{{ url('/search') }}" class="grimba-briefing__more">
                {{ __('Voir le briefing complet') }} →
            </a>
        </aside>

        {{-- Center: Hero Story --}}
        <section class="col-xl-6 col-lg-8 col-12 grimba-hero">
            @if($hero)
                @php
                    $heroTitle = GnTr::title($hero);
                    $heroDesc = GnTr::description($hero);
                    $heroTranslated = GnTr::isTranslated($hero);
                    $__heroBias = $hero->bias_rating ?? null;
                    $__heroBiasColor = match ($__heroBias) {
                        'left' => '#3b82f6',
                        'center' => '#a8a8a8',
                        'right' => '#e84c3d',
                        default => null,
                    };
                @endphp
                <a href="{{ $hero->url }}"
                   class="grimba-hero__media grimba-hero__media--cinematic"
                   data-grimba-hero-parallax
                   @if($__heroBiasColor) style="--gn-hero-bias: {{ $__heroBiasColor }};" @endif>
                    {!! Theme::partial('post-hero-img', ['post' => $hero, 'size' => 'extra-large', 'eager' => true]) !!}
                    <div class="grimba-hero__gradient"></div>
                    @if($__heroBiasColor)
                        <span class="grimba-hero__bias-strip" aria-hidden="true"></span>
                    @endif
                    <div class="grimba-hero__text">
                        <span class="grimba-hero__kicker">{{ __('Histoire phare') }}</span>
                        <h1 class="grimba-hero__title">{{ $heroTitle }}</h1>
                        @if($heroTranslated)
                            {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                        @endif
                        @if($heroDesc)
                            <p class="grimba-hero__desc">{{ \Illuminate\Support\Str::limit(strip_tags($heroDesc), 140) }}</p>
                        @endif
                    </div>
                    <div class="grimba-hero__coverage">
                        {!! Theme::partial('home.coverage-bar', ['post' => $hero, 'compact' => false]) !!}
                    </div>
                </a>

                <style>
                    .grimba-hero__media--cinematic {
                        position: relative;
                        overflow: hidden;
                        transition: transform .35s cubic-bezier(.22, 1, .36, 1), box-shadow .35s ease;
                        will-change: transform;
                    }

                    .grimba-hero__media--cinematic::before {
                        content: "";
                        position: absolute;
                        inset: 0;
                        z-index: 1;
                        pointer-events: none;
                        background: radial-gradient(60% 80% at 30% 0%, rgba(255, 255, 255, .14), transparent 60%);
                        opacity: 0;
                        transition: opacity .35s ease;
                    }

                    .grimba-hero__media--cinematic:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 24px 64px rgba(26, 23, 19, .22);
                    }

                    .grimba-hero__media--cinematic:hover::before {
                        opacity: 1;
                    }

                    .grimba-hero__kicker {
                        display: inline-block;
                        margin-bottom: 6px;
                        padding: 4px 10px;
                        border-radius: 999px;
                        background: rgba(255, 255, 255, .92);
                        color: #1a1713;
                        font-family: 'Public Sans', system-ui, sans-serif;
                        font-size: 10px;
                        font-weight: 800;
                        letter-spacing: .14em;
                        text-transform: uppercase;
                        box-shadow: 0 4px 14px rgba(26, 23, 19, .22);
                    }

                    .grimba-hero__bias-strip {
                        position: absolute;
                        left: 0;
                        bottom: 0;
                        z-index: 2;
                        width: 100%;
                        height: 4px;
                        background: linear-gradient(
                            90deg,
                            transparent 0%,
                            var(--gn-hero-bias, #a8a8a8) 12%,
                            color-mix(in srgb, var(--gn-hero-bias, #a8a8a8) 60%, #fff) 50%,
                            var(--gn-hero-bias, #a8a8a8) 88%,
                            transparent 100%
                        );
                        background-size: 220% 100%;
                        animation: grimbaHeroStripFlow 8s ease-in-out infinite alternate;
                        pointer-events: none;
                    }

                    @keyframes grimbaHeroStripFlow {
                        0% { background-position: 0% 0; }
                        100% { background-position: 100% 0; }
                    }

                    @media (prefers-reduced-motion: reduce) {
                        .grimba-hero__media--cinematic,
                        .grimba-hero__media--cinematic::before,
                        .grimba-hero__bias-strip {
                            transition: none;
                            animation: none;
                        }
                    }
                </style>
            @endif

            {{-- Top News Stories stack --}}
            @include(Theme::getThemeNamespace('partials.home.top-news-inline'))
        </section>

        {{-- Right: Blindspot sidebar --}}
        <aside class="col-xl-3 col-lg-12 col-12 grimba-blindspot-rail">
            <header class="grimba-blindspot-rail__head">
                <span class="grimba-blindspot-rail__kicker">
                    <span class="blindspot-badge blindspot-badge--ghost">{{ __('Angles morts') }}</span>
                </span>
                <p class="grimba-blindspot-rail__desc">
                    {{ __('Histoires couvertes de manière disproportionnée par un seul côté du spectre politique.') }}
                    <a href="{{ url('/angles-morts') }}">{{ __('En savoir plus') }}</a>
                </p>
            </header>

            @foreach($blindspots as $b)
                @php
                    $blindTitle = GnTr::title($b);
                    $blindTranslated = GnTr::isTranslated($b);
                @endphp
                <a href="{{ $b->url }}" class="grimba-blind-card">
                    <div class="grimba-blind-card__media">
                        {!! Theme::partial('post-hero-img', ['post' => $b, 'size' => 'medium']) !!}
                    </div>
                    <div class="grimba-blind-card__body">
                        <span class="grimba-blind-card__tag">
                            <span class="blindspot-badge blindspot-badge--on-dark">{{ __('Angle mort') }}</span>
                        </span>
                        <h3 class="grimba-blind-card__title">{{ $blindTitle }}</h3>
                        @if($blindTranslated)
                            {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                        @endif
                        {!! Theme::partial('home.coverage-bar', ['post' => $b, 'compact' => true, 'onDark' => true]) !!}
                    </div>
                </a>
            @endforeach

            <a href="{{ url('/angles-morts') }}" class="grimba-blindspot-rail__more">
                {{ __('Voir le fil des angles morts') }} →
            </a>

            {!! Theme::partial('bias-mix', ['variant' => 'compact']) !!}
        </aside>

    </div>
</section>
