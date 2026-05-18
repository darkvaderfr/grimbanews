@php
    /*
     * Vader 2026-05-16 Wave I — converted from `@extends(...)` /
     * `@section('content')` pattern to `Theme::layout()` pattern. The
     * @extends path was producing a complete <html><body>...</body></html>
     * doc, which Botble's master layout then wrapped in ANOTHER complete
     * doc → double <html>/<body>/<footer> rendering ("iframe" feel).
     * The Theme::layout() pattern lets Botble's master pull this view's
     * body via Theme::content() — single doc.
     */
    Theme::layout('grimba-chrome');

    use App\Support\GrimbaTranslationPresenter as GnTr;
    use App\Ground\Regions;
    use Illuminate\Support\Str;

    $regionLabel = Regions::label(
        Regions::migrate((string) request()->cookie(\App\Scopes\GrimbaRegionScope::COOKIE_NAME, 'international'))
    );
    $isReal = ($mode ?? 'latest') === 'real';
@endphp

    <section class="grimba-breaking-page container py-4 py-md-5">
        <header class="grimba-breaking-page__head">
            <span class="grimba-breaking-page__kicker grimba-breaking-page__kicker--{{ $isReal ? 'live' : 'latest' }}">
                <span class="grimba-breaking-page__pulse" aria-hidden="true"></span>
                @if($isReal)
                    {{ __('En direct') }}
                @else
                    {{ __('Dernières') }}
                @endif
                · {{ $regionLabel }}
            </span>
            {{-- Zen audit 2026-05-17: pill rendered as a SIBLING of the
                 heading, not a child. <details> inside <h1>/<h2>
                 pollutes the accessible heading name. --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h1 class="grimba-breaking-page__title mb-0">
                    @if($isReal)
                        {{ __('Breaking news en cours') }}
                    @else
                        {{ __('Pas de breaking news pour l’instant') }}
                    @endif
                </h1>
                @include(Theme::getThemeNamespace('partials.info-pill'), [
                    'size' => 'sm',
                    'body' => __("Live = histoires publiées dans la dernière heure portant les marqueurs éditoriaux 'breaking', 'urgence', 'dernière minute'. Latest = en cas de creux, on remonte aux 18 dernières heures pour ne jamais afficher une page vide."),
                ])
            </div>
            <p class="grimba-breaking-page__lede">
                @if($isReal)
                    {{ __('Histoires marquées « breaking news », « live updates », « état d\'urgence » et autres déclencheurs éditoriaux dans les 18 dernières heures.') }}
                @else
                    {{ __('Aucune histoire ne porte les marqueurs éditoriaux de breaking news dans la fenêtre actuelle. Voici les couvertures les plus fraîches à la place.') }}
                @endif
            </p>
        </header>

        @if($posts->isEmpty())
            <div class="grimba-breaking-page__empty">
                <span class="grimba-breaking-page__empty-mark" aria-hidden="true">∅</span>
                <p>{{ __('La couverture est calme dans cette édition.') }}</p>
                <a href="{{ url('/') }}" class="grimba-breaking-page__cta">{{ __('Retour à la home') }} →</a>
            </div>
        @else
            <ol class="grimba-breaking-page__list">
                @foreach($posts as $post)
                    @php
                        GnTr::warm($post);
                        $title = GnTr::title($post);
                        $excerpt = trim(strip_tags((string) GnTr::description($post)));
                        $excerpt = Str::limit($excerpt, 260);
                        $publishedAt = GnTr::publishedAt($post);
                        $bias = $post->bias_rating ?? 'unknown';
                        $biasColor = match ($bias) {
                            'left' => '#3b82f6',
                            'center' => '#a8a8a8',
                            'right' => '#e84c3d',
                            default => '#6b6459',
                        };
                    @endphp
                    <li class="grimba-breaking-page__item" style="--bp-color: {{ $biasColor }};">
                        <a href="{{ $post->url }}" class="grimba-breaking-page__link">
                            <header class="grimba-breaking-page__meta">
                                <span class="grimba-breaking-page__source">{{ $post->source_name ?: 'GrimbaNews' }}</span>
                                {!! Theme::partial('country-pill', ['post' => $post]) !!}
                                @if($publishedAt)
                                    <span class="grimba-breaking-page__time">{{ $publishedAt->locale(app()->getLocale())->diffForHumans() }}</span>
                                @endif
                            </header>
                            <h2 class="grimba-breaking-page__headline">{{ $title }}</h2>
                            @if($excerpt !== '')
                                <p class="grimba-breaking-page__excerpt">{{ $excerpt }}</p>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ol>
        @endif

        {{-- S-LSAT-05 — bottom-of-rail "X articles also available in
             [other locale]" ribbon. Hidden when no opposite-locale
             posts exist in the recency window OR the operator
             disabled the feature via the admin form. --}}
        @include(Theme::getThemeNamespace('partials.lang.tail-expander'), ['surface' => 'breaking', 'hours' => 24])
    </section>

    <style>
        .grimba-breaking-page {
            max-width: 920px;
            margin-inline: auto;
        }
        .grimba-breaking-page__head {
            position: relative;
            overflow: hidden;
            margin-bottom: 28px;
            padding: 24px 26px;
            border-radius: 16px;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.72), rgba(246, 241, 232, 0.56)),
                rgba(255, 255, 255, 0.62);
            border: 1px solid rgba(26, 23, 19, 0.08);
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.18),
                0 20px 52px rgba(26, 23, 19, 0.075);
        }
        .grimba-breaking-page__head::before {
            content: "";
            position: absolute;
            top: 0;
            left: 1rem;
            right: 1rem;
            height: 3px;
            pointer-events: none;
            background: linear-gradient(90deg, transparent, rgba(192, 57, 43, 0.52), rgba(59, 130, 246, 0.42), transparent);
        }
        .grimba-breaking-page__head > * {
            position: relative;
            z-index: 1;
        }
        .grimba-breaking-page__kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .grimba-breaking-page__kicker--live {
            background: #c0392b;
            color: #fffaf1;
            box-shadow: 0 8px 22px rgba(192, 57, 43, .28);
        }
        .grimba-breaking-page__kicker--latest {
            background: rgba(26, 23, 19, .08);
            color: var(--gn-ink, #1a1713);
            border: 1px solid rgba(26, 23, 19, .12);
        }
        .grimba-breaking-page__pulse {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 8px currentColor;
            animation: grimbaBreakingPagePulse 1.4s ease-in-out infinite;
        }
        .grimba-breaking-page__kicker--latest .grimba-breaking-page__pulse { animation: none; }
        @keyframes grimbaBreakingPagePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .35; transform: scale(.6); }
        }
        .grimba-breaking-page__title {
            margin: 0 0 8px;
            font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
            font-weight: 800;
            font-size: clamp(28px, 4.4vw, 48px);
            line-height: 1.05;
            letter-spacing: -0.025em;
            color: var(--gn-ink, #1a1713);
        }
        .grimba-breaking-page__lede {
            max-width: 60ch;
            margin: 0;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
        }
        .grimba-breaking-page__list {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .grimba-breaking-page__item {
            border-left: 4px solid var(--bp-color, #6b6459);
            border-radius: 0 14px 14px 0;
            background: rgba(255, 255, 255, .72);
            border-top: 1px solid rgba(26, 23, 19, .06);
            border-right: 1px solid rgba(26, 23, 19, .06);
            border-bottom: 1px solid rgba(26, 23, 19, .06);
            transition: transform .22s cubic-bezier(.22, 1, .36, 1), box-shadow .22s ease;
        }
        .grimba-breaking-page__item:hover {
            transform: translateX(3px);
            box-shadow: 0 12px 32px rgba(26, 23, 19, .08);
        }
        .grimba-breaking-page__link {
            display: block;
            padding: 16px 20px;
            color: var(--gn-ink, #1a1713);
            text-decoration: none;
        }
        .grimba-breaking-page__link:hover,
        .grimba-breaking-page__link:focus-visible {
            color: var(--gn-ink, #1a1713);
            text-decoration: none;
        }
        .grimba-breaking-page__meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 6px;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
        }
        .grimba-breaking-page__source {
            text-transform: uppercase;
            color: var(--bp-color, var(--gn-ink, #1a1713));
        }
        .grimba-breaking-page__time {
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 11.5px;
            text-transform: none;
            opacity: .72;
        }
        .grimba-breaking-page__headline {
            margin: 0 0 4px;
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 700;
            font-size: 19px;
            line-height: 1.25;
            letter-spacing: -0.01em;
            color: var(--gn-ink, #1a1713);
        }
        .grimba-breaking-page__excerpt {
            margin: 0;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
        }
        .grimba-breaking-page__empty {
            text-align: center;
            padding: 56px 24px;
            border: 1px dashed rgba(26, 23, 19, .14);
            border-radius: 18px;
            background: rgba(255, 255, 255, .58);
        }
        .grimba-breaking-page__empty-mark {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 48px;
            color: rgba(26, 23, 19, .25);
            line-height: 1;
        }
        .grimba-breaking-page__empty p {
            margin: 12px 0 16px;
            font-size: 15px;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
        }
        .grimba-breaking-page__cta {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 8px 18px;
            border-radius: 999px;
            background: #14110d;
            color: #fffaf1;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }
        .grimba-breaking-page__cta:hover {
            color: #fffaf1;
            text-decoration: none;
            filter: brightness(1.08);
        }

        [data-bs-theme="dark"] .grimba-breaking-page__item,
        body[data-theme="dark"] .grimba-breaking-page__item {
            background: rgba(28, 24, 17, .68);
            border-top-color: rgba(255, 250, 240, .08);
            border-right-color: rgba(255, 250, 240, .08);
            border-bottom-color: rgba(255, 250, 240, .08);
        }
        [data-bs-theme="dark"] .grimba-breaking-page__title,
        body[data-theme="dark"] .grimba-breaking-page__title,
        [data-bs-theme="dark"] .grimba-breaking-page__headline,
        body[data-theme="dark"] .grimba-breaking-page__headline,
        [data-bs-theme="dark"] .grimba-breaking-page__link,
        body[data-theme="dark"] .grimba-breaking-page__link {
            color: #fffaf0;
        }
    </style>
