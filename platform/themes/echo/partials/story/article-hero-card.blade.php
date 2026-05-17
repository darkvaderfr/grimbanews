@php
    /**
     * Article Hero Card — canonical layout per Vader 2026-05-16
     * screenshot. Every article / dossier page leads with the same
     * shape:
     *   1. Hero image (21:9)
     *   2. Meta line: ARTICLE · Read at <source> · <time> · ≈<min>
     *   3. Pill row: category + region + bias
     *   4. Big Fraunces title
     *   5. SOURCE card (publisher, country/lang, bias, credibility bar)
     *   6. AVAILABLE EXCERPT card (word count, body, original-source link)
     *
     * @var \Botble\Blog\Models\Post $post
     * @var \Illuminate\Support\Collection|null $sourceMeta
     */

    use App\Support\GrimbaTranslationPresenter as GnTr;
    use Illuminate\Support\Str;

    GnTr::warm($post);

    $__title = GnTr::title($post);
    $__desc = trim(strip_tags((string) GnTr::description($post)));
    $__publishedAt = GnTr::publishedAt($post);
    $__isTranslated = GnTr::isTranslated($post);

    // Hero image — same fallback chain as the legacy story hero.
    $__heroSrc = $post->image ?? null;
    $__heroResolved = $__heroSrc ? \Botble\Media\Facades\RvMedia::getImageUrl($__heroSrc) : null;
    $__heroDefault = \Botble\Media\Facades\RvMedia::getDefaultImage();
    $__heroUrl = ($__heroResolved && $__heroResolved !== $__heroDefault)
        ? $__heroResolved
        : route('public.og.placeholder', $post->id);

    // Source resolution from news_sources for credibility + ownership.
    $__sourceMetaRow = null;
    if (! empty($post->source_id)) {
        if (isset($sourceMeta) && $sourceMeta instanceof \Illuminate\Support\Collection && isset($sourceMeta[$post->source_id])) {
            $__sourceMetaRow = $sourceMeta[$post->source_id];
        } else {
            $__sourceMetaRow = \Illuminate\Support\Facades\DB::table('news_sources')
                ->where('id', $post->source_id)
                ->first(['id', 'name', 'website', 'country', 'language', 'credibility_score', 'bias_score', 'ownership_type', 'logo_url']);
        }
    }

    $__sourceName = $post->source_name
        ?: ($__sourceMetaRow->name ?? __('Source'));
    $__sourceCountry = strtoupper((string) ($__sourceMetaRow->country ?? $post->country ?? ''));
    $__sourceLang = strtoupper((string) ($__sourceMetaRow->language ?? $post->original_language ?? ''));
    $__credibility = (int) ($__sourceMetaRow->credibility_score ?? $post->credibility_score ?? 0);
    $__ownership = $__sourceMetaRow->ownership_type ?? $post->ownership_type ?? null;

    // Bias chip data.
    $__biasKey = $post->bias_rating ?? 'unknown';
    $__biasMeta = [
        'left' => ['label' => __('Gauche'), 'color' => '#3b82f6'],
        'center' => ['label' => __('Centre'), 'color' => '#a8a8a8'],
        'right' => ['label' => __('Droite'), 'color' => '#e84c3d'],
    ];
    $__biasLabel = $__biasMeta[$__biasKey]['label'] ?? __('Non classé');
    $__biasColor = $__biasMeta[$__biasKey]['color'] ?? '#6b6459';

    // Region pill — pull from the post's editorial_region tag.
    $__region = $post->editorial_region ?? null;
    $__regionLabel = $__region ? \App\Ground\Regions::label($__region) : null;

    // Reading-time estimate (200 wpm).
    $__readWords = max(1, str_word_count(strip_tags((string) ($post->content ?? $post->description ?? ''))));
    $__readMin = max(1, (int) ceil($__readWords / 200));

    // Sprint 12 — when the publisher API delivers the full article
    // body AND the reader has access AND the admin has the feature on,
    // swap the excerpt for the full body. Otherwise fall back to the
    // standard 400-char description excerpt.
    $__fullActive = (bool) (function_exists('setting') ? setting('grimba_full_article_active', true) : true);
    $__memberCanReadFull = (function_exists('is_plugin_active') && is_plugin_active('member') && auth('member')->check()) || auth()->check();
    $__fullPublic = (bool) (function_exists('setting') ? setting('grimba_full_article_public', true) : true);
    $__canReadFull = $__fullPublic || $__memberCanReadFull;

    $__readableBody = null;
    if ($__fullActive && $__canReadFull) {
        try {
            $__readableBody = \App\Support\GrimbaArticleText::readableBody($post);
        } catch (\Throwable) {
            $__readableBody = null;
        }
    }
    $__hasFullBody = $__readableBody !== null
        && ($__readableBody->source ?? null) === 'full'
        && trim((string) ($__readableBody->html ?? '')) !== '';
    $__fullBodyHtml = $__hasFullBody ? (string) $__readableBody->html : null;

    if ($__hasFullBody) {
        $__excerpt = trim(strip_tags($__fullBodyHtml));
    } else {
        $__excerpt = $__desc !== ''
            ? $__desc
            : trim(strip_tags((string) ($post->content ?? '')));
    }

    $__excerptWords = $__excerpt !== '' ? str_word_count($__excerpt) : 0;
    $__excerptDisplay = $__hasFullBody ? $__excerpt : Str::limit($__excerpt, 400);

    // Publisher URL for "Original source →".
    $__publisherUrl = null;
    if (! empty($post->source_id)) {
        $__publisherUrl = \Illuminate\Support\Facades\DB::table('rss_feed_items')
            ->where('post_id', $post->id)
            ->value('link')
            ?? \Illuminate\Support\Facades\DB::table('newsapi_items')
                ->where('post_id', $post->id)
                ->value('article_url');
        if (! $__publisherUrl && ! empty($__sourceMetaRow->website)) {
            $__publisherUrl = $__sourceMetaRow->website;
        }
    }

    // First-published-at relative time (e.g. "5 days ago").
    $__readAtTime = $__publishedAt
        ? $__publishedAt->locale(app()->getLocale())->diffForHumans()
        : null;

    // First category (skip internal review buckets).
    $__primaryCategory = null;
    if (method_exists($post, 'categories')) {
        $__cats = $post->relationLoaded('categories')
            ? $post->categories
            : $post->categories()->limit(5)->get();
        $__primaryCategory = $__cats
            ->reject(fn ($c) => in_array($c->name, \App\Support\GrimbaEditorialCategories::internalReviewNames(), true))
            ->reject(fn ($c) => in_array($c->name, \App\Support\GrimbaEditorialCategories::editionNames(), true))
            ->first();
    }
