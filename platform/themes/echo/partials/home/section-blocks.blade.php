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
        <header class="grimba-section__head grimba-section__head--editorial">
            <div class="grimba-section__head-left">
                <span class="grimba-section__eyebrow">{{ __('Rubrique') }}</span>
                <h2 class="grimba-section__title grimba-section__title--editorial">{{ $cat->name }}</h2>
            </div>
            <span class="grimba-section__rule" aria-hidden="true"></span>
            <div class="grimba-section__head-actions">
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

<style>
    .grimba-section__head--editorial {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: end;
        gap: 16px;
        margin-bottom: 20px;
    }

    .grimba-section__head-left {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .grimba-section__eyebrow {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .56));
    }

    .grimba-section__title--editorial {
        margin: 0;
        font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
        font-weight: 800;
        font-size: clamp(20px, 2.2vw, 28px);
        line-height: 1.05;
        letter-spacing: -0.02em;
        color: var(--gn-ink, #1a1713);
    }

    .grimba-section__rule {
        position: relative;
        height: 1px;
        background: linear-gradient(
            90deg,
            rgba(26, 23, 19, .14) 0%,
            rgba(26, 23, 19, .04) 100%
        );
        align-self: center;
        overflow: hidden;
    }

    .grimba-section__rule::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(
            90deg,
            transparent 0%,
            rgba(255, 255, 255, .8) 50%,
            transparent 100%
        );
        transform: translateX(-100%);
        animation: grimbaSectionShimmer 6s ease-in-out infinite;
    }

    @keyframes grimbaSectionShimmer {
        0% { transform: translateX(-100%); }
        65% { transform: translateX(100%); }
        100% { transform: translateX(100%); }
    }

    .grimba-section__head-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    [data-bs-theme="dark"] .grimba-section__rule,
    body[data-theme="dark"] .grimba-section__rule {
        background: linear-gradient(
            90deg,
            rgba(255, 250, 240, .18) 0%,
            rgba(255, 250, 240, .05) 100%
        );
    }

    [data-bs-theme="dark"] .grimba-section__title--editorial,
    body[data-theme="dark"] .grimba-section__title--editorial {
        color: #fffaf0;
    }

    @media (max-width: 767.98px) {
        .grimba-section__head--editorial {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .grimba-section__rule {
            display: none;
        }

        .grimba-section__head-actions {
            grid-column: 1 / -1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .grimba-section__rule::after {
            animation: none;
        }
    }
</style>
