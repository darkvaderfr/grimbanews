@php
    Theme::layout('full-width');
    Theme::set('isDetailPage', true);

    $descriptionStyle = theme_option('blog_description_style');
    $authorStyle = theme_option('blog_author_style');
    $url = $post->url;

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

                            <h2 class="echo-hero-title text-capitalize font-weight-bold mt-0">
                                <a title="{{ $post->name }}" href="{{ $url }}" class="title-hover truncate-custom truncate-3-custom">{{ $post->name }}</a>
                            </h2>

                            {!! Theme::partial('post-meta', [
                                'post' => $post,
                                'wrapperClass' => 'echo-hero-area-titlepost-post-like-comment-share post-meta',
                                'isSingle' => true,
                            ]) !!}

                            @if (echo_is_audio_post($post))
                                <div class="wrapper-audio-control">
                                    <audio controls>
                                        <source src="{{ RvMedia::url(echo_get_post_audio_url($post)) }}" type="audio/ogg">
                                    </audio>
                                </div>
                            @endif

                            @if ($description = $post->description)
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

                        @if ($content = $post->content)
                            <div class="ck-content">
                                {!! apply_filters('ads_render', null, 'post_before', ['class' => 'my-2 text-center']) !!}

                                {!! BaseHelper::clean($content) !!}

                                {!! apply_filters('ads_render', null, 'post_after', ['class' => 'my-2 text-center']) !!}
                            </div>
                        @endif

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
