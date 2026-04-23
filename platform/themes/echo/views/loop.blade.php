@php
    $enableSidebar = theme_option('blog_sidebar_enabled', true);
    $postStyle = request()->input('style', theme_option('post_style', 'card')) ;
@endphp

<section @class([
        'echo-hero-section inner echo-feature-area blog-list-content',
        'inner-2' => $postStyle == 'list',
        'blog-item-grid' => $postStyle == 'grid',
        'inner-3' => $postStyle == 'grid' || $postStyle == 'mixed'
     ])>
    <div class="echo-hero">
        <div class="container">
            <div class="echo-full-hero-content inner-category-1">
                <div class="row gx-5 sticky-coloum-wrap">
                    <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $enableSidebar,
                        'col-12' => ! $enableSidebar,
                    ])>
                        {!! Theme::partial('blog.posts', compact('posts', 'postStyle', 'enableSidebar')) !!}
                    </div>

                    @if ($enableSidebar)
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
