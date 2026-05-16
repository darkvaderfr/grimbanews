@php
    /**
     * Dossier Voices — Steve-led reinvention (Vader 2026-05-16).
     *
     * Replaces the old source-drilldown + article-list double card-grid
     * with a single, intentional surface:
     *   1. Three large quote panels (Gauche / Centre / Droite) showing
     *      each side's best framing as a single representative voice.
     *   2. A slim "Toutes les sources" table — one row per remaining
     *      contributing post, no cards, no excerpts. Source, country,
     *      bias chip, headline link.
     *
     * The point: a dossier is one story told from many angles, not
     * twelve card-shaped restatements of the same headline.
     *
     * @var \Illuminate\Support\Collection $clusterPosts
     * @var \Botble\Blog\Models\Post       $currentPost
     * @var \Illuminate\Support\Collection $sourceMeta
     */

    use App\Support\GrimbaDossierVoices;
    use App\Support\GrimbaSourceBreakdown;
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Botble\Blog\Models\Post as BlogPost;

    $voices = GrimbaDossierVoices::build($clusterPosts ?? collect(), $currentPost, $sourceMeta);

    $biasMeta = [
        'left' => ['label' => __('Gauche'),  'color' => '#3b82f6'],
        'center' => ['label' => __('Centre'), 'color' => '#a8a8a8'],
        'right' => ['label' => __('Droite'),  'color' => '#e84c3d'],
    ];

    // Slug map so "Lire chez la source" links resolve to the dossier
    // version on GrimbaNews, not the publisher URL (publisher link is
    // available from inside the article reader).
    $__voicePostIds = collect([
        $voices['left']['post'] ?? null,
        $voices['center']['post'] ?? null,
        $voices['right']['post'] ?? null,
    ])
        ->concat($voices['others'])
        ->filter()
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $__voiceUrls = $__voicePostIds->isEmpty()
        ? collect()
        : \Illuminate\Support\Facades\DB::table('slugs')
            ->whereIn('reference_id', $__voicePostIds->all())
            ->where('reference_type', BlogPost::class)
            ->whereIn('prefix', ['article', 'blog'])
            ->orderByRaw("CASE prefix WHEN 'article' THEN 0 ELSE 1 END")
            ->get(['reference_id', 'key'])
            ->unique('reference_id')
            ->mapWithKeys(fn ($slug) => [(int) $slug->reference_id => url('/article/' . $slug->key)]);

    $voiceUrl = static function (?BlogPost $post) use ($__voiceUrls): ?string {
        if (! $post) return null;
        return $__voiceUrls->get((int) $post->id) ?: ($post->url ?? null);
    };

    $totalVoices = ($voices['totals']['left'] ?? 0)
        + ($voices['totals']['center'] ?? 0)
        + ($voices['totals']['right'] ?? 0)
        + ($voices['totals']['unknown'] ?? 0);
@endphp

