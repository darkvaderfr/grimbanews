@php
    use App\Support\GrimbaTranslationPresenter as GnTr;

    Theme::layout('grimba-chrome');
    $isGrimbaCategoryPage = (bool) Theme::get('grimbaCategoryPage', false);
    $enableSidebar = $isGrimbaCategoryPage ? false : theme_option('blog_sidebar_enabled', true);
    $postStyle = request()->input('style', theme_option('post_style', 'grid')) ;

    $postCollection = $posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $posts->getCollection()
        : collect($posts);

    GnTr::warm($postCollection);

    $sortForLanguage = static function ($items) {
        return collect($items)
            ->sortBy([
                fn ($post) => GnTr::rankForTargetLocale($post),
                fn ($post) => - (int) optional(GnTr::publishedAt($post))->getTimestamp(),
            ])
            ->values();
    };

    if ($posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
        $posts->setCollection($sortForLanguage($posts->getCollection()));
    } elseif ($posts instanceof \Illuminate\Support\Collection) {
        $posts = $sortForLanguage($posts);
    }

    $postCollection = $posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $posts->getCollection()
        : collect($posts);

    if ($postCollection->isNotEmpty()) {
        (new \Illuminate\Database\Eloquent\Collection($postCollection->all()))->loadMissing('categories');
    }

    // S330 — bulk-warm cluster counts for every card on this page. One
    // SQL query instead of N (one per card). The coverage-bar partial
    // reads from CoverageCounts::get() which is now O(1) here.
    \App\Ground\CoverageCounts::warm($postCollection->pluck('story_cluster_id')->filter()->all());
@endphp

<section @class([
        'echo-hero-section inner echo-feature-area blog-list-content',
        'grimba-category-listing' => $isGrimbaCategoryPage,
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
                        {!! Theme::partial('bias-legend') !!}
                        {{-- S325: dropped feed-balance partial — duplicated the
                              category-header "Couverture sur {topic}" aggregate
                              AND the S316 top-sources rail. Kept bias-legend
                              since it's the L/C/R color key, educational. --}}
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
