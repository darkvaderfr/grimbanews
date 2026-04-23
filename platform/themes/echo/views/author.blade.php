@php
    $enableSidebar = theme_option('enable_sidebar', 'yes');
    $postStyle = request()->query('style') ?: theme_option('post_style', 'grid');
    Theme::layout('full-width');
    $authorSocials = $author->getMetaData('social_links', true);

    if ($authorSocials !== null && $authorSocials !== '[]') {
        $socials = Theme::convertSocialLinksToArray($authorSocials);
    } else {
        $socials = [];
    }
@endphp
<div class="author-detail-page echo-breadcrumb-area-2" @if ($bgImage = theme_option('breadcrumb_background_image')) style="background-image: url({{ RvMedia::getImageUrl($bgImage) }})" @endif>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="echo-author-content">
                    @if ($avatar = $author->avatar->url)
                        <div class="echo-author-picture">
                            <img src="{{ RvMedia::getImageUrl($avatar) }}" alt="{{ $author->name }}">
                        </div>
                    @endif
                    <div class="echo-author-info">
                        <h5 class="text-capitalize">{{ $author->name }}</h5>
                        @if ($description = $author->description)
                        <p>{!! BaseHelper::clean($description) !!}</p>
                        @endif

                        @if ($socials)
                            <div class="col-lg-12">
                                <ul class="author-social">
                                    @foreach($socials as $social)
                                        @continue((! $social->getIcon() && ! $social->getImage()) || ! $social->getUrl())

                                        <li>
                                            <a href="{{ $social->getUrl() }}" target="_blank" rel="noopener noreferrer">
                                                @if ($social->getImage())
                                                    {!! $social->getImageHtml() !!}
                                                @else
                                                    {!! $social->getIconHtml() !!}
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<section @class(['echo-hero-section inner echo-feature-area', 'inner-2' => $postStyle == 'list'])>
    <div class="echo-hero">
        <div class="container">
            <div class="echo-full-hero-content inner-category-1">
                <div class="row gx-5 sticky-coloum-wrap">
                    <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $enableSidebar == 'yes',
                        'col-12' => $enableSidebar == 'no',
                    ])>
                        {!! Theme::partial('blog.posts', compact('posts')) !!}
                    </div>

                    @if ($enableSidebar == 'yes')
                        <div class="col-xl-4 col-lg-5 col-md-12 sticky-coloum-item">
                            <div class="echo-right-ct-1">

                            {!! apply_filters('ads_render', null, 'primary_sidebar_before', ['class' => 'my-2 text-center']) !!}

                            {!! dynamic_sidebar('primary_sidebar') !!}

                            {!! apply_filters('ads_render', null, 'primary_sidebar_after', ['class' => 'my-2 text-center']) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
