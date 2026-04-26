@php
    Theme::layout('grimba-chrome');
    Theme::set('isDetailPage', true);

    // Branded OG image per post.
    $ogUrl = url('/og/post/' . $post->id . '.png');
    SeoHelper::openGraph()->setImage($ogUrl);
    SeoHelper::openGraph()->addProperty('image:width', '1200');
    SeoHelper::openGraph()->addProperty('image:height', '630');
    SeoHelper::twitter()->setType('summary_large_image');
    SeoHelper::twitter()->addImage($ogUrl);
@endphp

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

@php

    $descriptionStyle = theme_option('blog_description_style');
    $authorStyle = theme_option('blog_author_style');
    $url = $post->url;

    // S170 — translation feature dropped. Posts now always render in
    // their stored language. translated_* columns remain in the DB
    // (still populated by the cron) but are not consulted on read.
    // Vader's call: "let's get rid of the translation feature and
    // fully replicate ground news article display."
    $__gnTitle   = $post->name;
    $__gnDesc    = $post->description;
    // Compatibility shims so legacy branches that still reference
    // these vars don't throw — render-time they're now fixed.
    $__gnMode    = 'original';
    $__gnHasTr   = false;
    $__gnOriginalTitle = null;
    $__gnTarget  = 'fr';

    Theme::set('breadcrumb_background_image', $post->getMetaData('breadcrumb_background_image', true));
    Theme::set('breadcrumb_background_color', $post->getMetaData('breadcrumb_background_color', true));
    Theme::set('breadcrumb_text_color', $post->getMetaData('breadcrumb_text_color', true));

    // S148 — story-page mode. When this post belongs to a story
    // cluster with at least 2 published articles total, render the
    // GroundNews-style cluster view instead of the legacy single-post
    // layout. The legacy layout is kept as fallback for orphan posts
    // (no cluster) and clusters of 1 (no comparison value).
    $__gnClusterPosts = collect();
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
    }
@endphp

