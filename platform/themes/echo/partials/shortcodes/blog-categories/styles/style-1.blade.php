@if ($firstCategory || $categories->isNotEmpty())
    <section class="echo-de-category-area" @style($variableStyles)>
        <div class="echo-de-category-area-content">
            <div class="container">
                <div class="echo-de-category-full-content">
                    <div class="echo-de-category-title-btn">
                        @if ($title = $shortcode->title)
                            <h2 class="text-capitalize title-align-{{ $shortcode->title_align ?: 'start' }}">{!! BaseHelper::clean($title) !!}</h2>
                        @endif

                        @if ($firstCategory)
                            <a href="{{ $firstCategory->url }}" class="text-capitalize echo-py-btn">{{ __('View All Posts') }}</a>
                        @endif
                    </div>

                    <div class="row gx-5">
                        @foreach($categories as $category)
                            @php
                                $posts = $category->posts()->take(3)->with('slugable')->latest()->get();
                            @endphp

                            @continue($posts->isEmpty())
                            <div class="col-xl-4 col-lg-4 col-md-6">
                                <div class="echo-de-category-content echo-responsive-wd">
                                    <h3 class="text-capitalize">{{ $category->name }}</h3>
                                    <hr>
                                    @php
                                        $posts = get_posts_by_category($category->getKey(), $postsPerCategory);
                                    @endphp
                                    @foreach($posts as $post)
                                        <div class="echo-de-category-content-img-title">
                                            <div class="echo-de-category-content-img img-transition-scale position-relative">
                                                <a href="{{ $post->url }}">
                                                    {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'img-hover']) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                            </div>

                                            <div class="echo-de-category-content-title">
                                                <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>

                                                {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-de-category-read']) !!}
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="echo-de-category-show-more-btn">
                                        <a href="{{ $category->url }}" class="text-capitalize echo-py-btn">{{ __('View More') }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
