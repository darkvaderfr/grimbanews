@php
    $featuredPost = null;
    $backgroundImage = null;

    if ($categoryIds && is_array($categoryIds)) {
        /** @var \Botble\Blog\Models\Post $featuredPost */
        $featuredPost = \Botble\Blog\Models\Post::query()
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('id', $categoryIds);
            })
            ->wherePublished()
            ->latest()
            ->first();
    }
@endphp

<section class="echo-video-area home-three" @style($variableStyles)>
    <div class="container">
        <div class="echo-video-area-inner">
            @if ($featuredPost)
                <div class="row">
                    <div class="col-lg-7">
                        <div class="banner-content">
                            @if ($category = $featuredPost->firstCategory)
                                {!! Theme::partial('category-badge', compact('category')) !!}
                            @endif
                            <div class="contents">
                                <h2 class="content-title1">
                                    <a href="{{ $featuredPost->url }}" class="truncate-custom truncate-3-custom title-hover"
                                       title="{{ $name = $featuredPost->name }}">
                                        {{ $name }}
                                    </a>
                                </h2>

                                @if ($description = $featuredPost->description)
                                    <p class="desc truncate-custom truncate-3-custom">{!! BaseHelper::clean($description) !!}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if (echo_is_video_post($featuredPost) || echo_is_audio_post($featuredPost))
                        <div class="col-lg-5">
                            <div class="echo-button-wrapper mt--70">
                                {!! Theme::partial('blog.post.partials.action-post', ['post' => $featuredPost]) !!}
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            <div class="echo-bottom-post-area">
                <div class="row gy-4">
                    @foreach($categories as $category)
                        @php
                            $posts = $category->posts()->whereNot('id', $featuredPost->getKey())->take(3)->with('slugable')->latest()->get();
                        @endphp

                        @continue($posts->isEmpty())
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="wrapper">
                                <h5 class="title">{{ $category->name }}</h5>
                                @foreach($posts as $post)
                                    <div class="echo-trending-left-site-post">
                                        <div class="echo-trending-left-site-post-img img-transition-scale">
                                            <a href="{{ $post->url }}">
                                                {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'img-hover']) }}
                                            </a>
                                        </div>
                                        <div class="echo-trending-right-site-post-title">
                                            @if ($category = $post->firstCategory)
                                                {!! Theme::partial('category-badge', compact('category')) !!}
                                            @endif
                                            <a href="{{ $post->url }}" class="text-heading text-capitalize title-hover truncate-custom truncate-2-custom" title="{{ $name = $post->name }}">
                                                {{ $name }}
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