<section class="grimba-voices" aria-labelledby="grimba-voices-title">
    <header class="grimba-voices__head">
        <span class="grimba-voices__kicker">{{ __('Trois angles, une histoire') }}</span>
        <h2 id="grimba-voices-title" class="grimba-voices__title">
            {{ __('Comment chaque camp cadre cette histoire') }}
        </h2>
        <p class="grimba-voices__lede">
            {{ __('Un seul angle représentatif par camp éditorial. Toutes les autres sources sont listées plus bas, sans répétition de carte.') }}
        </p>
    </header>

    <div class="grimba-voices__grid">
        @foreach(['left', 'center', 'right'] as $side)
            @php
                $voice = $voices[$side];
                $sideColor = $biasMeta[$side]['color'];
                $sideLabel = $biasMeta[$side]['label'];
                $sideCount = (int) ($voices['totals'][$side] ?? 0);
            @endphp
            <article class="grimba-voices__panel grimba-voices__panel--{{ $side }}"
                     style="--voice-color: {{ $sideColor }};">
                <header class="grimba-voices__panel-head">
                    <span class="grimba-voices__side-dot" aria-hidden="true"></span>
                    <strong class="grimba-voices__side-label">{{ $sideLabel }}</strong>
                    <span class="grimba-voices__side-count">
                        {{ trans_choice(':count source|:count sources', $sideCount, ['count' => $sideCount]) }}
                    </span>
                </header>

                @if($voice)
                    @php
                        $url = $voiceUrl($voice['post']);
                        $sourceName = trim((string) ($voice['post']->source_name ?? '')) ?: __('Source');
                        $country = $voice['country'] ?? null;
                        $title = GnTr::title($voice['post']);
                        $isTranslated = GnTr::isTranslated($voice['post']);
                    @endphp
                    <blockquote class="grimba-voices__quote">
                        <p class="grimba-voices__quote-text">
                            “{{ $voice['excerpt'] }}”
                        </p>
                        @if($isTranslated)
                            <div class="grimba-voices__chip-row">
                                {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
                            </div>
                        @endif
                    </blockquote>

                    <footer class="grimba-voices__byline">
                        <span class="grimba-voices__source">{{ $sourceName }}</span>
                        @if($country)
                            <span class="grimba-voices__country">{{ strtoupper($country) }}</span>
                        @endif
                    </footer>

                    <a href="{{ $url }}" class="grimba-voices__cta">
                        {{ __('Lire ce cadrage') }} →
                    </a>

                    @if($sideCount > 1)
                        <span class="grimba-voices__more-note">
                            {{ trans_choice('+:count autre source|+:count autres sources', $sideCount - 1, ['count' => $sideCount - 1]) }} {{ __('plus bas') }}
                        </span>
                    @endif
                @else
                    <div class="grimba-voices__absent">
                        <span class="grimba-voices__absent-mark" aria-hidden="true">∅</span>
                        <p>{{ __(':side n\'a pas couvert cette histoire.', ['side' => $sideLabel]) }}</p>
                        <span class="grimba-voices__absent-hint">{{ __('Couverture asymétrique') }}</span>
                        @include(Theme::getThemeNamespace('partials.info-pill'), [
                            'size' => 'sm',
                            'tone' => 'soft',
                            'body' => __("Aucune source de ce camp n'a publié sur ce dossier. C'est ce qu'on appelle un angle mort — un signal éditorial en soi, pas un bug d'affichage."),
                        ])
                    </div>
                @endif
            </article>
        @endforeach
    </div>

    @if($voices['others']->isNotEmpty())
        <section class="grimba-voices__table-wrap" aria-labelledby="grimba-voices-table-title">
            <header class="grimba-voices__table-head">
                <h3 id="grimba-voices-table-title" class="grimba-voices__table-title">
                    {{ __('Toutes les sources contributrices') }}
                </h3>
                <span class="grimba-voices__table-count">
                    {{ trans_choice(':count source|:count sources', $voices['others']->count(), ['count' => $voices['others']->count()]) }}
                </span>
            </header>

            <ol class="grimba-voices__table">
                @foreach($voices['others'] as $other)
                    @php
                        $bucket = isset($biasMeta[$other->bias_rating ?? '']) ? $other->bias_rating : 'unknown';
                        $color = $biasMeta[$bucket]['color'] ?? '#6b6459';
                        $label = $biasMeta[$bucket]['label'] ?? __('Non classé');
                        $title = GnTr::title($other);
                        $url = $voiceUrl($other);
                        $country = null;
                        if ($sourceMeta && $other->source_id && isset($sourceMeta[$other->source_id])) {
                            $country = $sourceMeta[$other->source_id]->country ?? null;
                        }
                        $sourceLabel = trim((string) ($other->source_name ?? '')) ?: __('Source');
                    @endphp
                    <li class="grimba-voices__row" style="--row-color: {{ $color }};">
                        <span class="grimba-voices__row-bias" title="{{ $label }}" aria-label="{{ $label }}"></span>
                        <span class="grimba-voices__row-source">{{ $sourceLabel }}</span>
                        @if($country)
                            <span class="grimba-voices__row-country">{{ strtoupper($country) }}</span>
                        @else
                            <span class="grimba-voices__row-country grimba-voices__row-country--empty">·</span>
                        @endif
                        <a href="{{ $url ?: '#' }}" class="grimba-voices__row-headline">{{ $title }}</a>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif
</section>

<style>
    .grimba-voices {
        margin: 18px 0 24px;
    }

    .grimba-voices__head {
        margin-bottom: 18px;
    }

    .grimba-voices__kicker {
        display: inline-block;
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .6));
        margin-bottom: 6px;
    }

    .grimba-voices__title {
        margin: 0;
        font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
        font-weight: 800;
        font-size: clamp(22px, 2.4vw, 30px);
        line-height: 1.08;
        letter-spacing: -0.02em;
        color: var(--gn-ink, #1a1713);
    }

    .grimba-voices__lede {
        margin: 8px 0 0;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
        font-size: 14px;
        line-height: 1.5;
        max-width: 60ch;
    }

    .grimba-voices__grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .grimba-voices__panel {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 20px 20px 18px;
        border-radius: 20px;
        border: 1px solid color-mix(in srgb, var(--voice-color, #a8a8a8) 22%, rgba(26, 23, 19, .08));
        background:
            radial-gradient(80% 60% at 0% 0%, color-mix(in srgb, var(--voice-color, #a8a8a8) 14%, transparent), transparent 65%),
            rgba(255, 255, 255, .68);
        box-shadow: 0 18px 44px rgba(26, 23, 19, .08);
        overflow: hidden;
        transition: transform .3s cubic-bezier(.22, 1, .36, 1), box-shadow .3s ease;
    }

    .grimba-voices__panel::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(
            180deg,
            transparent 0%,
            var(--voice-color, #a8a8a8) 20%,
            color-mix(in srgb, var(--voice-color, #a8a8a8) 70%, #fff) 50%,
            var(--voice-color, #a8a8a8) 80%,
            transparent 100%
        );
        pointer-events: none;
    }

    .grimba-voices__panel:hover {
        transform: translateY(-3px);
        box-shadow: 0 24px 58px rgba(26, 23, 19, .14);
    }

    .grimba-voices__panel-head {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .grimba-voices__side-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--voice-color, #a8a8a8);
        box-shadow: 0 0 0 2px rgba(255, 255, 255, .6), 0 0 10px color-mix(in srgb, var(--voice-color, #a8a8a8) 50%, transparent);
    }

    .grimba-voices__side-label {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 800;
        font-size: 18px;
        letter-spacing: -0.01em;
        color: var(--voice-color, var(--gn-ink, #1a1713));
    }

    .grimba-voices__side-count {
        margin-left: auto;
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
    }

    .grimba-voices__quote {
        margin: 0;
        padding: 0;
        border: none;
    }

    .grimba-voices__quote-text {
        margin: 0;
        font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
        font-weight: 500;
        font-size: 17px;
        line-height: 1.42;
        letter-spacing: -0.005em;
        color: var(--gn-ink, #1a1713);
    }

    .grimba-voices__chip-row {
        margin-top: 8px;
    }

    .grimba-voices__byline {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Public Sans', system-ui, sans-serif;
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .grimba-voices__source {
        color: var(--gn-ink, #1a1713);
    }

    .grimba-voices__country {
        padding: 2px 6px;
        border-radius: 4px;
        background: rgba(26, 23, 19, .06);
        color: var(--gn-ink-muted, rgba(26, 23, 19, .62));
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 10px;
        letter-spacing: .12em;
    }

    .grimba-voices__cta {
        display: inline-flex;
        align-items: center;
        align-self: flex-start;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 999px;
        background: var(--voice-color, #1a1713);
        color: #fffaf0;
        font-family: 'Public Sans', system-ui, sans-serif;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .02em;
        text-decoration: none;
        transition: transform .2s ease, filter .2s ease;
    }

    .grimba-voices__cta:hover,
    .grimba-voices__cta:focus-visible {
        color: #fffaf0;
        transform: translateX(2px);
        filter: brightness(1.08) saturate(1.06);
        text-decoration: none;
    }

    .grimba-voices__more-note {
        font-family: 'Public Sans', system-ui, sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .03em;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .55));
    }

    /* Absent voice — show the coverage gap as an editorial signal, not
       an empty card. This is part of GrimbaNews' value proposition. */
    .grimba-voices__absent {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 18px 0 6px;
        text-align: center;
    }

    .grimba-voices__absent-mark {
        align-self: center;
        font-family: 'Fraunces', Georgia, serif;
        font-size: 38px;
        font-weight: 300;
        color: var(--voice-color, #a8a8a8);
        opacity: .42;
        line-height: 1;
    }

    .grimba-voices__absent p {
        margin: 0;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
        font-size: 13.5px;
        line-height: 1.45;
    }

    .grimba-voices__absent-hint {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
        color: var(--voice-color, #a8a8a8);
        opacity: .75;
    }

    /* Slim contributing-sources table — one row, no cards. Carries
       the same editorial gradient ribbon + glass background as the
       SOURCE / EXCERPT / Insights cards above. */
    .grimba-voices__table-wrap {
        position: relative;
        overflow: hidden;
        margin-top: 28px;
        padding: 22px 24px 18px;
        border-radius: 16px;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.72), rgba(246, 241, 232, 0.56)),
            rgba(255, 255, 255, 0.62);
        border: 1px solid rgba(26, 23, 19, .08);
        box-shadow:
            inset 0 0 0 1px rgba(255, 255, 255, 0.18),
            0 20px 52px rgba(26, 23, 19, 0.075);
    }
    .grimba-voices__table-wrap::before {
        content: "";
        position: absolute;
        top: 0;
        left: 1rem;
        right: 1rem;
        height: 3px;
        pointer-events: none;
        background: linear-gradient(90deg, transparent, rgba(192, 57, 43, 0.52), rgba(59, 130, 246, 0.42), transparent);
    }
    .grimba-voices__table-wrap > * {
        position: relative;
        z-index: 1;
    }

    .grimba-voices__table-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }

    .grimba-voices__table-title {
        margin: 0;
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        font-size: 16px;
        letter-spacing: -0.01em;
        color: var(--gn-ink, #1a1713);
    }

    .grimba-voices__table-count {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
    }

    .grimba-voices__table {
        margin: 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
    }

    .grimba-voices__row {
        display: grid;
        grid-template-columns: 8px minmax(0, 140px) 36px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        padding: 8px 4px;
        border-bottom: 1px solid rgba(26, 23, 19, .06);
        transition: background .18s ease;
    }

    .grimba-voices__row:last-child {
        border-bottom: none;
    }

    .grimba-voices__row:hover {
        background: rgba(255, 255, 255, .82);
    }

    .grimba-voices__row-bias {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--row-color, #6b6459);
        box-shadow: 0 0 0 1px rgba(255, 255, 255, .5);
    }

    .grimba-voices__row-source {
        font-family: 'Public Sans', system-ui, sans-serif;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--gn-ink, #1a1713);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .grimba-voices__row-country {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .12em;
        color: var(--gn-ink-muted, rgba(26, 23, 19, .55));
    }

    .grimba-voices__row-country--empty {
        opacity: .25;
    }

    .grimba-voices__row-headline {
        color: var(--gn-ink, #1a1713);
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 500;
        font-size: 14.5px;
        line-height: 1.32;
        letter-spacing: -0.005em;
        text-decoration: none;
        transition: color .18s ease;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .grimba-voices__row-headline:hover {
        color: var(--row-color, var(--gn-ink, #1a1713));
        text-decoration: underline;
        text-decoration-thickness: 1px;
        text-underline-offset: 2px;
    }

    /* Dark-mode parity */
    [data-bs-theme="dark"] .grimba-voices__title,
    body[data-theme="dark"] .grimba-voices__title,
    [data-bs-theme="dark"] .grimba-voices__quote-text,
    body[data-theme="dark"] .grimba-voices__quote-text,
    [data-bs-theme="dark"] .grimba-voices__source,
    body[data-theme="dark"] .grimba-voices__source,
    [data-bs-theme="dark"] .grimba-voices__table-title,
    body[data-theme="dark"] .grimba-voices__table-title,
    [data-bs-theme="dark"] .grimba-voices__row-source,
    body[data-theme="dark"] .grimba-voices__row-source,
    [data-bs-theme="dark"] .grimba-voices__row-headline,
    body[data-theme="dark"] .grimba-voices__row-headline {
        color: #fffaf0;
    }

    [data-bs-theme="dark"] .grimba-voices__panel,
    body[data-theme="dark"] .grimba-voices__panel {
        background:
            radial-gradient(80% 60% at 0% 0%, color-mix(in srgb, var(--voice-color, #a8a8a8) 22%, transparent), transparent 65%),
            rgba(28, 24, 17, .82);
    }

    [data-bs-theme="dark"] .grimba-voices__table-wrap,
    body[data-theme="dark"] .grimba-voices__table-wrap {
        background: rgba(28, 24, 17, .58);
        border-color: rgba(255, 250, 240, .12);
    }

    [data-bs-theme="dark"] .grimba-voices__row,
    body[data-theme="dark"] .grimba-voices__row {
        border-color: rgba(255, 250, 240, .10);
    }

    [data-bs-theme="dark"] .grimba-voices__row:hover,
    body[data-theme="dark"] .grimba-voices__row:hover {
        background: rgba(255, 250, 240, .06);
    }

    [data-bs-theme="dark"] .grimba-voices__country,
    body[data-theme="dark"] .grimba-voices__country {
        background: rgba(255, 250, 240, .10);
        color: rgba(255, 250, 240, .72);
    }

    /* Responsive collapse */
    @media (max-width: 991.98px) {
        .grimba-voices__grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .grimba-voices__row {
            grid-template-columns: 8px 1fr;
            gap: 8px;
        }

        .grimba-voices__row-source,
        .grimba-voices__row-country {
            grid-column: 2;
        }

        .grimba-voices__row-headline {
            grid-column: 1 / -1;
            -webkit-line-clamp: 3;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .grimba-voices__panel,
        .grimba-voices__cta,
        .grimba-voices__row {
            transition: none;
        }

        .grimba-voices__panel:hover {
            transform: none;
        }
    }
</style>
