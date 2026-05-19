{{-- Wave KKKKKK — twitter:image emitted manually AFTER Theme::header()
     so SeoHelper's singleton-state Card::addImage() can't accumulate
     across requests (would emit numbered twitter:image{0}+{1} variants
     Twitter doesn't honor). The URL was resolved in partials.seo-meta-config
     and stashed in Theme::set('__grimba_og_image_resolved').

     Wave UUUUUU — article-specific OG meta. The OG protocol uses bare
     `article:*` prefix; Botble's addProperty() auto-prefixes with `og:`
     so we emit these as raw <meta> tags here when post.blade has set
     the corresponding Theme:: keys. --}}
<meta name="twitter:image" content="{{ Theme::get('__grimba_og_image_resolved') ?: url('/og/home.png') }}">
{{-- Gate by isDetailPage so stale Theme::set state from a prior post
     render (laravel-octane / shared-kernel test process) doesn't leak
     article:* meta into a non-article page. post.blade sets isDetailPage.
     After emission, clear the keys so a subsequent request without
     post.blade re-setting them starts clean. --}}
@if(Theme::get('isDetailPage'))
    @if($__grimbaArticlePublishedTime = Theme::get('grimba_article_published_time'))
        <meta property="article:published_time" content="{{ $__grimbaArticlePublishedTime }}">
    @endif
    @if($__grimbaArticleModifiedTime = Theme::get('grimba_article_modified_time'))
        <meta property="article:modified_time" content="{{ $__grimbaArticleModifiedTime }}">
    @endif
    @if($__grimbaArticleAuthor = Theme::get('grimba_article_author'))
        <meta property="article:author" content="{{ $__grimbaArticleAuthor }}">
    @endif
@endif
@php
    Theme::set('isDetailPage', null);
    Theme::set('grimba_article_published_time', null);
    Theme::set('grimba_article_modified_time', null);
    Theme::set('grimba_article_author', null);
@endphp
