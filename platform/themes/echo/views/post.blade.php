@php
    use App\Support\GrimbaTranslationPresenter as GnTr;

    Theme::layout('grimba-chrome');
    Theme::set('isDetailPage', true);

    $__gnSeoTitle = GnTr::title($post);
    $__gnSeoDesc = GnTr::description($post);
    $__gnTarget = GnTr::targetLocale();
    $__gnHasTr = GnTr::isTranslated($post, $__gnTarget);
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
        'datePublished' => optional($post->created_at)->toAtomString(),
        'dateModified' => optional($post->updated_at ?: $post->created_at)->toAtomString(),
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
        'inLanguage' => $__gnHasTr ? $__gnTarget : ($post->original_language ?: 'fr'),
    ];

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
    $__gnRawFullBody = trim((string) ($post->full_content ?? ''));
    $__gnFullBody = $__gnFullActive && $__gnMemberCanReadFull && $__gnRawFullBody !== ''
        ? ($__gnHasTr && GnTr::hasTranslatedBody($post, $__gnTarget) ? (GnTr::body($post) ?: $__gnRawFullBody) : $__gnRawFullBody)
        : null;
    $__gnFullArticleLocked = $__gnFullActive && ! $__gnMemberCanReadFull && $__gnRawFullBody !== '';
    $__gnUpstream = $__gnRawFullBody !== ''
        ? (\Illuminate\Support\Facades\DB::table('rss_feed_items')->where('post_id', $post->id)->value('link')
            ?? \Illuminate\Support\Facades\DB::table('newsapi_items')->where('post_id', $post->id)->value('article_url'))
        : null;

    Theme::set('breadcrumb_background_image', $post->getMetaData('breadcrumb_background_image', true));
    Theme::set('breadcrumb_background_color', $post->getMetaData('breadcrumb_background_color', true));
    Theme::set('breadcrumb_text_color', $post->getMetaData('breadcrumb_text_color', true));

    // S148 — story-page mode. When this post belongs to a story
    // cluster with at least 2 published articles total, render the
    // GroundNews-style cluster view instead of the legacy single-post
    // layout. The legacy layout is kept as fallback for orphan posts
    // (no cluster) and clusters of 1 (no comparison value).
    $__gnClusterPosts = collect();
    $__gnSourceMeta = collect();
    $__gnIsStoryPage = false;
    if ($post->story_cluster_id) {
        $__gnClusterPosts = \Botble\Blog\Models\Post::query()
            ->where('story_cluster_id', $post->story_cluster_id)
            ->where('status', 'published')
            ->with('categories')
            ->orderBy('created_at', 'desc')
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
                'translated_to', 'original_language',
            ]);
        $__gnIsStoryPage = $__gnClusterPosts->count() >= 2;

        if ($__gnIsStoryPage) {
            $__gnSourceIds = $__gnClusterPosts->pluck('source_id')->filter()->unique()->all();
            $__gnSourceMeta = empty($__gnSourceIds) ? collect() :
                \Illuminate\Support\Facades\DB::table('news_sources')
                    ->whereIn('id', $__gnSourceIds)
                    ->get(['id','name','website','ownership_type','credibility_score','owner_name'])
                    ->keyBy('id');
        }
    }
@endphp