@endphp

<section class="grimba-article-card" aria-labelledby="grimba-article-card-title">
    @if($__heroUrl)
        <figure class="grimba-article-card__hero">
            <img src="{{ $__heroUrl }}"
                 alt="{{ $__title }}"
                 loading="eager"
                 decoding="sync"
                 width="1200"
                 height="630"
                 data-grimba-post-id="{{ $post->id }}">
        </figure>
    @endif

    <div class="grimba-article-card__meta">
        <span class="grimba-article-card__meta-kicker">{{ __('ARTICLE') }}</span>
        @if($__sourceName)
            <span class="grimba-article-card__sep" aria-hidden="true">·</span>
            <span class="grimba-article-card__meta-read">{{ __('Lu chez :source', ['source' => $__sourceName]) }}</span>
        @endif
        @if($__readAtTime)
            <span class="grimba-article-card__sep" aria-hidden="true">·</span>
            <span class="grimba-article-card__meta-time">{{ $__readAtTime }}</span>
        @endif
        <span class="grimba-article-card__readmin" title="{{ trans_choice(':count minute de lecture estimée|:count minutes de lecture estimées', $__readMin, ['count' => $__readMin]) }}">
            <span aria-hidden="true">⏱</span>
            <span>≈{{ $__readMin }} min</span>
        </span>
        @if(empty($post->original_language))
            {{-- S-LANG-14 (Vader 2026-05-17) — promoted from the italic
                 inline disclosure (S-LANG-05) to a proper visible badge
                 with a deep-link to methodology. The reader still sees
                 the raw text, but we never claim it's in their locale
                 and we explain why. The daily backfill cron (S-LANG-04)
                 sweeps the NULL backlog nightly so this is transient. --}}
            <span class="grimba-article-card__sep" aria-hidden="true">·</span>
            <a href="{{ url('/methodologie#language-detection') }}"
               class="grimba-article-card__lang-badge"
               title="{{ __("Le détecteur n'a pas pu confirmer la langue d'origine. Cliquez pour comprendre comment GrimbaNews classifie les langues.") }}">
                <span aria-hidden="true" class="grimba-article-card__lang-badge-dot">·</span>
                {{ __('Langue non classifiée') }}
            </a>
        @endif
    </div>

    <div class="grimba-article-card__pills">
        @if($__primaryCategory)
            <a href="{{ $__primaryCategory->url }}" class="grimba-article-card__pill grimba-article-card__pill--category">
                {{ __($__primaryCategory->name) }}
            </a>
        @endif
        @if($__regionLabel)
            <span class="grimba-article-card__pill grimba-article-card__pill--region">
                {{ __($__regionLabel) }}
            </span>
        @endif
        @if(in_array($__biasKey, ['left', 'center', 'right'], true))
            <span class="grimba-article-card__pill grimba-article-card__pill--bias" style="--pill-color: {{ $__biasColor }};">
                <span class="grimba-article-card__pill-dot" aria-hidden="true">—</span>
                {{ $__biasLabel }}
            </span>
        @endif
    </div>

    <h1 id="grimba-article-card-title" class="grimba-article-card__title">
        {{ $__title }}
    </h1>

    @if($__isTranslated)
        <div class="grimba-article-card__translated">
            {!! Theme::partial('nobuai-chip', ['size' => 'sm']) !!}
        </div>
    @endif

    <article class="grimba-article-card__source-card">
        <header class="grimba-article-card__source-head">
            <div class="grimba-article-card__source-id">
                <span class="grimba-article-card__source-label">{{ __('Source') }}</span>
                <strong class="grimba-article-card__source-name">{{ $__sourceName }}</strong>
                @if($__sourceCountry || $__sourceLang)
                    <span class="grimba-article-card__source-tag">
                        @if($__sourceCountry){{ $__sourceCountry }}@endif
                        @if($__sourceCountry && $__sourceLang) · @endif
                        @if($__sourceLang){{ $__sourceLang }}@endif
                    </span>
                @endif
            </div>
            <div class="grimba-article-card__source-chips">
                @if(in_array($__biasKey, ['left', 'center', 'right'], true))
                    <span class="grimba-article-card__chip" style="--chip-color: {{ $__biasColor }};">
                        <span aria-hidden="true">—</span> {{ $__biasLabel }}
                    </span>
                @endif
                @if($__ownership)
                    <span class="grimba-article-card__chip grimba-article-card__chip--ownership">
                        {{ __(ucfirst(str_replace('_', ' ', (string) $__ownership))) }}
                    </span>
                @endif
            </div>
        </header>

        @if($__credibility > 0)
            <div class="grimba-article-card__credibility">
                <div class="grimba-article-card__credibility-row">
                    <span class="grimba-article-card__credibility-label">{{ __('Crédibilité de la source') }}</span>
                    <strong class="grimba-article-card__credibility-value">{{ $__credibility }}/100</strong>
                </div>
                <div class="grimba-article-card__credibility-bar" aria-hidden="true">
                    <span style="width: {{ max(0, min(100, $__credibility)) }}%;"></span>
                </div>
            </div>
        @endif
    </article>

    {{-- Sprint 11 (Vader 2026-05-16): the larger ad sits between the
         SOURCE card above and the EXCERPT card below, so the ad reads
         as part of the article-opening flow instead of clutter at the
         end of the dossier. --}}
    @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
        'location' => 'grimba_story_after_hero',
        'class' => 'grimba-ad-slot--leaderboard my-3',
    ])

    @if($__excerptDisplay !== '')
        <article class="grimba-article-card__excerpt-card grimba-article-card__excerpt-card--featured @if($__hasFullBody) grimba-article-card__excerpt-card--full @endif">
            <header class="grimba-article-card__excerpt-head">
                <div class="grimba-article-card__excerpt-head-left">
                    <span class="grimba-article-card__source-label">
                        {{ $__hasFullBody ? __('Article intégral') : __('Extrait disponible') }}
                    </span>
                    <div class="grimba-article-card__excerpt-pills">
                        @if($__regionLabel)
                            <span class="grimba-article-card__pill grimba-article-card__pill--region">
                                {{ __($__regionLabel) }}
                            </span>
                        @endif
                        @if($__primaryCategory)
                            <a href="{{ $__primaryCategory->url }}" class="grimba-article-card__pill grimba-article-card__pill--category">
                                {{ __($__primaryCategory->name) }}
                            </a>
                        @endif
                        @if(in_array($__biasKey, ['left', 'center', 'right'], true))
                            <span class="grimba-article-card__pill grimba-article-card__pill--bias" style="--pill-color: {{ $__biasColor }};">
                                <span class="grimba-article-card__pill-dot" aria-hidden="true">—</span>
                                {{ $__biasLabel }}
                            </span>
                        @endif
                        @if($__ownership)
                            <span class="grimba-article-card__pill grimba-article-card__pill--ownership">
                                {{ __(ucfirst(str_replace('_', ' ', (string) $__ownership))) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="grimba-article-card__excerpt-meta">
                    <span class="grimba-article-card__excerpt-count">
                        {{ trans_choice(':count mot|:count mots', $__excerptWords, ['count' => $__excerptWords]) }}
                    </span>
                    <span class="grimba-article-card__excerpt-readmin" title="{{ trans_choice(':count minute de lecture estimée|:count minutes de lecture estimées', $__readMin, ['count' => $__readMin]) }}">
                        <span aria-hidden="true">⏱</span>
                        <span>≈{{ $__readMin }} min</span>
                    </span>
                </div>
            </header>
            @if($__hasFullBody)
                <div class="grimba-article-card__excerpt-body grimba-article-card__excerpt-body--full">
                    {!! \Botble\Base\Facades\BaseHelper::clean($__fullBodyHtml) !!}
                </div>
            @else
                <p class="grimba-article-card__excerpt-body">{{ $__excerptDisplay }}</p>
            @endif
            @if($__publisherUrl)
                <footer class="grimba-article-card__excerpt-foot">
                    <a href="{{ $__publisherUrl }}"
                       class="grimba-article-card__excerpt-link"
                       target="_blank"
                       rel="noopener external">
                        {{ __('Source originale') }} : <strong>{{ $__sourceName }}</strong> <span aria-hidden="true">↗</span>
                    </a>
                </footer>
            @endif
        </article>
    @endif
</section>

@once
    <style>
        .grimba-article-card {
            margin-bottom: 24px;
            color: var(--gn-ink, #1a1713);
        }

        .grimba-article-card__hero {
            margin: 0 0 16px;
            border-radius: 16px;
            overflow: hidden;
            background: #14110d;
            aspect-ratio: 21 / 9;
        }
        .grimba-article-card__hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .grimba-article-card__meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 10px;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
        }
        .grimba-article-card__meta-kicker {
            color: var(--gn-ink, #1a1713);
        }
        .grimba-article-card__sep {
            opacity: .5;
        }
        .grimba-article-card__meta-read,
        .grimba-article-card__meta-time {
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 12px;
            text-transform: none;
            letter-spacing: .01em;
            font-weight: 600;
        }
        .grimba-article-card__readmin {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(26, 23, 19, .06);
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 11px;
            font-weight: 700;
            text-transform: none;
            letter-spacing: .02em;
        }
        /* S-LANG-05/14 — unclassified-language badge. Promoted from the
           italic inline disclosure to a tappable pill so curious readers
           can deep-link to /methodology and understand what triggered
           the state. Amber tone (not red): the article isn't wrong,
           the classifier just hasn't run on it yet — that's an
           in-progress state, not an error. Zen audit fix 2026-05-17. */
        .grimba-article-card__lang-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 9px;
            border-radius: 999px;
            background: rgba(184, 134, 11, .10);
            border: 1px solid rgba(184, 134, 11, .26);
            color: #8a6608;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .02em;
            text-decoration: none;
            transition: background .18s ease, border-color .18s ease;
        }
        .grimba-article-card__lang-badge:hover,
        .grimba-article-card__lang-badge:focus-visible {
            background: rgba(184, 134, 11, .18);
            border-color: rgba(184, 134, 11, .38);
            color: #6e4f02;
            text-decoration: none;
        }
        .grimba-article-card__lang-badge-dot {
            font-weight: 900;
            line-height: 0;
            font-size: 18px;
            margin-top: -2px;
        }
        [data-bs-theme="dark"] .grimba-article-card__lang-badge,
        body[data-theme="dark"] .grimba-article-card__lang-badge {
            background: rgba(224, 184, 120, .14);
            border-color: rgba(224, 184, 120, .30);
            color: #e0b878;
        }

        .grimba-article-card__pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .grimba-article-card__pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(26, 23, 19, .06);
            border: 1px solid rgba(26, 23, 19, .10);
            color: var(--gn-ink, #1a1713);
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: .01em;
        }
        .grimba-article-card__pill:hover,
        .grimba-article-card__pill:focus-visible {
            background: rgba(26, 23, 19, .10);
            color: var(--gn-ink, #1a1713);
            text-decoration: none;
        }
        .grimba-article-card__pill--category {
            background: rgba(255, 245, 235, .8);
            border-color: rgba(192, 57, 43, .22);
            color: #c0392b;
        }
        .grimba-article-card__pill--region {
            background: rgba(247, 244, 235, .9);
            border-color: rgba(26, 23, 19, .12);
            font-weight: 600;
        }
        .grimba-article-card__pill--bias {
            background: rgba(26, 23, 19, .04);
            color: var(--pill-color, var(--gn-ink, #1a1713));
            border-color: color-mix(in srgb, var(--pill-color, #a8a8a8) 22%, rgba(26, 23, 19, .10));
        }
        .grimba-article-card__pill-dot {
            opacity: .7;
            font-weight: 800;
        }

        .grimba-article-card__title {
            margin: 0 0 12px;
            font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
            font-weight: 800;
            font-size: clamp(28px, 4.4vw, 48px);
            line-height: 1.05;
            letter-spacing: -0.025em;
            color: var(--gn-ink, #1a1713);
        }

        .grimba-article-card__translated {
            margin-bottom: 12px;
        }

        .grimba-article-card__source-card,
        .grimba-article-card__excerpt-card {
            position: relative;
            overflow: hidden;
            padding: 22px 24px;
            margin: 18px 0;
            border-radius: 16px;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.72), rgba(246, 241, 232, 0.56)),
                rgba(255, 255, 255, 0.62);
            border: 1px solid rgba(26, 23, 19, 0.08);
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.18),
                0 20px 52px rgba(26, 23, 19, 0.075);
        }

        /* Gradient top ribbon — editorial signature shared with the
           legacy full-article card. Vader 2026-05-16: every excerpt-
           class card on the site carries this. */
        .grimba-article-card__source-card::before,
        .grimba-article-card__excerpt-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 1rem;
            right: 1rem;
            height: 3px;
            pointer-events: none;
            background: linear-gradient(90deg, transparent, rgba(192, 57, 43, 0.52), rgba(59, 130, 246, 0.42), transparent);
        }
        .grimba-article-card__source-card > *,
        .grimba-article-card__excerpt-card > * {
            position: relative;
            z-index: 1;
        }

        .grimba-article-card__excerpt-title {
            margin: 6px 0 0;
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            font-size: clamp(1.4rem, 2.2vw, 2rem);
            line-height: 1.05;
            letter-spacing: -0.01em;
            color: var(--gn-ink, #1a1713);
        }

        .grimba-article-card__source-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .grimba-article-card__source-id {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        .grimba-article-card__source-label {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .56));
        }
        .grimba-article-card__source-name {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--gn-ink, #1a1713);
        }
        .grimba-article-card__source-tag {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
            text-transform: uppercase;
        }
        .grimba-article-card__source-chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .grimba-article-card__chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(26, 23, 19, .04);
            border: 1px solid color-mix(in srgb, var(--chip-color, #a8a8a8) 22%, rgba(26, 23, 19, .10));
            color: var(--chip-color, var(--gn-ink, #1a1713));
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 12px;
            font-weight: 700;
        }
        .grimba-article-card__chip--ownership {
            background: rgba(255, 252, 245, .9);
            color: var(--gn-ink, #1a1713);
            border-color: rgba(26, 23, 19, .10);
        }

        .grimba-article-card__credibility {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed rgba(26, 23, 19, .12);
        }
        .grimba-article-card__credibility-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 8px;
        }
        .grimba-article-card__credibility-label {
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
        }
        .grimba-article-card__credibility-value {
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            font-size: 15px;
            color: var(--gn-ink, #1a1713);
        }
        .grimba-article-card__credibility-bar {
            position: relative;
            height: 8px;
            border-radius: 999px;
            background: rgba(26, 23, 19, .08);
            overflow: hidden;
        }
        .grimba-article-card__credibility-bar > span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #16a34a, #15803d);
        }

        .grimba-article-card__excerpt-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .grimba-article-card__excerpt-head-left {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
            flex: 1 1 auto;
        }
        .grimba-article-card__excerpt-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .grimba-article-card__excerpt-pills .grimba-article-card__pill {
            font-size: 11.5px;
            padding: 3px 9px;
        }
        .grimba-article-card__pill--ownership {
            background: rgba(255, 252, 245, .9);
            color: var(--gn-ink, #1a1713);
            border-color: rgba(26, 23, 19, .10);
            font-weight: 600;
        }
        .grimba-article-card__excerpt-meta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .grimba-article-card__excerpt-count {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            padding: 3px 9px;
            border-radius: 999px;
            background: rgba(26, 23, 19, .05);
            color: var(--gn-ink-muted, rgba(26, 23, 19, .56));
        }
        .grimba-article-card__excerpt-readmin {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 3px 9px;
            border-radius: 999px;
            background: rgba(26, 23, 19, .05);
            color: var(--gn-ink-muted, rgba(26, 23, 19, .56));
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
        }
        .grimba-article-card__excerpt-body {
            margin: 0 0 12px;
            font-family: 'Fraunces', Georgia, serif;
            font-size: 16px;
            font-weight: 400;
            line-height: 1.6;
            color: var(--gn-ink, #1a1713);
        }

        /* Long-form typography for when the publisher API delivers the
           full body — proper paragraph rhythm, link treatment, figure
           handling, blockquote accent. Activates via the --full
           modifier on the excerpt card. */
        .grimba-article-card__excerpt-body--full {
            font-size: 17.5px;
            line-height: 1.7;
            max-width: 70ch;
            margin-inline: auto;
            margin-bottom: 16px;
        }
        .grimba-article-card__excerpt-body--full > * + * {
            margin-top: 0.85em;
        }
        .grimba-article-card__excerpt-body--full p:first-of-type::first-letter {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 3.2em;
            font-weight: 800;
            float: left;
            line-height: 0.95;
            padding-right: 0.08em;
            margin-top: 0.06em;
            color: #c0392b;
        }
        .grimba-article-card__excerpt-body--full a {
            color: #c0392b;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }
        .grimba-article-card__excerpt-body--full a:hover {
            color: #14110d;
        }
        .grimba-article-card__excerpt-body--full blockquote {
            margin: 1.25em 0;
            padding: 6px 0 6px 18px;
            border-left: 3px solid #c0392b;
            font-style: italic;
            color: var(--gn-ink, #1a1713);
            opacity: .82;
        }
        .grimba-article-card__excerpt-body--full figure {
            margin: 1.5em 0;
        }
        .grimba-article-card__excerpt-body--full figure img,
        .grimba-article-card__excerpt-body--full img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            display: block;
        }
        .grimba-article-card__excerpt-body--full figcaption {
            margin-top: 6px;
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 13px;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
            text-align: center;
        }
        .grimba-article-card__excerpt-body--full h2,
        .grimba-article-card__excerpt-body--full h3 {
            font-family: 'Fraunces', Georgia, serif;
            font-weight: 800;
            letter-spacing: -0.01em;
            margin-top: 1.5em;
        }
        .grimba-article-card__excerpt-body--full h2 {
            font-size: 1.5em;
        }
        .grimba-article-card__excerpt-body--full h3 {
            font-size: 1.25em;
        }
        .grimba-article-card__excerpt-body--full ul,
        .grimba-article-card__excerpt-body--full ol {
            padding-left: 1.4em;
        }
        .grimba-article-card__excerpt-body--full li {
            margin: 0.4em 0;
        }

        [data-bs-theme="dark"] .grimba-article-card__excerpt-body--full,
        body[data-theme="dark"] .grimba-article-card__excerpt-body--full {
            color: #fffaf0;
        }
        [data-bs-theme="dark"] .grimba-article-card__excerpt-body--full p:first-of-type::first-letter,
        body[data-theme="dark"] .grimba-article-card__excerpt-body--full p:first-of-type::first-letter {
            color: #e84c3d;
        }
        [data-bs-theme="dark"] .grimba-article-card__excerpt-body--full a,
        body[data-theme="dark"] .grimba-article-card__excerpt-body--full a {
            color: #ffb4a8;
        }
        [data-bs-theme="dark"] .grimba-article-card__excerpt-body--full a:hover,
        body[data-theme="dark"] .grimba-article-card__excerpt-body--full a:hover {
            color: #fffaf0;
        }
        [data-bs-theme="dark"] .grimba-article-card__excerpt-body--full blockquote,
        body[data-theme="dark"] .grimba-article-card__excerpt-body--full blockquote {
            border-left-color: #e84c3d;
            color: rgba(255, 250, 240, .82);
        }
        [data-bs-theme="dark"] .grimba-article-card__excerpt-body--full figcaption,
        body[data-theme="dark"] .grimba-article-card__excerpt-body--full figcaption {
            color: rgba(255, 250, 240, .58);
        }
        .grimba-article-card__excerpt-foot {
            text-align: center;
            padding-top: 8px;
            border-top: 1px dashed rgba(26, 23, 19, .12);
        }
        .grimba-article-card__excerpt-link {
            font-family: 'Public Sans', system-ui, sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: var(--gn-ink-muted, rgba(26, 23, 19, .65));
            text-decoration: none;
        }
        .grimba-article-card__excerpt-link:hover,
        .grimba-article-card__excerpt-link:focus-visible {
            color: var(--gn-ink, #1a1713);
        }
        .grimba-article-card__excerpt-link strong {
            color: var(--gn-ink, #1a1713);
        }

        [data-bs-theme="dark"] .grimba-article-card__title,
        body[data-theme="dark"] .grimba-article-card__title,
        [data-bs-theme="dark"] .grimba-article-card__source-name,
        body[data-theme="dark"] .grimba-article-card__source-name,
        [data-bs-theme="dark"] .grimba-article-card__excerpt-body,
        body[data-theme="dark"] .grimba-article-card__excerpt-body,
        [data-bs-theme="dark"] .grimba-article-card__credibility-value,
        body[data-theme="dark"] .grimba-article-card__credibility-value {
            color: #fffaf0;
        }
        [data-bs-theme="dark"] .grimba-article-card__source-card,
        body[data-theme="dark"] .grimba-article-card__source-card,
        [data-bs-theme="dark"] .grimba-article-card__excerpt-card,
        body[data-theme="dark"] .grimba-article-card__excerpt-card {
            background: rgba(28, 24, 17, .62);
            border-color: rgba(255, 250, 240, .12);
        }
        [data-bs-theme="dark"] .grimba-article-card__meta-kicker,
        body[data-theme="dark"] .grimba-article-card__meta-kicker {
            color: rgba(255, 250, 240, .92);
        }
        [data-bs-theme="dark"] .grimba-article-card__credibility,
        body[data-theme="dark"] .grimba-article-card__credibility {
            border-top-color: rgba(255, 250, 240, .14);
        }
        [data-bs-theme="dark"] .grimba-article-card__excerpt-foot,
        body[data-theme="dark"] .grimba-article-card__excerpt-foot {
            border-top-color: rgba(255, 250, 240, .14);
        }

        @media (max-width: 575.98px) {
            .grimba-article-card__source-card,
            .grimba-article-card__excerpt-card {
                padding: 14px 14px;
            }
            .grimba-article-card__title {
                font-size: clamp(24px, 7vw, 32px);
            }
        }
    </style>
@endonce
