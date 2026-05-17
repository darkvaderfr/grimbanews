@php
    use App\Support\GrimbaTranslationPresenter as GnTr;
    use App\Support\GrimbaEditorialCategories as GnCats;

    Theme::layout('grimba-chrome');
    Theme::set('isDetailPage', true);

    GnTr::warm($post);
    if (method_exists($post, 'categories')) {
        $post->loadMissing('categories');
        $post->setRelation(
            'categories',
            $post->categories
                ->reject(fn ($category): bool => in_array($category->name, GnCats::internalReviewNames(), true))
                ->values()
        );
    }

    $__gnSeoTitle = GnTr::title($post);
    $__gnSeoDesc = GnTr::description($post);
    $__gnTarget = GnTr::targetLocale();
    $__gnHasTr = GnTr::isTranslated($post, $__gnTarget);
    $__gnPublishedAt = GnTr::publishedAt($post);
    SeoHelper::setTitle($__gnSeoTitle);
    if ($__gnSeoDesc) {
        SeoHelper::setDescription(strip_tags((string) $__gnSeoDesc));
    }

    // Branded OG image per post. Cluster pages get a composite story
    // card with source count + bias distribution.
    $__gnOgClusterCount = $post->story_cluster_id
        ? \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
            ->where('story_cluster_id', $post->story_cluster_id)
            ->where('status', 'published')
            ->count()
        : 0;
    $ogUrl = ($post->story_cluster_id && $__gnOgClusterCount >= 2)
        ? url('/og/story/' . $post->story_cluster_id . '.png')
        : url('/og/post/' . $post->id . '.png');
    Theme::set('grimba_og_image', $ogUrl);
    SeoHelper::openGraph()->setImage($ogUrl);
    SeoHelper::openGraph()->addProperty('image:width', '1200');
    SeoHelper::openGraph()->addProperty('image:height', '630');
    SeoHelper::twitter()->setType('summary_large_image');
    SeoHelper::twitter()->addImage($ogUrl);

    $__gnClean = static fn ($value) => trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8')));
    $__gnArticleUrl = $post->url;
    $__gnClusterUrl = ($post->story_cluster_id && $__gnOgClusterCount >= 2)
        ? url('/comparatif/' . $post->story_cluster_id)
        : $__gnArticleUrl;
    $__gnJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $__gnClean($__gnSeoTitle),
        'description' => $__gnClean($__gnSeoDesc ?: $post->description),
        'image' => [$ogUrl],
        'url' => $__gnArticleUrl,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $__gnClusterUrl,
        ],
        'datePublished' => optional($__gnPublishedAt)->toAtomString(),
        'dateModified' => optional($post->updated_at ?: $__gnPublishedAt)->toAtomString(),
        'author' => [
            '@type' => 'Organization',
            'name' => $post->source_name ?: 'GrimbaNews',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'GrimbaNews',
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url('/og/home.png'),
                'width' => 1200,
                'height' => 630,
            ],
        ],
        'isAccessibleForFree' => true,
        // S-LANG-06 (Vader 2026-05-16) — JSON-LD inLanguage now respects
        // unclassified posts. Falling back to 'fr' for a NULL post is a
        // lie to search engines; omit the key in that case.
        'inLanguage' => $__gnHasTr
            ? $__gnTarget
            : (! empty($post->original_language) ? $post->original_language : null),
    ];
    if ($__gnJsonLd['inLanguage'] === null) {
        unset($__gnJsonLd['inLanguage']);
    }

    if ($post->relationLoaded('categories') || method_exists($post, 'categories')) {
        $section = optional($post->categories->first())->name;
        if ($section) {
            $__gnJsonLd['articleSection'] = $section;
        }
    }
@endphp

