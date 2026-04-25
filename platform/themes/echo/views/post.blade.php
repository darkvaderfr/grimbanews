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

    // S89: single-post translation respects the grimba_translate reader
    // cookie the same way the grid card + hero do. When auto/both is set
    // and we have a translated row that targets the reader's locale,
    // swap title + description + content (content stays untranslated in
    // this sprint — only name + description are translated at ingest
    // time; the full body is S91 follow-up when Vader wires DeepL doc-
    // translate or an equivalent).
    $__gnMode   = (string) (request()->cookie('grimba_translate') ?? 'original');
    if (! in_array($__gnMode, ['original', 'auto', 'both'], true)) $__gnMode = 'original';
    $__gnTarget = (string) (request()->cookie('grimba_lang') ?? 'fr');
    $__gnHasTr  = ! empty($post->translated_name)
        && ($post->translated_to ?? null) === $__gnTarget
        && ($post->original_language ?? null) !== $__gnTarget;

    $__gnTitle   = ($__gnMode !== 'original' && $__gnHasTr) ? $post->translated_name : $post->name;
    $__gnOriginalTitle = ($__gnMode === 'both' && $__gnHasTr) ? $post->name : null;
    $__gnDesc    = ($__gnMode !== 'original' && $__gnHasTr && $post->translated_description)
        ? $post->translated_description
        : $post->description;

    Theme::set('breadcrumb_background_image', $post->getMetaData('breadcrumb_background_image', true));
    Theme::set('breadcrumb_background_color', $post->getMetaData('breadcrumb_background_color', true));
    Theme::set('breadcrumb_text_color', $post->getMetaData('breadcrumb_text_color', true));
@endphp

<section class="echo-hero-section inner inner-post echo-feature-area bg-white blog-post-details-content">
    <div class="echo-hero">
        <div class="container">
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
