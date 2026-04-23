@php
    Theme::layout('full-width');
    $enableSidebar = theme_option('enable_sidebar', 'yes');
    $postStyle = request()->query('style') ?: theme_option('post_style', 'grid');

@endphp

<section @class(['echo-hero-section inner echo-feature-area', 'inner-2' => $postStyle == 'list'])>
    <div class="echo-hero">
        <div class="container">
            <div class="echo-full-hero-content inner-category-1">
                <div class="row gx-5 sticky-coloum-wrap">
                    <div @class([
                        'col-xl-8 col-lg-7 col-md-12' => $enableSidebar == 'yes',
                        'col-12' => $enableSidebar == 'no',
                    ])>
                        @if ($posts->isNotEmpty())
                            {!! Theme::partial('blog.posts', compact('posts')) !!}
                        @else
                            <h1 class="text-center">{{ __('Ops! No results found') }}</h1>
                            <p class="text-center">
                                {{ __('We couldn’t find what you searched for. Try searching again or') }}
                                <a
                                    class="link-primary custom-link"
                                    href="{{ route('public.single', 'blog') }}"
                                >{{ __('Back here') }}</a>
                            </p>
                        @endif
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
