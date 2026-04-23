@php
    $rows = $posts->chunk(3);

    $titleAlign = $shortcode->title_align ?: 'center';
@endphp
<section class="echo-trending-area home-three"
    @style($variableStyles)
>
    <div class="echo-trending-content">
        <div class="container">
            @if ($title = $shortcode->title)
                <h2 @class(['section-title',
                    'text-center' => $titleAlign == 'center',
                    'text-end' => $titleAlign == 'end',
                    'text-start' => $titleAlign == 'start',
                 ])>
                    {!! BaseHelper::clean($title) !!}
                </h2>
            @endif
            <div class="echo-trending-full-content">
                @foreach($rows as $posts)
                    <div @class(['row gx-6', 'flex-row-reverse' => $shortcode->style == 'style-2'])>
                        @if ($firstPost = $posts->first())
                            <div class="col-xl-6">
                                <div class="echo-trending-right-site-post">
                                    <div class="echo-trending-right-site-post-img img-transition-scale position-relative">
                                        <a href="{{ $firstPost->url }}">
                                            {{ RvMedia::image($firstPost->image, $firstPost->name , 'large', attributes: ['class' => 'img-hover']) }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', ['post' => $firstPost]) !!}
                                    </div>
                                    <div class="echo-trending-right-site-post-title">
                                        <a href="{{ $firstPost->url }}" title="{{ $name = $firstPost->name }}" class="title-hover text-capitalize text-heading mb-3 truncate-custom truncate-2-custom">{{ $name }}</a>
                                    </div>

                                    {!! Theme::partial('post-meta', ['post' => $firstPost, 'wrapperClass' => 'echo-trending-right-site-like-comment-share-icons']) !!}
                                </div>
                            </div>
                        @endif


                        <div class="col-xl-6">
                            @foreach($posts->skip(1) as $post)
                                <div class="echo-trending-left-site-post">
                                    <div class="echo-trending-left-site-post-img img-transition-scale position-relative">
                                        <a href="{{ $post->url }}">
                                            {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                        </a>

                                        {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                    </div>
                                    <div class="echo-trending-right-site-post-title">
                                        @if ($category = $post->firstCategory)
                                            {!! Theme::partial('category-badge', compact('category')) !!}
                                        @endif

                                        <a href="{{ $post->url }}" class="text-capitalize text-heading title-hover truncate-custom truncate-2-custom" title="{{ $name = $post->name }}">
                                            {{ $post->name }}
                                        </a>

                                        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-trending-post-bottom-icons']) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