@if($__gnIsStoryPage)
    <section class="grimba-story container py-4 py-md-5">
        <div class="row gx-4 gx-lg-5">
            <div class="col-lg-8 col-12 mb-4">

                @php
                    // Best hero image for the story: current post's image
                    // first, else any cluster post that has one, else the
                    // editorial placeholder route. The cards below each
                    // carry their own image too.
                    $__gnHero = $post->image ?: $__gnClusterPosts->pluck('image')->filter()->first();
                    $__gnHeroUrl = $__gnHero
                        ? \Botble\Media\Facades\RvMedia::getImageUrl($__gnHero)
                        : route('public.og.placeholder', $post->id);
                @endphp

                <div class="grimba-story-hero glass-panel p-0 mb-3" style="overflow:hidden;">
                    <div class="ratio ratio-21x9" style="background:rgba(0,0,0,0.04);">
                        <img src="{{ $__gnHeroUrl }}"
                             alt="{{ $__gnTitle }}"
                             loading="eager"
                             decoding="sync"
                             width="1200"
                             height="630"
                             style="object-fit:cover; width:100%; height:100%;">
                    </div>
                </div>

                {{-- S170 — Hero block matches GroundNews article display:
                     kicker → title → bias filter tabs + Bias Comparison
                     button → bullet summary with NobuAI insights toggle. --}}
                @php
                    $__gnLatest = $__gnClusterPosts->max('updated_at');
                    $__gnByBias = ['left' => 0, 'center' => 0, 'right' => 0, 'unknown' => 0];
                    foreach ($__gnClusterPosts as $cp) {
                        $b = $cp->bias_rating ?? 'unknown';
                        if (! isset($__gnByBias[$b])) $b = 'unknown';
                        $__gnByBias[$b]++;
                    }
                @endphp
                <header class="glass-panel p-3 p-md-4 mb-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2 small">
                        <span class="grimba-methodology__kicker">{{ __('Histoire') }}</span>
                        @if($post->source_name)
                            <span class="opacity-50">·</span>
                            <span class="opacity-75">{{ __("Lu d'abord chez :source", ['source' => $post->source_name]) }}</span>
                        @endif
                        <span class="opacity-50">·</span>
                        <span class="opacity-75">
                            {{ trans_choice(':count couverture|:count couvertures', $__gnClusterPosts->count(), ['count' => $__gnClusterPosts->count()]) }}
                        </span>
                        @if($__gnLatest)
                            <span class="opacity-50">·</span>
                            <span class="opacity-75">{{ __('Mis à jour :time', ['time' => $__gnLatest->locale($__gnTarget)->diffForHumans()]) }}</span>
                        @endif
                        {{-- S179 — reading time chip on the story hero meta line --}}
                        {!! Theme::partial('reading-time', ['post' => $post]) !!}
                    </div>

                    <h1 class="grimba-methodology__title m-0 mb-3"
                        style="font-size:clamp(28px, 3.6vw, 44px); line-height:1.1; letter-spacing:-0.5px;">
                        {{ $__gnTitle }}
                    </h1>
                    @if($__gnHasTr)
                        <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                            {!! Theme::partial('nobuai-chip', ['size' => 'md']) !!}
                            <span class="small opacity-65">
                                {{ __('Article original en :source affiché en :target.', ['source' => strtoupper((string) $post->original_language), 'target' => $__gnTargetLabel]) }}
                            </span>
                        </div>
                    @endif

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

                    {{-- S170 — bias filter tabs sit right under the title,
                         GroundNews-style. Tabs use the same data attribute
                         as the article-list section below; clicking filters
                         in place via the existing JS handler. --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-3" data-grimba-cluster-tabs>
                        @php
                            $__pillBg = 'background:rgba(0,0,0,0.05); padding:4px;';
                            $__activeBtn = 'background:var(--gn-ink,#1a1713); color:var(--gn-paper,#f6f1e8);';
                            $__inactiveBtn = 'background:transparent; color:var(--gn-ink,#1a1713);';
                        @endphp
                        <div role="tablist" style="display:flex; gap:4px; border-radius:9999px; {{ $__pillBg }}">
                            <button type="button" data-bias-tab="all" role="tab" aria-selected="true"
                                    style="padding:6px 14px; border-radius:9999px; border:none; font-weight:700; font-size:13px; {{ $__activeBtn }}">
                                {{ __('Tous') }}
                            </button>
                            @foreach(['left' => [__('Gauche'),'#3b82f6'], 'center' => [__('Centre'),'#a8a8a8'], 'right' => [__('Droite'),'#e84c3d']] as $b => [$lbl,$col])
                                @if($__gnByBias[$b] > 0)
                                    <button type="button" data-bias-tab="{{ $b }}" role="tab" aria-selected="false"
                                            style="padding:6px 14px; border-radius:9999px; border:none; font-weight:600; font-size:13px; {{ $__inactiveBtn }}">
                                        <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:{{ $col }}; margin-right:5px; vertical-align:1px;"></span>
                                        {{ $lbl }}
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        <button type="button"
                                onclick="document.querySelector('.grimba-story-distribution')?.scrollIntoView({behavior:'smooth', block:'start'});"
                                style="margin-left:auto; padding:6px 14px; border-radius:9999px; border:1px solid rgba(26,23,19,0.18); background:rgba(255,255,255,0.6); color:var(--gn-ink,#1a1713); font-weight:600; font-size:13px; cursor:pointer;"
                                title="{{ __('Voir la distribution des biais') }}">
                            ⚖️ {{ __('Comparaison des biais') }}
                        </button>

                        {{-- S173 — save-for-later pill. Cookie-only (no auth).
                             Toggles post id in grimba_vault, surfaces in /coffre. --}}
                        {!! Theme::partial('save-button', ['post' => $post, 'variant' => 'pill']) !!}
                    </div>

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
                        <div style="border-top:1px dashed rgba(26,23,19,0.15); padding-top:14px; margin-top:18px;">
                            <details open class="grimba-insights" style="cursor:default;">
                                <summary class="grimba-insights__summary"
                                    style="cursor:pointer; list-style:none; display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                                    <span style="
                                        display:inline-flex; align-items:center; gap:6px;
                                        padding:4px 10px; border-radius:9999px;
                                        background:linear-gradient(135deg,#1a1713,#3a342c);
                                        color:#f6f1e8;
                                        font-family:'Public Sans',system-ui,sans-serif;
                                        font-size:11.5px; font-weight:700; letter-spacing:0.5px;
                                    ">
                                        <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#f6f1e8;"></span>
                                        {{ $__badgeLabel }}
                                    </span>
                                    <span class="ms-auto small opacity-55" style="font-size:12px;">
                                        ▾ <span style="margin-left:4px;">{{ __('cliquer pour masquer') }}</span>
                                    </span>
                                </summary>

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
                                    <ul class="m-0" style="list-style:none; padding:0; font-size:15.5px; line-height:1.55; color:var(--gn-ink,#1a1713);">
                                        @foreach($__gnSummaryItems as $item)
                                            @php
                                                $__bColor = $__bulletColor[$item['bias'] ?? 'unknown'] ?? $__bulletColor['unknown'];
                                            @endphp
                                            <li style="display:flex; gap:10px; margin-bottom:12px; align-items:flex-start;">
                                                <span aria-hidden="true" title="{{ __($item['bias'] ?? 'non classé') }}"
                                                      style="flex:0 0 8px; width:8px; height:8px; margin-top:8px; border-radius:50%; background:{{ $__bColor }};"></span>
                                                <span style="flex:1;">
                                                    {{ $item['text'] }}
                                                    @if($item['source'])
                                                        <span class="opacity-60" style="font-size:12.5px; margin-left:4px;">— {{ $item['source'] }}</span>
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
                                    <div class="grimba-nobuai-insights">
                                        @foreach($__parsedInsights as $insight)
                                            @php
                                                $__meta = $__insightMeta[$insight['label']] ?? ['icon' => '•', 'tone' => 'var(--gn-ink,#1a1713)'];
                                            @endphp
                                            <div class="grimba-nobuai-insight">
                                                <span class="grimba-nobuai-insight__dot" style="--insight-tone: {{ $__meta['tone'] }};">{{ $__meta['icon'] }}</span>
                                                <span class="grimba-nobuai-insight__copy">
                                                    <strong>{{ __($insight['label']) }}</strong>
                                                    {{ $insight['body'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(count($__gnSummaryItems) === 1)
                                    <p class="m-0" style="font-size:15.5px; line-height:1.6; color:var(--gn-ink,#1a1713);">
                                        {{ is_array($__gnSummaryItems[0]) ? $__gnSummaryItems[0]['text'] : $__gnSummaryItems[0] }}
                                    </p>
                                @else
                                    <ul class="m-0 ps-3" style="font-size:15.5px; line-height:1.6; color:var(--gn-ink,#1a1713);">
                                        @foreach(array_slice($__gnSummaryItems, 0, 6) as $line)
                                            <li style="margin-bottom:10px;">{{ is_array($line) ? $line['text'] : $line }}</li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if($__badgeFootnote)
                                    <p class="small opacity-55 mt-2 mb-0">{{ $__badgeFootnote }}</p>
                                @endif

                                <div class="d-flex justify-content-end mt-3">
                                    <a href="{{ url('/contact') }}?topic=insight&post={{ $post->id }}"
                                       style="font-size:12px; color:var(--gn-ink,#1a1713); opacity:0.55; text-decoration:underline;">
                                        {{ __('Ce résumé vous semble incorrect ?') }}
                                    </a>
                                </div>
                            </details>
                        </div>

                        <style>
                            .grimba-insights__summary::-webkit-details-marker { display: none; }
                            .grimba-insights[open] .grimba-insights__summary span:last-child::before {
                                content: '▴ ';
                            }
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

                @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                    'location' => 'grimba_story_after_hero',
                    'class' => 'grimba-ad-slot--leaderboard my-3',
                ])

                @include(Theme::getThemeNamespace('partials.story.share-kit'), [
                    'title' => $__gnTitle,
                ])

                @include(Theme::getThemeNamespace('partials.story.source-drilldown'), [
                    'clusterPosts' => $__gnClusterPosts,
                    'sourceMeta'   => $__gnSourceMeta,
                ])

                @include(Theme::getThemeNamespace('partials.story.full-article'), [
                    'post' => $post,
                    'body' => $__gnFullBody,
                    'locked' => $__gnFullArticleLocked,
                    'loginUrl' => $__gnMemberLoginUrl,
                    'upstream' => $__gnUpstream,
                ])

                @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                    'location' => 'grimba_story_mid',
                    'class' => 'grimba-ad-slot--native my-3',
                ])

                @include(Theme::getThemeNamespace('partials.story.article-list'), [
                    'clusterPosts' => $__gnClusterPosts,
                    'currentPost'  => $post,
                    'sourceMeta'   => $__gnSourceMeta,
                ])
            </div>

            <aside class="col-lg-4 col-12">
                <div class="position-sticky" style="top: 90px;">
                    @include(Theme::getThemeNamespace('partials.story.coverage-details'), [
                        'clusterPosts' => $__gnClusterPosts,
                        'clusterId'    => $post->story_cluster_id,
                    ])
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
                    @include(Theme::getThemeNamespace('partials.story.voices'), [
                        'clusterPosts' => $__gnClusterPosts,
                    ])
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
<section class="echo-hero-section inner inner-post echo-feature-area blog-post-details-content">
    <div class="echo-hero">
        <div class="container">
            {{-- S170 — translation feature dropped. The legacy single-
                 post layout had a translate-picker here; gone. --}}
            <div class="echo-full-hero-content">
                <div class="row gx-5 sticky-coloum-wrap">
                    <div class="col-xl-8 col-lg-8">
                        {{-- S200 — orphan-post parity. Single-source posts now
                             use the same glass-language hero as clustered
                             stories instead of the stock Echo Bootstrap block. --}}
                        <header class="grimba-orphan-hero glass-panel p-0 mb-4" style="overflow:hidden;">
                            @if (defined('GALLERY_MODULE_SCREEN_NAME') && ! empty($galleries = gallery_meta_data($post)))
                                <div class="grimba-orphan-hero__media">
                                    {!! render_object_gallery($galleries) !!}
                                </div>
                            @elseif ($image = $post->image)
                                <div class="ratio ratio-21x9" style="background:rgba(0,0,0,0.04);">
                                    {{ RvMedia::image($image, $post->name, attributes: [
                                        'style' => 'object-fit:cover;width:100%;height:100%;',
                                        'loading' => 'eager',
                                        'decoding' => 'sync',
                                        'width' => 1200,
                                        'height' => 630,
                                    ]) }}
                                </div>
                            @else
                                <div class="ratio ratio-21x9" style="background:rgba(0,0,0,0.04);">
                                    <img src="{{ route('public.og.placeholder', $post->id) }}"
                                         alt="{{ $__gnTitle }}"
                                         loading="eager"
                                         decoding="sync"
                                         width="1200"
                                         height="630"
                                         style="object-fit:cover;width:100%;height:100%;">
                                </div>
                            @endif

                            <div class="p-3 p-md-4">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-2 small">
                                    <span class="grimba-methodology__kicker">{{ __('Article') }}</span>
                                    @if($post->source_name)
                                        <span class="opacity-50">·</span>
                                        <span class="opacity-75">{{ __('Lu chez :source', ['source' => $post->source_name]) }}</span>
                                    @endif
                                    <span class="opacity-50">·</span>
                                    <span class="opacity-75">{{ optional($post->created_at)->locale($__gnTarget)->diffForHumans() }}</span>
                                    {!! Theme::partial('reading-time', ['post' => $post]) !!}
                                </div>

                                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                    @include(Theme::getThemeNamespace('partials.category-badge'), ['post' => $post])
                                    @include(Theme::getThemeNamespace('partials.bias-badge'), ['bias' => $post->bias_rating ?? 'unknown', 'size' => 'sm'])
                                </div>

                                <h1 class="grimba-methodology__title m-0 mb-3"
                                    style="font-size:clamp(30px, 4vw, 52px); line-height:1.04; letter-spacing:-0.7px;">
                                    {{ $__gnTitle }}
                                </h1>
                                @if($__gnHasTr)
                                    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
                                        {!! Theme::partial('nobuai-chip', ['size' => 'md']) !!}
                                        <span class="small opacity-65">
                                            {{ __('Article original en :source affiché en :target.', ['source' => strtoupper((string) $post->original_language), 'target' => $__gnTargetLabel]) }}
                                        </span>
                                    </div>
                                @endif

                                @include(Theme::getThemeNamespace('partials.blog.post.partials.source-attribution'), ['post' => $post])

                                @if ($description = trim(strip_tags((string) $__gnDesc)))
                                    <div style="border-top:1px dashed rgba(26,23,19,0.15); padding-top:14px; margin-top:18px;">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span style="
                                                display:inline-flex; align-items:center; gap:6px;
                                                padding:4px 10px; border-radius:9999px;
                                                background:linear-gradient(135deg,#1a1713,#3a342c);
                                                color:#f6f1e8;
                                                font-family:'Public Sans',system-ui,sans-serif;
                                                font-size:11.5px; font-weight:700; letter-spacing:0.5px;
                                            ">
                                                <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#f6f1e8;"></span>
                                                {{ __('Aperçu') }}
                                            </span>
                                            <span class="small opacity-55">{{ __("Résumé multi-sources à venir si d'autres couvertures rejoignent cette histoire.") }}</span>
                                        </div>
                                        <p class="m-0" style="font-size:15.5px; line-height:1.6; color:var(--gn-ink,#1a1713);">
                                            {{ \Illuminate\Support\Str::limit($description, 260) }}
                                        </p>
                                    </div>
                                @endif

                                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                                    {!! Theme::partial('comparatif-cta', ['post' => $post]) !!}
                                    {!! Theme::partial('save-button', ['post' => $post, 'variant' => 'pill']) !!}
                                </div>
                            </div>
                        </header>

                        @include(Theme::getThemeNamespace('partials.story.share-kit'), [
                            'title' => $__gnTitle,
                        ])
                        @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                            'location' => 'grimba_article_top',
                            'class' => 'grimba-ad-slot--leaderboard my-3',
                        ])

                        @if (echo_is_audio_post($post))
                            <div class="wrapper-audio-control">
                                <audio controls>
                                    <source src="{{ RvMedia::url(echo_get_post_audio_url($post)) }}" type="audio/ogg">
                                </audio>
                            </div>
                        @endif

                        @php
                            $__gnBody = GnTr::body($post);
                            $__gnShowOrig = $__gnHasTr && GnTr::hasTranslatedBody($post, $__gnTarget);
                        @endphp
                        @if ($content = $__gnBody)
                            <div class="ck-content">
                                @include(Theme::getThemeNamespace('partials.home.ad-slot'), [
                                    'location' => 'grimba_article_mid',
                                    'class' => 'grimba-ad-slot--native my-3',
                                ])
                                {!! apply_filters('ads_render', null, 'post_before', ['class' => 'my-2 text-center']) !!}

                                {!! BaseHelper::clean($content) !!}

                                @if ($__gnShowOrig)
                                    <details class="mt-4 mb-2 small">
                                        <summary class="text-muted" style="cursor: pointer;">Afficher le texte original ({{ strtoupper($post->original_language) }})</summary>
                                        <div class="mt-2 opacity-75" lang="{{ $post->original_language }}">
                                            {!! BaseHelper::clean($post->content) !!}
                                        </div>
                                    </details>
                                @endif

                                {!! apply_filters('ads_render', null, 'post_after', ['class' => 'my-2 text-center']) !!}
                            </div>
                        @endif

                        @include(Theme::getThemeNamespace('partials.story.full-article'), [
                            'post' => $post,
                            'body' => $__gnFullBody,
                            'locked' => $__gnFullArticleLocked,
                            'loginUrl' => $__gnMemberLoginUrl,
                            'upstream' => $__gnUpstream,
                        ])

                        {{-- GrimbaNews other angles (sibling cluster posts) --}}
                        @include(Theme::getThemeNamespace('partials.blog.post.partials.other-angles'), ['post' => $post])

                        @php
                            $socials = \Botble\Theme\Supports\ThemeSupport::getSocialSharingButtons($post->url, $__gnTitle, RvMedia::getImageUrl($post->image));
                            $tags = $post->tags;
                        @endphp

                        <div class="echo-financial-area">
                            <div class="content mb-5">
                                <div class="row align-items-center">
                                    @if ($tags->isNotEmpty())
                                        <div @class(['col-lg-6 col-md-6 col-sm-12' => $socials, 'col-12' => ! $socials])>
                                            <div class="details-tag">
                                                <h6>{{ __('Tags:') }}</h6>
                                                @foreach($tags as $tag)
                                                    <a class="py-2" href="{{ $tag->url }}"><button>{{ $tag->name }}</button></a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($socials)
                                        <div @class(['col-lg-6 col-md-6 col-sm-12' => $tags, 'col-12' => $tags->isEmpty() ])>
                                            <div @class(['details-share', 'justify-content-start' => $tags->isEmpty()] )>
                                                <h6>{{ __('Share:') }}</h6>
                                                @foreach($socials as $social)
                                                    <a target="_blank" href="{{ $social['url'] }}" aria-label="{{ __('Share on :name', ['name' => 'Facebook']) }}">
                                                        {!! $social['icon'] !!}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
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

                        <div class="mt-5">
                            {!! dynamic_sidebar('blog_bottom_sidebar') !!}
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 sticky-coloum-item">
                        <div class="echo-right-ct-1">
                            {!! apply_filters('ads_render', null, 'primary_sidebar_before', ['class' => 'my-2 text-center']) !!}

                            {!! dynamic_sidebar('primary_sidebar') !!}

                            {!! apply_filters('ads_render', null, 'primary_sidebar_after', ['class' => 'my-2 text-center']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif{{-- close S148 story-page fallback wrapper --}}
