<section class="echo-popular-area" @style($variableStyles)>
    <div class="echo-popular-content">
        <div class="container">
            <div class="echo-popular-full-content">
                <div class="echo-popular-tabing">
                    <div class="row gx-6">
                        <div @class([
                                'col-xl-8 col-lg-7 col-md-12' => $sidebar = $shortcode->sidebar,
                                'col-12' => ! $sidebar,
                            ])>
                            @if ($title = $shortcode->title)
                                <div class="echo-home-2-title">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="echo-home-2-main-title">
                                                <h2 @class([
                                                        'text-capitalize',
                                                         'text-center' => $shortcode->title_align == 'center',
                                                         'text-end' => $shortcode->title_align == 'end',
                                                    ])>
                                                    {!! BaseHelper::clean($title) !!}
                                                </h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="blog-categories" data-bb-toggle="ajax-posts">
                                <div class="echo-popular-tabing-btn">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12">
                                            <ul class="echo-popular-tab-button list-unstyled nav nav-tabs" id="myTab" role="tablist">
                                                @foreach($categories as $category)
                                                    <li class="nav-item" role="presentation">
                                                        <button
                                                            @class(['text-uppercase', 'active' => $loop->first])
                                                            data-bs-target="#category-{{ $category->id }}"
                                                            data-bs-toggle="tab"
                                                            type="button"
                                                            role="tab"
                                                            aria-controls="category-{{ $category->id }}"
                                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                                            data-bb-toggle="fetch"
                                                            data-url="{{ route('public.ajax.posts-by-category', $category->id) }}"
                                                            data-limit="{{ $postsPerCategory }}"
                                                        >
                                                            {{ $category->name }}
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-content position-relative">
                                    <div class="wrapper-loading" style="display: none">
                                        <div class="loading-spinner"></div>
                                    </div>

                                    <div class="blog-categories-tab-content">
                                        @if ($firstCategory = $categories->first())
                                            @if ($posts = get_posts_by_category($firstCategory->getKey(), $postsPerCategory))
                                                {!! Theme::partial('shortcodes.blog-categories.partials.posts', ['posts' => $posts, 'category' => $firstCategory]) !!}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($sidebar)
                            <div class="col-xl-4 col-lg-5 col-md-12">
                                <div class="echo-feature-area-right-site-flexing">
                                    {!! dynamic_sidebar($sidebar) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