<script type="application/ld+json">{!! json_encode($__gnJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

{{-- S49: record post visit in grimba_read cookie (last 30, most-recent-first). --}}
<script>
    (function () {
        try {
            const id = '{{ (int) $post->id }}';
            const current = (document.cookie.match(/(?:^|; )grimba_read=([^;]+)/)?.[1] || '').split(',').filter(Boolean);
            const updated = [id, ...current.filter(x => x !== id)].slice(0, 30);
            document.cookie = 'grimba_read=' + updated.join(',') + '; path=/; max-age=' + (60 * 60 * 24 * 30) + '; SameSite=Lax';
        } catch (_) {}
    })();
</script>

{{-- S201 — story reading progress. Tiny fixed bar, updated with
     requestAnimationFrame so scroll work stays cheap. --}}
<div class="grimba-reading-progress" aria-hidden="true">
    <span data-grimba-reading-progress></span>
</div>
<script>
    (function () {
        const bar = document.querySelector('[data-grimba-reading-progress]');
        if (! bar) return;

        let ticking = false;
        function paint() {
            const target = document.querySelector('.grimba-story, .blog-post-details-content') || document.body;
            const rect = target.getBoundingClientRect();
            const viewport = window.innerHeight || document.documentElement.clientHeight || 1;
            const total = Math.max(1, rect.height - viewport);
            const read = Math.min(total, Math.max(0, -rect.top));
            bar.style.transform = 'scaleX(' + (read / total).toFixed(4) + ')';
            ticking = false;
        }
        function requestPaint() {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(paint);
        }

        window.addEventListener('scroll', requestPaint, { passive: true });
        window.addEventListener('resize', requestPaint);
        requestPaint();
    })();
</script>

@php

    $descriptionStyle = theme_option('blog_description_style');
    $authorStyle = theme_option('blog_author_style');
    $url = $post->url;

    // FR mode is the reading mode: English-source posts render from
    // translated_* columns, with NobuAI attribution beside the title.
    $__gnTarget  = GnTr::targetLocale();
    $__gnHasTr   = GnTr::isTranslated($post, $__gnTarget);
    $__gnMode    = $__gnHasTr ? 'translated' : 'original';
    $__gnTitle   = GnTr::title($post);
    $__gnDesc    = GnTr::description($post);
    $__gnOriginalTitle = $__gnHasTr ? $post->name : null;
    $__gnTargetLabel = match ($__gnTarget) {
        'en' => __('anglais'),
        'fr' => __('français'),
        default => strtoupper($__gnTarget),
    };
    $__gnMemberCanReadFull = (is_plugin_active('member') && auth('member')->check()) || auth()->check();
    $__gnMemberLoginUrl = (is_plugin_active('member') && \Illuminate\Support\Facades\Route::has('public.member.login'))
        ? route('public.member.login')
        : url('/login');
    $__gnFullActive = (bool) setting('grimba_full_article_active', true);
    $__gnFullArticlePublic = (bool) setting('grimba_full_article_public', true);
    $__gnCanReadFull = $__gnFullArticlePublic || $__gnMemberCanReadFull;
    $__gnReadableBody = \App\Support\GrimbaArticleText::readableBody($post);
    $__gnRawFullBody = trim((string) ($__gnReadableBody->html ?? ''));
    $__gnFullBodySource = $__gnReadableBody->source ?? null;
    $__gnTranslatedBody = GnTr::body($post);
    $__gnTranslatedBodyClean = \App\Support\GrimbaArticleText::cleanIngestBody($__gnTranslatedBody);
    $__gnTranslatedBodyReadable = $__gnHasTr
        && $__gnTranslatedBodyClean
        && \App\Support\GrimbaArticleText::textLength($__gnTranslatedBodyClean) >= \App\Support\GrimbaArticleText::MIN_READABLE_CHARS;
    $__gnFullBody = $__gnFullActive && $__gnCanReadFull && $__gnReadableBody
        ? ($__gnFullBodySource !== 'full' && $__gnTranslatedBodyReadable ? $__gnTranslatedBodyClean : $__gnRawFullBody)
        : null;
    $__gnFullArticleLocked = $__gnFullActive && ! $__gnCanReadFull && $__gnReadableBody !== null;
    $__gnShowsReaderBody = $__gnFullBody !== null || $__gnFullArticleLocked;
    $__gnUpstream = $__gnReadableBody
        ? (\Illuminate\Support\Facades\DB::table('rss_feed_items')->where('post_id', $post->id)->value('link')
            ?? \Illuminate\Support\Facades\DB::table('newsapi_items')->where('post_id', $post->id)->value('article_url')
            ?? \App\Support\GrimbaArticleText::firstHttpUrlFromHtml($post->content ?? null))
        : null;

    Theme::set('breadcrumb_background_image', $post->getMetaData('breadcrumb_background_image', true));
    Theme::set('breadcrumb_background_color', $post->getMetaData('breadcrumb_background_color', true));
    Theme::set('breadcrumb_text_color', $post->getMetaData('breadcrumb_text_color', true));

    // S148 — story-page mode. When this post belongs to a story
    // cluster with at least 2 published articles total, render the
    // multi-source dossier view instead of the legacy single-post layout.
    // The legacy layout is kept as fallback for orphan posts (no cluster)
    // and clusters of 1 (no comparison value).
    $__gnClusterPosts = collect();
    $__gnSourceMeta = collect();
    $__gnIsStoryPage = false;
    if ($post->story_cluster_id) {
        $__gnClusterPosts = \Botble\Blog\Models\Post::withoutGlobalScope('grimba_region')
            ->where('story_cluster_id', $post->story_cluster_id)
            ->where('status', 'published')
            ->with('categories')
            ->tap(fn ($q) => \App\Support\GrimbaPostRecency::orderByPublished($q))
            ->get([
                'id', 'name', 'description', 'source_id', 'source_name',
                'bias_rating', 'story_cluster_id', 'created_at', 'updated_at',
                'image',
                // S161 — translation fields so the cluster article list
                // can honor the NobuAI toggle. Without these the
                // SELECT'd object had only `name`, leaving the list
                // stuck on original-language headlines regardless of
                // cookie state.
                'translated_name', 'translated_description',
                'translated_content', 'translated_to', 'original_language',
                'content', 'full_content',
            ]);
        GnTr::warm($__gnClusterPosts);
        $__gnIsStoryPage = $__gnClusterPosts->count() >= 2;

        if ($__gnIsStoryPage) {
            $__gnSourceIds = $__gnClusterPosts->pluck('source_id')->filter()->unique()->all();
            $__gnSourceMeta = \App\Support\GrimbaSourceMeta::forIds($__gnSourceIds);
        }
    }
@endphp

@if($__gnIsStoryPage)
    <section class="grimba-story container grimba-story-page py-4 py-md-5">
        <div class="row gx-4 gx-lg-5">
            <div class="col-lg-8 col-12 mb-4">

                {{-- Canonical article hero card per Vader 2026-05-16
                     screenshot: hero image + meta line + pill row +
                     Fraunces title + SOURCE card + AVAILABLE EXCERPT
                     card. Replaces the legacy grimba-story-hero +
                     grimba-story-page__header pair. --}}
                @include(Theme::getThemeNamespace('partials.story.article-hero-card'), [
                    'post' => $post,
                    'sourceMeta' => $__gnSourceMeta,
                ])

                {{-- Cluster-side companion to the canonical article hero
                     card above. The hero card carries the title + meta +
                     SOURCE card + AVAILABLE EXCERPT card. The block
                     below adds the cluster-only signals: coverage-gap
                     warning when only one side covers the story, the
                     action bar with source-count stat + Analyse des
                     sources jump + Save pill, and the NobuAI insights
                     synthesis. --}}
                @php
                    $__gnLatest = $__gnClusterPosts->max('updated_at');
                    $__gnByBias = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
                    foreach ($__gnClusterPosts as $cp) {
                        $b = $cp->bias_rating ?? 'unknown';
                        if (! isset($__gnByBias[$b])) $b = 'unknown';
                        $__gnByBias[$b]++;
                    }
                @endphp
                <header class="grimba-story-page__header glass-panel p-3 p-md-4 mb-3">

                    {{-- S181 — derived coverage-gap callout. When a cluster
                         has 2+ sources but only one of L/C/R is represented,
                         surface that explicitly so the reader knows they're
                         getting a one-sided take. Cheaper than the manually-
                         set is_blindspot flag (auto from cluster contents). --}}
                    @php
                        $__sidesPresent = collect(['left','center','right'])
                            ->filter(fn ($b) => ($__gnByBias[$b] ?? 0) > 0)
                            ->values();
                        $__oneSidedCoverage = $__gnClusterPosts->count() >= 2 && $__sidesPresent->count() === 1;
                        $__sideMeta = [
                            'left'   => ['label' => __('la gauche'), 'color' => '#3b82f6'],
                            'center' => ['label' => __('le centre'), 'color' => '#a8a8a8'],
                            'right'  => ['label' => __('la droite'), 'color' => '#e84c3d'],
                        ];
                    @endphp
                    @if($__oneSidedCoverage)
                        @php $__sole = $__sidesPresent->first(); @endphp
                        <div class="d-flex align-items-center gap-2 mb-3" role="note"
                             style="
                                padding:10px 14px; border-radius:12px;
                                background:{{ $__sideMeta[$__sole]['color'] }}14;
                                border:1px solid {{ $__sideMeta[$__sole]['color'] }}40;
                                color:var(--gn-ink,#1a1713);
                                font-size:13.5px; line-height:1.4;
                             ">
                            <span aria-hidden="true" style="font-size:16px;">⚠</span>
                            <span>
                                <strong>{{ __('Couverture déséquilibrée') }}</strong> —
                                {{ __("cette histoire n'est pour l'instant couverte que par :side.", ['side' => $__sideMeta[$__sole]['label']]) }}
                                <a href="{{ url('/angles-morts') }}?cluster={{ (int) $post->story_cluster_id }}#cluster-{{ (int) $post->story_cluster_id }}" style="color:inherit; text-decoration:underline;">{{ __('Voir cet angle mort') }}</a>.
                            </span>
                        </div>
                    @endif

                    {{-- Dossier action bar (post-reinvention 2026-05-16).
                         The old filter tabs targeted article-list which
                         no longer exists; dossier-voices renders below
                         already split by side. Single line: coverage
                         stat + analyse-sources jump + save pill. --}}
                    <div class="grimba-story-page__bar mb-3">
                        <div class="grimba-story-page__bar-stat">
                            <span class="grimba-story-page__bar-stat-num">{{ $__gnClusterPosts->count() }}</span>
                            <span class="grimba-story-page__bar-stat-label">{{ trans_choice(':count source contributrice|:count sources contributrices', $__gnClusterPosts->count()) }}</span>
                            @include(Theme::getThemeNamespace('partials.info-pill'), [
                                'size' => 'sm',
                                'tone' => 'soft',
                                'body' => __("Pour ce dossier, combien de sources de chaque camp politique couvrent la même histoire. Tap les pastilles à droite pour filtrer la liste par camp."),
                            ])
                            @foreach(['left' => '#3b82f6', 'center' => '#a8a8a8', 'right' => '#e84c3d'] as $b => $col)
                                @if($__gnByBias[$b] > 0)
                                    <span class="grimba-story-page__bar-pill"
                                          style="--pill-color: {{ $col }};"
                                          title="{{ trans_choice(':count source|:count sources', $__gnByBias[$b]) }}">
                                        {{ $__gnByBias[$b] }}
                                    </span>
                                @endif
                            @endforeach
                        </div>

                        <div class="grimba-story-page__bar-actions">
                            <button type="button"
                                    class="grimba-story-page__compare"
                                    onclick="document.querySelector('.grimba-story-distribution')?.scrollIntoView({behavior:'smooth', block:'start'});"
                                    title="{{ __('Voir la distribution des biais') }}">
                                <span aria-hidden="true">⚖</span>
                                <span class="grimba-story-page__compare-label grimba-story-page__compare-label--full">{{ __('Analyse des sources') }}</span>
                                <span class="grimba-story-page__compare-label grimba-story-page__compare-label--short">{{ __('Sources') }}</span>
                            </button>

                            {{-- Save-for-later pill (cookie-only, no auth). --}}
                            {!! Theme::partial('save-button', ['post' => $post, 'variant' => 'pill']) !!}
                        </div>
                    </div>

                    <style>
                        .grimba-story-page__bar {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                            flex-wrap: wrap;
                            padding: 10px 14px;
                            border-radius: 14px;
                            background: rgba(255, 255, 255, .54);
                            border: 1px solid rgba(26, 23, 19, .08);
                        }
                        .grimba-story-page__bar-stat {
                            display: flex;
                            align-items: baseline;
                            gap: 8px;
                            min-width: 0;
                            flex-wrap: wrap;
                        }
                        .grimba-story-page__bar-stat-num {
                            font-family: 'Fraunces', Georgia, serif;
                            font-weight: 800;
                            font-size: 24px;
                            line-height: 1;
                            color: var(--gn-ink, #1a1713);
                            letter-spacing: -0.02em;
                        }
                        .grimba-story-page__bar-stat-label {
                            font-family: 'Public Sans', system-ui, sans-serif;
                            font-size: 12.5px;
                            font-weight: 600;
                            color: var(--gn-ink-muted, rgba(26, 23, 19, .62));
                        }
                        .grimba-story-page__bar-pill {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            min-width: 24px;
                            height: 22px;
                            padding: 0 7px;
                            border-radius: 999px;
                            background: color-mix(in srgb, var(--pill-color, #a8a8a8) 14%, transparent);
                            color: var(--pill-color, #a8a8a8);
                            font-family: 'JetBrains Mono', ui-monospace, monospace;
                            font-size: 11px;
                            font-weight: 800;
                            letter-spacing: .04em;
                            border: 1px solid color-mix(in srgb, var(--pill-color, #a8a8a8) 32%, transparent);
                        }
                        .grimba-story-page__bar-actions {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            flex-shrink: 0;
                        }
                        [data-bs-theme="dark"] .grimba-story-page__bar,
                        body[data-theme="dark"] .grimba-story-page__bar {
                            background: rgba(28, 24, 17, .58);
                            border-color: rgba(255, 250, 240, .10);
                        }
                        [data-bs-theme="dark"] .grimba-story-page__bar-stat-num,
                        body[data-theme="dark"] .grimba-story-page__bar-stat-num {
                            color: #fffaf0;
                        }
                        @media (max-width: 575.98px) {
                            .grimba-story-page__bar-actions {
                                width: 100%;
                                justify-content: flex-start;
                            }
                        }
                    </style>

                    {{-- S175 — Multi-source summary. When the post has a
                         pre-generated summary_nobuai, use it (LLM-driven,
                         once that ships). Otherwise build an extractive
                         baseline by lifting the lead sentence from each
                         cluster post's description, then dedupe near-
                         duplicates. The badge label flips to "Synthèse
                         multi-sources" so we don't pretend extractive is
                         AI. Single-bullet description-only fallback is
                         preserved as a last resort. --}}
                    @php
                        $__gnSummaryItems = [];
                        $__gnSummaryMode = 'fallback';
                        $__gnNobuAiScrub = static function (string $value): string {
                            $pattern = '/\b(?:OpenAI|OpenRouter|Anthropic|Claude|ChatGPT|GPT(?:-\d+(?:\.\d+)?)?|xAI|Grok|Google|Gemini|Mistral|Perplexity|Groq|DeepL|LibreTranslate)\b/iu';

                            return trim(preg_replace($pattern, 'NobuAI', $value) ?? $value);
                        };

                        if (! empty($post->summary_nobuai ?? null)) {
                            $__gnSummaryItems = collect(preg_split("/\r\n|\n|\r/", (string) $post->summary_nobuai) ?: [])
                                ->map(fn ($line) => $__gnNobuAiScrub(trim((string) $line)))
                                ->filter()
                                ->unique(fn ($line) => mb_strtolower((string) $line))
                                ->values()
                                ->all();
                            $__gnSummaryMode = 'nobuai';
                        } elseif ($__gnClusterPosts->count() >= 2) {
                            // Extractive: one bullet per source's lead sentence.
                            // Sort: current post first, then by published recency.
                            $__gnExtract = [];
                            $__gnSeen = [];
                            $__ordered = $__gnClusterPosts
                                ->sortByDesc(fn ($cp) => (int) $cp->id === (int) $post->id ? 1 : 0)
                                ->values();
                            foreach ($__ordered as $cp) {
                                $desc = trim(strip_tags((string) GnTr::description($cp)));
                                if ($desc === '') continue;
                                // Lead sentence: split on . ! ? followed by space/EOL.
                                $parts = preg_split('/(?<=[\.\!\?])\s+/u', $desc, 2);
                                $lead = trim($parts[0] ?? $desc);
                                if (mb_strlen($lead) < 30) {
                                    // Too short to be a useful sentence — take more.
                                    $lead = $desc;
                                }
                                $lead = \Illuminate\Support\Str::limit($lead, 220);
                                // Dedupe by first 40 lowercased chars.
                                $sig = mb_strtolower(mb_substr(preg_replace('/\s+/u', ' ', $lead), 0, 40));
                                if (isset($__gnSeen[$sig])) continue;
                                $__gnSeen[$sig] = true;
                                $__gnExtract[] = [
                                    'text'   => $lead,
                                    'source' => $cp->source_name ?? null,
                                    // S183 — bias dot on each bullet so a
                                    // reader can scan which side is saying
                                    // what without reading every line.
                                    'bias'   => $cp->bias_rating ?? 'unknown',
                                ];
                                if (count($__gnExtract) >= 5) break;
                            }
                            if (! empty($__gnExtract)) {
                                $__gnSummaryItems = $__gnExtract;
                                $__gnSummaryMode = 'extractive';
                            }
                        }

                        if (empty($__gnSummaryItems) && $__gnDesc) {
                            $__gnSummaryItems = [strip_tags($__gnDesc)];
                            $__gnSummaryMode = 'fallback';
                        }
                    @endphp

                    @if(! empty($__gnSummaryItems))
                        {{-- S171 → S175 — Insights block. Badge label flips
                             based on $__gnSummaryMode: NobuAI when LLM-
                             generated, "Synthèse multi-sources" when
                             extractive (lead sentence per cluster source),
                             default copy on single-bullet fallback. --}}
                        @php
                            $__badgeLabel = match ($__gnSummaryMode) {
                                'nobuai'     => __('Insights par NobuAI'),
                                'extractive' => __('Synthèse multi-sources'),
                                default      => __('Aperçu'),
                            };
                            $__gnNobuAiGeneratedAt = ! empty($post->summary_generated_at ?? null)
                                ? \Carbon\Carbon::parse($post->summary_generated_at)->locale($__gnTarget)->diffForHumans()
                                : null;
                            $__gnNobuAiIsStale = false;
                            if (! empty($post->summary_generated_at ?? null) && ! empty($__gnLatest)) {
                                $__gnNobuAiIsStale = \Carbon\Carbon::parse($__gnLatest)->gt(\Carbon\Carbon::parse($post->summary_generated_at));
                            }
                            $__badgeFootnote = match ($__gnSummaryMode) {
                                'nobuai'     => $__gnNobuAiIsStale
                                    ? __('NobuAI à rafraîchir : une nouvelle couverture est arrivée après ce résumé.')
                                    : ($__gnNobuAiGeneratedAt
                                    ? __('Généré par NobuAI :time.', ['time' => $__gnNobuAiGeneratedAt])
                                    : __('Généré par NobuAI.')),
                                'extractive' => __('Première phrase de chaque source · résumé NobuAI à venir.'),
                                default      => __("Résumé multi-sources à venir dès qu'une autre couverture rejoint cette histoire."),
                            };
                        @endphp
                        <section class="grimba-insights-panel" aria-labelledby="grimba-insights-title">
                            <header class="grimba-insights-panel__head">
                                <span class="grimba-insights-panel__badge">
                                    <span class="grimba-insights-panel__badge-dot" aria-hidden="true"></span>
                                    {{ $__badgeLabel }}
                                </span>
                                <h2 id="grimba-insights-title" class="grimba-insights-panel__title">
                                    {{ __('Ce que les sources disent') }}
                                    @include(Theme::getThemeNamespace('partials.info-pill'), [
                                        'size' => 'sm',
                                        'body' => __('Synthèse générée par NobuAI à partir des articles du dossier. Mode extractif = phrases tirées des articles tels quels. Mode NobuAI = synthèse rédigée. Vérifiez toujours la source pour les chiffres et citations.'),
                                    ])
                                </h2>
                            </header>

                                @if($__gnSummaryMode === 'extractive')
                                    {{-- S175/S183 — bulleted list with bias-colored
                                         dot + per-source attribution. --}}
                                    @php
                                        $__bulletColor = [
                                            'left'    => '#3b82f6',
                                            'center'  => '#a8a8a8',
                                            'right'   => '#e84c3d',
                                            'unknown' => 'rgba(26,23,19,0.45)',
                                        ];
                                    @endphp
                                    <ul class="grimba-insights-panel__list" data-mode="extractive">
                                        @foreach($__gnSummaryItems as $item)
                                            @php
                                                $__bColor = $__bulletColor[$item['bias'] ?? 'unknown'] ?? $__bulletColor['unknown'];
                                            @endphp
                                            <li class="grimba-insights-panel__row" style="--insight-tone: {{ $__bColor }};">
                                                <span class="grimba-insights-panel__row-dot" aria-hidden="true"></span>
                                                <span class="grimba-insights-panel__row-body">
                                                    {{ $item['text'] }}
                                                    @if($item['source'])
                                                        <span class="grimba-insights-panel__row-source">— {{ $item['source'] }}</span>
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif($__gnSummaryMode === 'nobuai')
                                    @php
                                        $__insightMeta = [
                                            'Ce qui est confirmé' => ['icon' => '✓', 'tone' => '#166534'],
                                            'Perspective africaine' => ['icon' => '◆', 'tone' => '#c0392b'],
                                            'Ce que dit la gauche' => ['icon' => '●', 'tone' => '#3b82f6'],
                                            'Ce que dit le centre' => ['icon' => '●', 'tone' => '#8a8a8a'],
                                            'Ce que dit la droite' => ['icon' => '●', 'tone' => '#e84c3d'],
                                            'Angle mort' => ['icon' => '!', 'tone' => '#8a2be2'],
                                            'Pourquoi ça compte' => ['icon' => '→', 'tone' => '#1a1713'],
                                        ];
                                        $__parsedInsights = collect($__gnSummaryItems)
                                            ->map(function ($line) use ($__insightMeta) {
                                                $text = trim((string) (is_array($line) ? ($line['text'] ?? '') : $line));
                                                $label = 'NobuAI';
                                                $body = $text;
                                                if (str_contains($text, ':')) {
                                                    [$candidate, $rest] = array_map('trim', explode(':', $text, 2));
                                                    if (isset($__insightMeta[$candidate])) {
                                                        $label = $candidate;
                                                        $body = $rest;
                                                    }
                                                }
                                                return ['label' => $label, 'body' => $body];
                                            })
                                            ->filter(fn ($item) => $item['body'] !== '')
                                            ->unique(fn ($item) => mb_strtolower($item['label'] . '|' . $item['body']))
                                            ->take(6)
                                            ->values();
                                    @endphp
                                    <div class="grimba-insights-panel__cards" data-mode="nobuai">
                                        @foreach($__parsedInsights as $insight)
                                            @php
                                                $__meta = $__insightMeta[$insight['label']] ?? ['icon' => '•', 'tone' => 'var(--gn-ink,#1a1713)'];
                                            @endphp
                                            <article class="grimba-insights-panel__card" style="--insight-tone: {{ $__meta['tone'] }};">
                                                <span class="grimba-insights-panel__card-icon" aria-hidden="true">{{ $__meta['icon'] }}</span>
                                                <div class="grimba-insights-panel__card-body">
                                                    <span class="grimba-insights-panel__card-label">{{ __($insight['label']) }}</span>
                                                    <p class="grimba-insights-panel__card-text">{{ $insight['body'] }}</p>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @elseif(count($__gnSummaryItems) === 1)
                                    <p class="grimba-insights-panel__single">
                                        {{ is_array($__gnSummaryItems[0]) ? $__gnSummaryItems[0]['text'] : $__gnSummaryItems[0] }}
                                    </p>
                                @else
                                    <ul class="grimba-insights-panel__list" data-mode="fallback">
                                        @foreach(array_slice($__gnSummaryItems, 0, 6) as $line)
                                            <li class="grimba-insights-panel__row" style="--insight-tone: rgba(26,23,19,0.45);">
                                                <span class="grimba-insights-panel__row-dot" aria-hidden="true"></span>
                                                <span class="grimba-insights-panel__row-body">{{ is_array($line) ? $line['text'] : $line }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                <footer class="grimba-insights-panel__foot">
                                    @if($__badgeFootnote)
                                        <span class="grimba-insights-panel__foot-note">{{ $__badgeFootnote }}</span>
                                    @endif
                                    <a href="{{ url('/contact') }}?topic=insight&post={{ $post->id }}"
                                       class="grimba-insights-panel__foot-link">
                                        {{ __('Signaler une imprécision') }}
                                    </a>
                                </footer>
                            </section>

                            {{-- S306 — Bias Comparison Summary (3-column framing).
                                  Both extractive (already bias-attributed) and
                                  nobuai (labelled "Ce que dit la gauche/centre/droite")
                                  modes feed it. The partial bails when fewer than
                                  2 sides have items. --}}
                            @php
                                $__gnComparisonItems = [];
                                if ($__gnSummaryMode === 'extractive') {
                                    $__gnComparisonItems = $__gnSummaryItems;
                                } elseif ($__gnSummaryMode === 'nobuai' && ! empty($__parsedInsights)) {
                                    $__sideMap = [
                                        'Ce que dit la gauche' => 'left',
                                        'Ce que dit le centre' => 'center',
                                        'Ce que dit la droite' => 'right',
                                    ];
                                    foreach ($__parsedInsights as $ins) {
                                        $side = $__sideMap[$ins['label']] ?? null;
                                        if ($side) {
                                            $__gnComparisonItems[] = [
                                                'text'   => $ins['body'],
                                                'source' => null,
                                                'bias'   => $side,
                                            ];
                                        }
                                    }
                                }
                            @endphp
                            @if(! empty($__gnComparisonItems))
                                @include(Theme::getThemeNamespace('partials.story.bias-comparison-summary'), [
                                    'items' => $__gnComparisonItems,
                                ])
                            @endif

                        <style>
                            .grimba-insights-panel {
                                position: relative;
                                overflow: hidden;
                                margin-top: 18px;
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
                            .grimba-insights-panel::before {
                                content: "";
                                position: absolute;
                                top: 0;
                                left: 1rem;
                                right: 1rem;
                                height: 3px;
                                pointer-events: none;
                                background: linear-gradient(90deg, transparent, rgba(192, 57, 43, 0.52), rgba(59, 130, 246, 0.42), transparent);
                            }
                            .grimba-insights-panel > * {
                                position: relative;
                                z-index: 1;
                            }
                            .grimba-insights-panel__head {
                                display: flex;
                                align-items: baseline;
                                gap: 10px;
                                flex-wrap: wrap;
                                margin-bottom: 14px;
                            }
                            .grimba-insights-panel__badge {
                                display: inline-flex;
                                align-items: center;
                                gap: 6px;
                                padding: 4px 10px;
                                border-radius: 9999px;
                                background: linear-gradient(135deg, #1a1713, #3a342c);
                                color: #f6f1e8;
                                font-family: 'Public Sans', system-ui, sans-serif;
                                font-size: 10.5px;
                                font-weight: 800;
                                letter-spacing: .14em;
                                text-transform: uppercase;
                                box-shadow: 0 6px 18px rgba(26, 23, 19, .22);
                            }
                            .grimba-insights-panel__badge-dot {
                                width: 6px;
                                height: 6px;
                                border-radius: 50%;
                                background: #f6f1e8;
                                box-shadow: 0 0 8px rgba(246, 241, 232, .8);
                            }
                            .grimba-insights-panel__title {
                                margin: 0;
                                font-family: 'Fraunces', 'Playfair Display', Georgia, serif;
                                font-weight: 700;
                                font-size: 18px;
                                line-height: 1.2;
                                letter-spacing: -0.01em;
                                color: var(--gn-ink, #1a1713);
                            }
                            .grimba-insights-panel__list {
                                list-style: none;
                                margin: 0;
                                padding: 0;
                                display: flex;
                                flex-direction: column;
                                gap: 10px;
                            }
                            .grimba-insights-panel__row {
                                display: flex;
                                gap: 12px;
                                align-items: flex-start;
                                padding: 0 0 0 4px;
                                font-family: 'Public Sans', system-ui, sans-serif;
                                font-size: 14.5px;
                                line-height: 1.5;
                                color: var(--gn-ink, #1a1713);
                            }
                            .grimba-insights-panel__row-dot {
                                flex: 0 0 8px;
                                width: 8px;
                                height: 8px;
                                margin-top: 8px;
                                border-radius: 50%;
                                background: var(--insight-tone, rgba(26, 23, 19, .45));
                                box-shadow: 0 0 0 2px rgba(255, 255, 255, .42), 0 0 10px color-mix(in srgb, var(--insight-tone, #a8a8a8) 40%, transparent);
                            }
                            .grimba-insights-panel__row-body {
                                flex: 1;
                                min-width: 0;
                            }
                            .grimba-insights-panel__row-source {
                                opacity: .58;
                                font-size: 12.5px;
                                margin-left: 4px;
                            }
                            .grimba-insights-panel__single {
                                margin: 0;
                                font-family: 'Public Sans', system-ui, sans-serif;
                                font-size: 15px;
                                line-height: 1.55;
                                color: var(--gn-ink, #1a1713);
                            }
                            .grimba-insights-panel__cards {
                                display: grid;
                                gap: 10px;
                            }
                            .grimba-insights-panel__card {
                                display: grid;
                                grid-template-columns: 30px minmax(0, 1fr);
                                gap: 12px;
                                align-items: flex-start;
                                padding: 12px 14px;
                                border-radius: 14px;
                                border: 1px solid color-mix(in srgb, var(--insight-tone, #1a1713) 18%, rgba(26, 23, 19, .08));
                                background: linear-gradient(180deg, rgba(255, 255, 255, .82), rgba(255, 255, 255, .52));
                            }
                            .grimba-insights-panel__card::before {
                                content: "";
                                position: absolute;
                            }
                            .grimba-insights-panel__card-icon {
                                width: 30px;
                                height: 30px;
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                border-radius: 50%;
                                background: color-mix(in srgb, var(--insight-tone, #1a1713) 14%, rgba(255, 255, 255, .85));
                                color: var(--insight-tone, var(--gn-ink, #1a1713));
                                font-family: 'Public Sans', system-ui, sans-serif;
                                font-size: 14px;
                                font-weight: 800;
                                box-shadow: 0 4px 12px color-mix(in srgb, var(--insight-tone, #a8a8a8) 25%, transparent);
                            }
                            .grimba-insights-panel__card-body {
                                display: flex;
                                flex-direction: column;
                                gap: 3px;
                                min-width: 0;
                            }
                            .grimba-insights-panel__card-label {
                                font-family: 'JetBrains Mono', ui-monospace, monospace;
                                font-size: 10px;
                                font-weight: 700;
                                letter-spacing: .14em;
                                text-transform: uppercase;
                                color: var(--insight-tone, var(--gn-ink-muted, rgba(26, 23, 19, .6)));
                            }
                            .grimba-insights-panel__card-text {
                                margin: 0;
                                font-family: 'Public Sans', system-ui, sans-serif;
                                font-size: 14.5px;
                                line-height: 1.5;
                                color: var(--gn-ink, #1a1713);
                            }
                            .grimba-insights-panel__foot {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                gap: 12px;
                                flex-wrap: wrap;
                                margin-top: 14px;
                                padding-top: 12px;
                                border-top: 1px dashed rgba(26, 23, 19, .14);
                            }
                            .grimba-insights-panel__foot-note {
                                font-family: 'JetBrains Mono', ui-monospace, monospace;
                                font-size: 10.5px;
                                font-weight: 600;
                                letter-spacing: .04em;
                                color: var(--gn-ink-muted, rgba(26, 23, 19, .58));
                            }
                            .grimba-insights-panel__foot-link {
                                font-family: 'Public Sans', system-ui, sans-serif;
                                font-size: 12px;
                                font-weight: 700;
                                color: var(--gn-ink-muted, rgba(26, 23, 19, .55));
                                text-decoration: underline;
                                text-decoration-thickness: 1px;
                                text-underline-offset: 3px;
                                transition: color .18s ease;
                            }
                            .grimba-insights-panel__foot-link:hover,
                            .grimba-insights-panel__foot-link:focus-visible {
                                color: var(--gn-ink, #1a1713);
                            }

                            [data-bs-theme="dark"] .grimba-insights-panel,
                            body[data-theme="dark"] .grimba-insights-panel {
                                background:
                                    radial-gradient(120% 60% at 0% 0%, rgba(255, 250, 240, .06), transparent 65%),
                                    linear-gradient(180deg, rgba(28, 24, 17, .82), rgba(28, 24, 17, .58));
                                border-color: rgba(255, 250, 240, .12);
                            }
                            [data-bs-theme="dark"] .grimba-insights-panel__title,
                            body[data-theme="dark"] .grimba-insights-panel__title,
                            [data-bs-theme="dark"] .grimba-insights-panel__row,
                            body[data-theme="dark"] .grimba-insights-panel__row,
                            [data-bs-theme="dark"] .grimba-insights-panel__card-text,
                            body[data-theme="dark"] .grimba-insights-panel__card-text,
                            [data-bs-theme="dark"] .grimba-insights-panel__single,
                            body[data-theme="dark"] .grimba-insights-panel__single {
                                color: #fffaf0;
                            }
                            [data-bs-theme="dark"] .grimba-insights-panel__card,
                            body[data-theme="dark"] .grimba-insights-panel__card {
                                background: linear-gradient(180deg, rgba(255, 250, 240, .04), rgba(255, 250, 240, .02));
                            }
                            [data-bs-theme="dark"] .grimba-insights-panel__foot,
                            body[data-theme="dark"] .grimba-insights-panel__foot {
                                border-color: rgba(255, 250, 240, .18);
                            }

                            /* Legacy alias block — preserved as a no-op until the
                               next deploy clears old cached views. */
                            .grimba-nobuai-insights {
                                display: grid;
                                gap: 10px;
                            }
                            .grimba-nobuai-insight {
                                display: flex;
                                gap: 10px;
                                align-items: flex-start;
                                padding: 11px 12px;
                                border: 1px solid rgba(26,23,19,0.11);
                                border-radius: 14px;
                                background: rgba(255,255,255,0.52);
                                color: var(--gn-ink,#1a1713);
                                font-size: 15px;
                                line-height: 1.48;
                            }
                            .grimba-nobuai-insight__dot {
                                flex: 0 0 24px;
                                width: 24px;
                                height: 24px;
                                border-radius: 9999px;
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                margin-top: 1px;
                                background: color-mix(in srgb, var(--insight-tone) 14%, transparent);
                                color: var(--insight-tone);
                                font-size: 12px;
                                font-weight: 800;
                            }
                            .grimba-nobuai-insight__copy strong {
                                display: block;
                                margin-bottom: 1px;
                                color: var(--gn-ink,#1a1713);
                                font-size: 12px;
                                font-family: 'Public Sans', system-ui, sans-serif;
                                text-transform: uppercase;
                                letter-spacing: 0.05em;
                            }
                            [data-theme="dark"] .grimba-nobuai-insight,
                            body[data-theme="dark"] .grimba-nobuai-insight,
                            html[data-bs-theme="dark"] .grimba-nobuai-insight {
                                background: rgba(246,241,232,0.08);
                                border-color: rgba(246,241,232,0.14);
                            }
                        </style>
                    @endif
                </header>

                {{-- Sprint 12 — article-hero-card now consumes both
                     the excerpt AND the full-body API output directly
                     (replaces the excerpt with the full HTML when
                     readableBody->source === 'full' and the reader
                     has access). The full-article partial fires ONLY
                     for the locked-member-gate case so the login CTA
                     still renders for paid content. --}}
                @if($__gnFullArticleLocked)
                    @include(Theme::getThemeNamespace('partials.story.full-article'), [
                        'post' => $post,
                        'body' => $__gnFullBody,
                        'locked' => $__gnFullArticleLocked,
                        'loginUrl' => $__gnMemberLoginUrl,
                        'upstream' => $__gnUpstream,
                        'source' => $__gnFullBodySource,
                    ])
                @endif

                {{-- grimba_story_after_hero ad is now rendered inside
                     the article-hero-card partial between SOURCE and
                     EXCERPT cards (Vader 2026-05-16 reposition). The
                     duplicate include here is dropped. --}}

                @include(Theme::getThemeNamespace('partials.story.share-kit'), [
                    'title' => $__gnTitle,
                ])

                {{-- Steve-led reinvention 2026-05-16: replaces the old
                     source-drilldown + article-list double card-grid
                     with a single three-voices panel + slim sources
                     table. A dossier is one story from many angles, not
                     twelve restatements of the same headline. --}}
                @include(Theme::getThemeNamespace('partials.story.dossier-voices'), [
                    'clusterPosts' => $__gnClusterPosts,
                    'currentPost'  => $post,
                    'sourceMeta'   => $__gnSourceMeta,
                ])

                @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                    'location' => 'grimba_story_mid',
                    'class' => 'grimba-ad-slot--native my-3',
                ])
            </div>

            <aside class="col-lg-4 col-12">
                <div class="position-sticky" style="top: 90px;">
                    {{-- S341: dropped story.coverage-details — its data
                          (total source count, L/C/R counts, last-updated)
                          is already shown in the article-list header
                          (":count articles"), the bias-distribution bar
                          below, and the timeline panel further down. The
                          panel was a textual restatement of what the
                          visual bar conveys. --}}
                    @include(Theme::getThemeNamespace('partials.story.bias-distribution'), [
                        'clusterPosts' => $__gnClusterPosts,
                        'sourceMeta'   => $__gnSourceMeta,
                    ])
                    @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                        'location' => 'grimba_story_sidebar',
                        'class' => 'grimba-ad-slot--sidebar my-3',
                    ])
                    @include(Theme::getThemeNamespace('partials.story.timeline'), [
                        'clusterPosts' => $__gnClusterPosts,
                    ])
                    @include(Theme::getThemeNamespace('partials.story.highlights'), [
                        'clusterPosts' => $__gnClusterPosts,
                    ])
                    {{-- Sidebar voices partial superseded by the
                         main-column dossier-voices panel — was
                         showing the same per-side quotes twice. --}}
                    @include(Theme::getThemeNamespace('partials.story.similar-topics'), [
                        'post' => $post,
                    ])
                </div>
            </aside>
        </div>
    </section>
@endif

@if(! $__gnIsStoryPage)
{{-- S172 — dropped Bootstrap bg-white that forced light bg in dark
     mode. body chrome's grimba paper bg now shines through correctly. --}}
<section class="echo-hero-section inner inner-post echo-feature-area blog-post-details-content grimba-article-shell">
    <div class="echo-hero">
        <div class="container">
            {{-- S170 — translation feature dropped. The legacy single-
                 post layout had a translate-picker here; gone. --}}
            <div class="echo-full-hero-content">
                @php
                    $__gnPrimarySidebarHtml = trim((string) dynamic_sidebar('primary_sidebar'));
                    $__gnBlogBottomSidebarHtml = trim((string) dynamic_sidebar('blog_bottom_sidebar'));
                    $__gnHasPrimarySidebar = $__gnPrimarySidebarHtml !== '';
                @endphp
                <div class="row gx-5 sticky-coloum-wrap">
                    <div @class([
                        'grimba-article-primary',
                        'col-xl-8 col-lg-8' => $__gnHasPrimarySidebar,
                        'col-xl-9 col-lg-10 mx-auto' => ! $__gnHasPrimarySidebar,
                    ])>
                        {{-- Single-post (orphan) layout now uses the
                             canonical article-hero-card pattern shared
                             with dossier pages. Vader 2026-05-16: "all
                             article pages should be displayed moving
                             forward" with this shape. The compare-CTA
                             + save pill ride below as a thin action
                             bar so the card stays clean. --}}
                        @include(Theme::getThemeNamespace('partials.story.article-hero-card'), [
                            'post' => $post,
                        ])

                        <div class="grimba-orphan-actions d-flex flex-wrap align-items-center gap-2 mb-4">
                            {!! Theme::partial('comparatif-cta', ['post' => $post]) !!}
                            {!! Theme::partial('save-button', ['post' => $post, 'variant' => 'pill']) !!}
                        </div>

                        {{-- Same dedupe as the story-page path (Sprint 12):
                             article-hero-card now consumes the excerpt/full
                             body inline, so the legacy full-article partial
                             only fires for the locked member-gate case. --}}
                        @if($__gnFullArticleLocked)
                            @include(Theme::getThemeNamespace('partials.story.full-article'), [
                                'post' => $post,
                                'body' => $__gnFullBody,
                                'locked' => $__gnFullArticleLocked,
                                'loginUrl' => $__gnMemberLoginUrl,
                                'upstream' => $__gnUpstream,
                                'source' => $__gnFullBodySource,
                            ])
                        @endif

                        @include(Theme::getThemeNamespace('partials.story.share-kit'), [
                            'title' => $__gnTitle,
                        ])
                        {{-- Vader 2026-05-16: the larger top ad below share
                             is removed — the smaller native ad inside
                             .ck-content is the only post-content placement.
                             Big banner already sits between the hero and
                             excerpt cards (article-hero-card). --}}

                        @if (echo_is_audio_post($post))
                            <div class="wrapper-audio-control">
                                <audio controls>
                                    <source src="{{ RvMedia::url(echo_get_post_audio_url($post)) }}" type="audio/ogg">
                                </audio>
                            </div>
                        @endif

                        @php
                            $__gnBody = $__gnShowsReaderBody ? null : $__gnTranslatedBody;
                            $__gnShowOrig = $__gnHasTr && GnTr::hasTranslatedBody($post, $__gnTarget);
                            // Vader 2026-05-16 — strip the ingester
                            // boilerplate ("Lire l'article original" link +
                            // "Full text is unavailable…" NewsAPI marker)
                            // before rendering. The reader already has a
                            // clean canonical-source link up in the excerpt
                            // card.
                            $__gnContentClean = $__gnBody
                                ? \App\Support\GrimbaArticleText::cleanIngestBody($__gnBody)
                                : null;
                        @endphp
                        @if (! empty($__gnContentClean))
                            <div class="ck-content">
                                @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                                    'location' => 'grimba_article_mid',
                                    'class' => 'grimba-ad-slot--native my-3',
                                ])
                                {!! apply_filters('ads_render', null, 'post_before', ['class' => 'my-2 text-center']) !!}

                                {!! BaseHelper::clean($__gnContentClean) !!}

                                @if ($__gnShowOrig)
                                    {{-- S-LANG-07: only emit lang="" when the
                                         origin language is actually known.
                                         Empty `lang=""` is invalid HTML and
                                         lies to screen readers. --}}
                                    <details class="mt-4 mb-2 small">
                                        <summary class="text-muted" style="cursor: pointer;">{{ __('Afficher le texte original') }}@if(! empty($post->original_language)) ({{ strtoupper($post->original_language) }})@endif</summary>
                                        <div class="mt-2 opacity-75" @if(! empty($post->original_language)) lang="{{ $post->original_language }}" @endif>
                                            {!! BaseHelper::clean(\App\Support\GrimbaArticleText::stripNewsApiTruncationMarker($post->content ?? '') ?: '') !!}
                                        </div>
                                    </details>
                                @endif

                                {!! apply_filters('ads_render', null, 'post_after', ['class' => 'my-2 text-center']) !!}
                            </div>
                        @endif

                        {{-- GrimbaNews other angles (sibling cluster posts) --}}
                        @include(Theme::getThemeNamespace('partials.blog.post.partials.other-angles'), ['post' => $post])

                        @php
                            $tags = $post->tags;
                        @endphp

                        {{-- Sprint 13 (Vader 2026-05-16): the legacy
                             Botble Echo details-share icon row is gone —
                             share-kit (icon row, NobuAI-branded) now
                             carries all sharing affordances. Tags stay
                             here because they're a distinct surface. --}}
                        @if ($tags->isNotEmpty())
                            <div class="echo-financial-area">
                                <div class="content mb-5">
                                    <div class="details-tag">
                                        <h6>{{ __('Tags:') }}</h6>
                                        @foreach($tags as $tag)
                                            <a class="py-2" href="{{ $tag->url }}"><button>{{ $tag->name }}</button></a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (($posts = get_related_posts($post->id, 2)) && $posts->isNotEmpty())
                            <div class="echo-more-news-area">
                                <div class="inner">
                                    <div class="row">
                                        @if ($prevPost = $posts[0])
                                            @php($url = $prevPost->url)
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                <div class="echo-top-story">
                                                    <div class="echo-story-picture img-transition-scale">
                                                        @if ($image = $prevPost->image)
                                                            <a href="{{ $url }}" class="related-img">
                                                                {{ RvMedia::image($image, $prevPost->name, attributes: ['class' => 'img-hover']) }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <div class="echo-story-text">
                                                        <em>
                                                            <a href="{{ $url }}" class="prev font-italic font-weight-light"><i class="fa-light fa-arrow-left"></i> {{ __('Previously') }}</a>
                                                        </em>
                                                        <h6><a href="{{ $url }}" title="{{ $prevPost->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $prevPost->name }}</a></h6>

                                                        {!! Theme::partial('post-meta', ['post' => $prevPost]) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($nextPost = (isset($posts[1]) ? $posts[1] : null))
                                            @php($url = $nextPost->url)
                                            <div class="col-lg-6 col-md-6 col-sm-12">
                                                <div class="echo-top-story">
                                                    <div class="echo-story-picture img-transition-scale">
                                                        @if ($image = $nextPost->image)
                                                            <a href="{{ $url }}" class="related-img">
                                                                {{ RvMedia::image($image, $nextPost->name, attributes: ['class' => 'img-hover']) }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                    <div class="echo-story-text">
                                                        <em>
                                                            <a href="{{ $url }}" class="prev font-italic font-weight-light">{{ __('Up next') }} <i class="fa-light fa-arrow-right"></i></a>
                                                        </em>
                                                        <h6><a href="{{ $url }}" title="{{ $nextPost->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $nextPost->name }}</a></h6>

                                                        {!! Theme::partial('post-meta', ['post' => $nextPost]) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (class_exists($post->author_type) && ($author = $post->author) && $post->author->exists)
                            @if ($authorStyle == 'avatar_center' )
                                <div class="echo-ab-pr">
                                    @if ($avatar = $author->avatar->url)
                                        <div class="echo-ab-pr-img text-center">
                                            {{ RvMedia::image($avatar, $author->name, attributes: ['class' => 'author-avatar']) }}
                                        </div>
                                    @endif
                                    <div class="echo-ab-pr-name text-center">
                                        <h5>{{ $author->name }}</h5>
                                    </div>
                                    @php($tagName = '@' . $author->last_name)
                                    <div class="echo-ab-pr-sub-name text-center">
                                        <span>{{ $tagName }}</span>
                                    </div>

                                    @if ($description = $author->description)
                                        <div class="echo-ab-pr-info mt-3">
                                            <p class="text-center">{!! BaseHelper::clean($description) !!}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="echo-author-area">
                                    @if ($avatar = $author->avatar->url)
                                        <div class="image-area">
                                            {{ RvMedia::image($avatar, $author->name, attributes: ['class' => 'author-avatar']) }}
                                        </div>
                                    @endif
                                    <div class="content">
                                        <h5 class="title">{{ $author->name }}</h5>
                                        @if ($description = $author->description)
                                            <p class="desc">{!! BaseHelper::clean($description) !!}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif

                        {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, null, $post) !!}

                        <script>
                            (function () {
                                const articleShell = document.currentScript.closest('.grimba-article-shell');
                                if (! articleShell) return;

                                const sanitizeCommentText = (value) => (value || '')
                                    .replace(/\u00a0/g, ' ')
                                    .replace(/[ \t]+\n/g, '\n')
                                    .replace(/\n{4,}/g, '\n\n\n')
                                    .trimStart()
                                    .slice(0, 1000);

                                const insertAroundSelection = (editor, before, after = before, fallback = '') => {
                                    editor.focus();
                                    const selection = window.getSelection();
                                    if (! selection || selection.rangeCount === 0 || ! editor.contains(selection.anchorNode)) {
                                        editor.textContent = sanitizeCommentText(editor.textContent + fallback);
                                        return;
                                    }

                                    const range = selection.getRangeAt(0);
                                    const selected = range.toString() || fallback;
                                    range.deleteContents();
                                    range.insertNode(document.createTextNode(before + selected + after));
                                    selection.removeAllRanges();
                                    editor.dispatchEvent(new Event('input', { bubbles: true }));
                                };

                                const prefixSelectionLines = (editor, prefix) => {
                                    editor.focus();
                                    const selection = window.getSelection();
                                    const selected = selection && selection.rangeCount && editor.contains(selection.anchorNode)
                                        ? selection.getRangeAt(0).toString()
                                        : '';
                                    const text = selected || 'Votre point';
                                    const prefixed = text.split(/\n/).map((line) => prefix + line.replace(new RegExp('^' + prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')), '')).join('\n');

                                    if (! selected) {
                                        editor.textContent = sanitizeCommentText(editor.textContent + (editor.textContent ? '\n' : '') + prefixed);
                                        editor.dispatchEvent(new Event('input', { bubbles: true }));
                                        return;
                                    }

                                    const range = selection.getRangeAt(0);
                                    range.deleteContents();
                                    range.insertNode(document.createTextNode(prefixed));
                                    selection.removeAllRanges();
                                    editor.dispatchEvent(new Event('input', { bubbles: true }));
                                };

                                const enhanceCommentForm = (form) => {
                                    if (form.grimbaRichTextEnhanced) return;

                                    const textarea = form.querySelector('textarea[name="content"]');
                                    if (! textarea) return;

                                    form.grimbaRichTextEnhanced = true;
                                    form.querySelectorAll('.gn-comments-composer').forEach((node) => node.remove());
                                    textarea.classList.add('gn-comments__source-textarea');

                                    const composer = document.createElement('div');
                                    composer.className = 'gn-comments-composer';
                                    composer.innerHTML = [
                                        '<div class="gn-comments-composer__toolbar" role="toolbar" aria-label="Mise en forme du commentaire">',
                                        '<button type="button" data-gn-format="bold" aria-label="Gras"><strong>B</strong></button>',
                                        '<button type="button" data-gn-format="italic" aria-label="Italique"><em>I</em></button>',
                                        '<button type="button" data-gn-format="quote" aria-label="Citation">"</button>',
                                        '<button type="button" data-gn-format="bullet" aria-label="Liste">•</button>',
                                        '</div>',
                                        '<div class="gn-comments-composer__editor" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="Ajoutez une lecture nuancée, une source ou une question..."></div>',
                                        '<div class="gn-comments-composer__meta"><span>Texte léger, liens inclus si utiles.</span><span data-gn-count>0/1000</span></div>',
                                    ].join('');

                                    textarea.insertAdjacentElement('beforebegin', composer);

                                    const editor = composer.querySelector('.gn-comments-composer__editor');
                                    const counter = composer.querySelector('[data-gn-count]');
                                    const sync = () => {
                                        const value = sanitizeCommentText(editor.innerText);
                                        textarea.value = value;
                                        counter.textContent = value.length + '/1000';
                                    };
                                    form.grimbaSyncComposer = () => {
                                        editor.textContent = textarea.value || '';
                                        sync();
                                    };

                                    editor.textContent = textarea.value || '';
                                    sync();

                                    editor.addEventListener('input', sync);
                                    editor.addEventListener('paste', (event) => {
                                        event.preventDefault();
                                        const text = event.clipboardData?.getData('text/plain') || '';
                                        document.execCommand('insertText', false, sanitizeCommentText(text));
                                    });
                                    textarea.addEventListener('input', () => {
                                        if (textarea.value !== sanitizeCommentText(editor.innerText)) {
                                            editor.textContent = textarea.value;
                                            sync();
                                        }
                                    });
                                    form.addEventListener('submit', sync);
                                    form.addEventListener('reset', () => window.setTimeout(() => {
                                        editor.textContent = '';
                                        sync();
                                    }, 0));

                                    composer.addEventListener('click', (event) => {
                                        const button = event.target.closest('[data-gn-format]');
                                        if (! button) return;

                                        const action = button.dataset.gnFormat;
                                        if (action === 'bold') insertAroundSelection(editor, '**', '**', 'texte important');
                                        if (action === 'italic') insertAroundSelection(editor, '_', '_', 'nuance');
                                        if (action === 'quote') prefixSelectionLines(editor, '> ');
                                        if (action === 'bullet') prefixSelectionLines(editor, '- ');
                                        sync();
                                    });
                                };

                                const enhanceAll = () => articleShell
                                    .querySelectorAll('.fob-comment-form')
                                    .forEach(enhanceCommentForm);

                                enhanceAll();
                                new MutationObserver(enhanceAll).observe(articleShell, { childList: true, subtree: true });
                                if (window.jQuery) {
                                    window.jQuery(document).on('ajaxSuccess', function (_event, _xhr, settings) {
                                        if (! settings?.url || ! String(settings.url).includes('/fob-comment')) return;
                                        window.setTimeout(() => articleShell
                                            .querySelectorAll('.fob-comment-form')
                                            .forEach((form) => form.grimbaSyncComposer?.()), 0);
                                    });
                                }
                            })();
                        </script>

                        @if($__gnBlogBottomSidebarHtml !== '')
                            <div class="mt-5">
                                {!! $__gnBlogBottomSidebarHtml !!}
                            </div>
                        @endif
                    </div>
                    @if($__gnHasPrimarySidebar)
                        <div class="col-xl-4 col-lg-4 sticky-coloum-item">
                            <div class="echo-right-ct-1">
                                {!! apply_filters('ads_render', null, 'primary_sidebar_before', ['class' => 'my-2 text-center']) !!}

                                {!! $__gnPrimarySidebarHtml !!}

                                {!! apply_filters('ads_render', null, 'primary_sidebar_after', ['class' => 'my-2 text-center']) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif{{-- close S148 story-page fallback wrapper --}}
