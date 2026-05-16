@php
    use App\Support\GrimbaHomeFeed;
    use App\Support\GrimbaTranslationPresenter as GnTr;

    $__sectionBlocks = GrimbaHomeFeed::sections();
@endphp

@foreach($__sectionBlocks as $__section)
    @php
        $cat = $__section['category'];
        $latest = $__section['latest'];
        $categoryBlindspots = $__section['blindspots'];

        GnTr::warm(collect([$latest])->filter()->concat($categoryBlindspots));
    @endphp

    <section class="grimba-section mt-5">
        <header class="grimba-section__head d-flex justify-content-between align-items-center mb-3">
            <h2 class="grimba-section__title">{{ $cat->name }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ $cat->url }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Suivre') }}</a>
                <a href="{{ $cat->url }}" class="btn-grimba btn-grimba--ghost btn-grimba--sm">{{ __('Voir tout') }}</a>
            </div>
        </header>

        <div class="row g-4">
            <div class="col-lg-7 col-12">
                @if($latest)
                    @php
                        $latestTitle = GnTr::title($latest);
                        $latestTranslated = GnTr::isTranslated($latest);
                    @endphp
                    <a href="{{ $latest->url }}" class="grimba-section__hero">
                        {!! Theme::partial('post-hero-img', ['post' => $latest, 'size' => 'extra-large']) !!}
                        <div class="grimba-section__hero-body">
                            <span class="grimba-section__kicker">{{ __('Dernières :category', ['category' => strtolower($cat->name)]) }}</span>
                            <h3 class="grimba-section__hero-title">{{ $latestTitle }}</h3>
                            @if($latestTranslated)
                                {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                            @endif
                            {!! Theme::partial('home.coverage-bar', ['post' => $latest, 'compact' => false]) !!}
                        </div>
                    </a>
                @endif
            </div>

            <div class="col-lg-5 col-12 d-flex flex-column gap-3">
                <span class="grimba-section__kicker grimba-section__kicker--rail">{{ __('Angles morts') }} · {{ $cat->name }}</span>

                @foreach($categoryBlindspots as $b)
                    @php
                        $blindTitle = GnTr::title($b);
                        $blindTranslated = GnTr::isTranslated($b);
                    @endphp
                    <a href="{{ $b->url }}" class="grimba-blind-card grimba-blind-card--wide">
                        <div class="grimba-blind-card__media">
                            {!! Theme::partial('post-hero-img', ['post' => $b, 'size' => 'medium']) !!}
                        </div>
                        <div class="grimba-blind-card__body">
                            <span class="blindspot-badge blindspot-badge--on-dark">{{ __('Angle mort') }}</span>
                            <h4 class="grimba-blind-card__title">{{ $blindTitle }}</h4>
                            @if($blindTranslated)
                                {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                            @endif
                            {!! Theme::partial('home.coverage-bar', ['post' => $b, 'compact' => true, 'onDark' => true]) !!}
                        </div>
                    </a>
                @endforeach

                <form class="grimba-blind-subscribe" method="POST" action="{{ route('public.newsletter.subscribe') }}">
                    @csrf
                    <input type="hidden" name="source_key" value="section_blindspot_{{ \Illuminate\Support\Str::slug($cat->name) }}">
                    <span class="blindspot-badge blindspot-badge--on-dark">{{ __('Newsletter angles morts') }}</span>
                    <p class="small mb-2">{{ __('Recevez chaque semaine les histoires ignorées par votre camp.') }}</p>
                    <div class="d-flex gap-2">
                        <input type="email" name="email" required placeholder="{{ __('Adresse e-mail') }}" aria-label="{{ __('Adresse e-mail') }}">
                        <button type="submit" class="btn-grimba btn-grimba--solid btn-grimba--sm">{{ __("S'inscrire") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endforeach
