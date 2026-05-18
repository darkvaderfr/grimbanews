@php
    /* Vader 2026-05-16 Wave I — see breaking.blade.php for the same fix
       rationale (double <html>/<body> from @extends-pattern). */
    Theme::layout('grimba-chrome');

    use App\Support\GrimbaTranslationPresenter as GnTr;
    use App\Ground\Regions;
    use Illuminate\Support\Str;

    $regionLabel = Regions::label(
        Regions::migrate((string) request()->cookie(\App\Scopes\GrimbaRegionScope::COOKIE_NAME, 'international'))
    );
@endphp

    <section class="grimba-latest-page container py-4 py-md-5">
        <header class="grimba-latest-page__head">
            <span class="grimba-latest-page__kicker">
                {{ __('Flux frais') }} · {{ $regionLabel }}
            </span>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h1 class="grimba-latest-page__title mb-0">{{ __('Dernières histoires') }}</h1>
                @include(Theme::getThemeNamespace('partials.info-pill'), [
                    'size' => 'sm',
                    'body' => __("Flux chronologique strict, tous camps confondus. Pour filtrer par camp politique, ouvrez un dossier individuel — chaque dossier porte sa propre répartition Gauche / Centre / Droite."),
                ])
            </div>
            <p class="grimba-latest-page__lede">
                {{ __('Les 40 articles les plus récents pour votre édition et votre langue, triés par date de publication.') }}
            </p>
        </header>

        @if($posts->isEmpty())
            <div class="grimba-latest-page__empty">
                <p>{{ __('Aucune actualité fraîche pour cette édition.') }}</p>
                <a href="{{ url('/') }}" class="grimba-latest-page__cta">{{ __('Retour à la home') }} →</a>
            </div>
        @else
            <ol class="grimba-latest-page__list">
                @foreach($posts as $index => $post)
                    @php
                        $title = GnTr::title($post);
                        $excerpt = trim(strip_tags((string) GnTr::description($post)));
                        $excerpt = Str::limit($excerpt, 220);
                        $publishedAt = GnTr::publishedAt($post);
                        $primaryCategory = $post->categories?->first();
                    @endphp
                    <li class="grimba-latest-page__item">
                        <span class="grimba-latest-page__rank" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <article class="grimba-latest-page__body">
                            <header class="grimba-latest-page__meta">
                                {{-- S-CAT-02b — primaryTopicFor() badge replaces the raw
                                     "first attached category" link that often surfaced
                                     a regional bin (Europe/Afrique) instead of the topic. --}}
                                @include(Theme::getThemeNamespace('partials.cards.category-badge'), ['post' => $post, 'size' => 'sm'])
                                @if($post->source_name)
                                    <span class="grimba-latest-page__source">{{ $post->source_name }}</span>
                                @endif
                                {!! Theme::partial('country-pill', ['post' => $post]) !!}
                                @if($publishedAt)
                                    <span class="grimba-latest-page__time">{{ $publishedAt->locale(app()->getLocale())->diffForHumans() }}</span>
                                @endif
                            </header>
                            <h2 class="grimba-latest-page__headline">
                                <a href="{{ $post->url }}">{{ $title }}</a>
                            </h2>
                            @if($excerpt !== '')
                                <p class="grimba-latest-page__excerpt">{{ $excerpt }}</p>
                            @endif
                        </article>
                    </li>
                @endforeach
            </ol>
        @endif

        {{-- S-LSAT-05 — tail expander (opposite-locale prompt). --}}
        @include(Theme::getThemeNamespace('partials.lang.tail-expander'), ['surface' => 'latest', 'hours' => 72])
    </section>

    <style>
        .grimba-latest-page {
            max-width: 960px;
            margin-inline: auto;
        }
        .grimba-latest-page__head {
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
        .grimba-latest-page__head::before {
            content: "";
            position: absolute;
            top: 0;
            left: 1rem;
            right: 1rem;
            height: 3px;
            pointer-events: none;
            background: linear-gradient(90deg, transparent, rgba(192, 57, 43, 0.52), rgba(59, 130, 246, 0.42), transparent);
        }
        .grimba-latest-page__head > * {
            position: relative;
            z-index: 1;
        }
        .grimba-latest-page__kicker {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(26, 23, 19, .06);
            border: 1px solid rgba(26, 23, 19, .12);
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .62));
            margin-bottom: 12px;
        }
        .grimba-latest-page__title {
            margin: 0 0 8px;
            font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
            font-weight: 800;
            font-size: clamp(28px, 4.4vw, 48px);
            line-height: 1.05;
            letter-spacing: -0.025em;
            color: var(--gn-ink, #1a1713);
        }
        .grimba-latest-page__lede {
            max-width: 60ch;
            margin: 0;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
        }
        .grimba-latest-page__list {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
        }
        .grimba-latest-page__item {
            display: grid;
            grid-template-columns: 56px minmax(0, 1fr);
            gap: 14px;
            align-items: start;
            padding: 18px 0;
            border-bottom: 1px solid rgba(26, 23, 19, .08);
        }
        .grimba-latest-page__item:last-child {
            border-bottom: none;
        }
        .grimba-latest-page__rank {
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            font-size: 28px;
            line-height: 1;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .42));
            text-align: right;
            padding-top: 4px;
        }
        .grimba-latest-page__body {
            min-width: 0;
        }
        .grimba-latest-page__meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 6px;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .62));
        }
        .grimba-latest-page__category {
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(192, 57, 43, .12);
            color: #c0392b;
            text-decoration: none;
            font-weight: 700;
        }
        .grimba-latest-page__category:hover {
            background: rgba(192, 57, 43, .22);
            color: #c0392b;
        }
        .grimba-latest-page__source {
            font-weight: 600;
        }
        .grimba-latest-page__time {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .04em;
            opacity: .76;
        }
        .grimba-latest-page__headline {
            margin: 0 0 4px;
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 700;
            font-size: 20px;
            line-height: 1.22;
            letter-spacing: -0.01em;
        }
        .grimba-latest-page__headline a {
            color: var(--gn-ink, #1a1713);
            text-decoration: none;
        }
        .grimba-latest-page__headline a:hover {
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }
        .grimba-latest-page__excerpt {
            margin: 0;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
        }
        .grimba-latest-page__empty {
            text-align: center;
            padding: 56px 24px;
            border: 1px dashed rgba(26, 23, 19, .14);
            border-radius: 18px;
            background: rgba(255, 255, 255, .58);
        }
        .grimba-latest-page__cta {
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
            margin-top: 12px;
        }

        [data-bs-theme="dark"] .grimba-latest-page__title,
        body[data-theme="dark"] .grimba-latest-page__title,
        [data-bs-theme="dark"] .grimba-latest-page__headline a,
        body[data-theme="dark"] .grimba-latest-page__headline a {
            color: #fffaf0;
        }
        [data-bs-theme="dark"] .grimba-latest-page__item,
        body[data-theme="dark"] .grimba-latest-page__item {
            border-bottom-color: rgba(255, 250, 240, .10);
        }
    </style>