@if($__gnIsStoryPage)
    <section class="grimba-story container py-4 py-md-5">
        <div class="row gx-4 gx-lg-5">
            <div class="col-lg-8 col-12 mb-4">

                @php
                    // Best hero image for the story: current post's image
                    // first, else any cluster post that has one. The
                    // cards below each carry their own image too — this
                    // is the "above the fold" anchor.
                    $__gnHero = $post->image ?: $__gnClusterPosts->pluck('image')->filter()->first();
                @endphp

                @if($__gnHero)
                    <div class="grimba-story-hero glass-panel p-0 mb-3" style="overflow:hidden;">
                        <div class="ratio ratio-21x9" style="background:rgba(0,0,0,0.04);">
                            <img src="{{ \Botble\Media\Facades\RvMedia::getImageUrl($__gnHero) }}"
                                 alt="{{ $__gnTitle }}"
                                 loading="eager"
                                 style="object-fit:cover; width:100%; height:100%;">
                        </div>
                    </div>
                @endif

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
                        <span class="grimba-methodology__kicker">Histoire</span>
                        @if($post->source_name)
                            <span class="opacity-50">·</span>
                            <span class="opacity-75">Lu d'abord chez {{ $post->source_name }}</span>
                        @endif
                        <span class="opacity-50">·</span>
                        <span class="opacity-75">
                            {{ $__gnClusterPosts->count() }} {{ $__gnClusterPosts->count() === 1 ? 'couverture' : 'couvertures' }}
                        </span>
                        @if($__gnLatest)
                            <span class="opacity-50">·</span>
                            <span class="opacity-75">Mis à jour {{ $__gnLatest->locale('fr')->diffForHumans() }}</span>
                        @endif
                    </div>

                    <h1 class="grimba-methodology__title m-0 mb-3"
                        style="font-size:clamp(28px, 3.6vw, 44px); line-height:1.1; letter-spacing:-0.5px;">
                        {{ $__gnTitle }}
                    </h1>

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
                                Tous
                            </button>
                            @foreach(['left' => ['Gauche','#3b82f6'], 'center' => ['Centre','#a8a8a8'], 'right' => ['Droite','#e84c3d']] as $b => [$lbl,$col])
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
                                title="Voir la distribution des biais">
                            ⚖️ Comparaison des biais
                        </button>
                    </div>

                    {{-- AI summary section. NobuAI summaries (S110) are
                         not generated yet — when they ship, $post->summary_nobuai
                         will populate this. Until then we render the post's
                         description as a single bullet so the section
                         doesn't look broken. --}}
                    @php
                        $__gnSummaryItems = [];
                        if (! empty($post->summary_nobuai ?? null)) {
                            $__gnSummaryItems = array_filter(array_map(
                                'trim',
                                preg_split("/\r\n|\n|\r/", (string) $post->summary_nobuai)
                            ));
                        } elseif ($__gnDesc) {
                            $__gnSummaryItems = [strip_tags($__gnDesc)];
                        }
                    @endphp

                    @php
                        // S163 — full-article reading. Setting gates
                        // the feature (paid-tier flag); rendering
                        // gates on the post actually having extracted
                        // full_content. Translated full body is
                        // post.translated_content (S91) when the
                        // reader is in NobuAI mode.
                        $__gnFullActive  = (bool) setting('grimba_full_article_active', false);
                        $__gnFullBody = $__gnFullActive ? ($post->full_content ?? null) : null;
                    @endphp

                    @if(! empty($__gnSummaryItems))
                        {{-- S170 — Insights par NobuAI block, GroundNews-style.
                             Toggle-collapsible via <details>. Reader can hide
                             the AI summary if they don't want it. --}}
                        <details open class="mt-3" style="cursor:default;">
                            <summary style="cursor:pointer; list-style:none; display:flex; align-items:center; gap:8px; font-family:'Public Sans',system-ui,sans-serif; font-size:13px; font-weight:700; letter-spacing:0.4px; text-transform:uppercase; color:var(--gn-ink,#1a1713); opacity:0.75; margin-bottom:10px;">
                                <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:linear-gradient(135deg,#6b7280,#1a1713);"></span>
                                Insights par NobuAI
                                <span style="margin-left:auto; font-size:11px; opacity:0.6; font-weight:500; text-transform:none;">cliquer pour masquer</span>
                            </summary>
                            @if(count($__gnSummaryItems) === 1)
                                <p class="m-0" style="font-size:15px; line-height:1.55;">{{ $__gnSummaryItems[0] }}</p>
                                <p class="small opacity-55 mt-2 mb-0">Résumé éditorial à venir — couverture en cours.</p>
                            @else
                                <ul class="m-0 ps-3" style="font-size:15px; line-height:1.55;">
                                    @foreach(array_slice($__gnSummaryItems, 0, 6) as $line)
                                        <li class="mb-2">{{ $line }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </details>
                    @endif
                </header>

                @if($__gnFullBody)
                    <details class="grimba-full-article glass-panel p-3 p-md-4 mb-3" style="cursor:pointer;">
                        <summary style="cursor:pointer; font-family:'Public Sans',system-ui,sans-serif; font-weight:700; font-size:14px; letter-spacing:0.4px; text-transform:uppercase; color:var(--gn-ink,#1a1713);">
                            Lire l'article complet ↓
                            <span class="small opacity-65 ms-2" style="font-weight:500; text-transform:none; letter-spacing:0;">
                                · {{ \Illuminate\Support\Str::words(strip_tags($__gnFullBody), 1, '') === '' ? '' : (\Illuminate\Support\Str::wordCount(strip_tags($__gnFullBody)) . ' mots') }}
                            </span>
                        </summary>
                        <div class="grimba-full-article__body mt-3" style="font-family:'Fraunces','Playfair Display',Georgia,serif; font-size:17px; line-height:1.65; color:var(--gn-ink,#1a1713);">
                            {!! BaseHelper::clean($__gnFullBody) !!}
                        </div>
                        @php
                            $__gnUpstream = \Illuminate\Support\Facades\DB::table('rss_feed_items')
                                ->where('post_id', $post->id)->value('link')
                                ?? \Illuminate\Support\Facades\DB::table('newsapi_items')
                                    ->where('post_id', $post->id)->value('article_url');
                        @endphp
                        @if($__gnUpstream)
                            <p class="small opacity-60 mt-3 mb-0" style="font-family:'Public Sans',system-ui,sans-serif;">
                                Source originale :
                                <a href="{{ $__gnUpstream }}" target="_blank" rel="noopener" style="color:#c0392b;">
                                    {{ $post->source_name ?? 'lire chez l\'éditeur' }} ↗
                                </a>
                            </p>
                        @endif
                    </details>
                @endif

                @include(Theme::getThemeNamespace('partials.story.article-list'), [
                    'clusterPosts' => $__gnClusterPosts,
                    'currentPost'  => $post,
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
<section class="echo-hero-section inner inner-post echo-feature-area bg-white blog-post-details-content">
    <div class="echo-hero">
        <div class="container">
            {{-- S170 — translation feature dropped. The legacy single-
                 post layout had a translate-picker here; gone. --}}
            <div class="echo-full-hero-content">
                <div class="row gx-5 sticky-coloum-wrap">
                    <div class="col-xl-8 col-lg-8">
                        <div class="echo-hero-baner">
                            <div class="echo-inner-img-ct-1 img-transition-scale mb-3 position-relative">
                                @if (defined('GALLERY_MODULE_SCREEN_NAME') && ! empty($galleries = gallery_meta_data($post)))
                                    {!! render_object_gallery($galleries) !!}
                                @elseif ($image = $post->image)
                                    {{ RvMedia::image($image, $post->name, attributes: ['class' => 'post-style-1-frist-hero-img']) }}
                                @endif

                                {!! Theme::partial('blog.post.partials.action-post', ['post' => $post, 'enableActionAudio' => false]) !!}
                            </div>

                            {{-- Category row --}}
                            <div class="d-flex align-items-center gap-2 mb-2">
                                @include(Theme::getThemeNamespace('partials.category-badge'), ['post' => $post])
                            </div>

                            {{-- GrimbaNews source attribution --}}
                            @include(Theme::getThemeNamespace('partials.blog.post.partials.source-attribution'), ['post' => $post])

                            <h2 class="echo-hero-title text-capitalize font-weight-bold mt-0">
                                <a title="{{ $__gnTitle }}" href="{{ $url }}" class="title-hover truncate-custom truncate-3-custom">{{ $__gnTitle }}</a>
                            </h2>
                            @if ($__gnMode !== 'original' && $__gnHasTr)
                                <div class="mt-2 mb-2">
                                    {!! Theme::partial('nobuai-chip', ['size' => 'md']) !!}
                                </div>
                            @endif
                            @if ($__gnOriginalTitle)
                                <p class="small opacity-75 mb-3" lang="{{ $post->original_language }}" title="{{ $__gnOriginalTitle }}">
                                    {{ $__gnOriginalTitle }}
                                </p>
                            @elseif ($__gnMode !== 'original' && ! $__gnHasTr && ($post->original_language ?? null) && $post->original_language !== $__gnTarget)
                                <p class="small opacity-50 mb-2" lang="{{ $post->original_language }}">
                                    <em>— traduction en attente ({{ strtoupper($post->original_language) }})</em>
                                </p>
                            @endif

                            {!! Theme::partial('post-meta', [
                                'post' => $post,
                                'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share post-meta',
                                'isSingle' => true,
                            ]) !!}

                            {{-- S141 — "see other coverages" chip when the
                                 post belongs to a multi-source cluster --}}
                            <div class="text-center text-md-start mt-3">
                                {!! Theme::partial('comparatif-cta', ['post' => $post]) !!}
                            </div>

                            @if (echo_is_audio_post($post))
                                <div class="wrapper-audio-control">
                                    <audio controls>
                                        <source src="{{ RvMedia::url(echo_get_post_audio_url($post)) }}" type="audio/ogg">
                                    </audio>
                                </div>
                            @endif

                            @if ($description = $__gnDesc)
                                @if ($descriptionStyle == 'drop_cap')
                                    <p class="echo-hero-discription">
                                        @if ($firstChar = substr($description, 0, 1))
                                            <span class="text-dropped-cap">{!! BaseHelper::clean($firstChar) !!}</span>
                                        @endif

                                        @if ($descriptionRemaining = substr($description, 1))
                                            {!! BaseHelper::clean($descriptionRemaining) !!}
                                        @endif
                                    </p>
                                    <div class="clearfix"></div>
                                @else
                                    <p class="echo-hero-discription">{!! BaseHelper::clean($description) !!}</p>
                                @endif
                            @endif
                        </div>

                        @php
                            // S91: swap body content when reader asked for
                            // auto translation AND we have a translated_content
                            // row in the right locale. 'both' mode stacks:
                            // translation first (primary reading), original
                            // beneath in a collapsed <details> so a curious
                            // reader can compare.
                            $__gnBody       = ($__gnMode !== 'original' && $__gnHasTr && $post->translated_content)
                                ? $post->translated_content
                                : $post->content;
                            $__gnShowOrig   = ($__gnMode === 'both' && $__gnHasTr && $post->translated_content);
                        @endphp
                        @if ($content = $__gnBody)
                            <div class="ck-content">
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

                        {{-- GrimbaNews other angles (sibling cluster posts) --}}
                        @include(Theme::getThemeNamespace('partials.blog.post.partials.other-angles'), ['post' => $post])

                        @php
                            $socials = \Botble\Theme\Supports\ThemeSupport::getSocialSharingButtons($post->url, $post->name, RvMedia::getImageUrl($post->image));
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
