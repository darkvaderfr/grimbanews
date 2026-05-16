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

            <ol class="grimba-briefing__list">
                @foreach($briefing->take(5) as $p)
                    @php
                        $title = GnTr::title($p);
                        $isTranslated = GnTr::isTranslated($p);
                        $publishedAt = GnTr::publishedAt($p);
                    @endphp
                    <li class="grimba-briefing__item">
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
                @endphp
                <a href="{{ $hero->url }}" class="grimba-hero__media">
                    {!! Theme::partial('post-hero-img', ['post' => $hero, 'size' => 'extra-large', 'eager' => true]) !!}
                    <div class="grimba-hero__gradient"></div>
                    <div class="grimba-hero__text">
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
