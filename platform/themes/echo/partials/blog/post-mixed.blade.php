@php
    $postChunks = $posts->chunk(9);
@endphp

@if ($postChunks->isNotEmpty())
    <div class="echo-ct-style-bg-color">
        @foreach($postChunks as $posts)
            <div class="echo-popular-item-category">
                <div class="row">
                    <div class="echo-griding-ct-style-3 row">
                        @foreach($posts->take(4) as $post)
                            <div class="col-sm-6 mb-4">
                                {!! Theme::partial('blog.post.partials.items.grid', compact('post')) !!}
                            </div>
                        @endforeach
                    </div>

                    @foreach($posts->skip(4) as $post)
                        @if ($loop->first)
                            {!! Theme::partial('blog.post.partials.items.card', ['post' => $post, 'classWrapper' => 'banner-inner-3']) !!}
                        @else
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="echo-popular-cat-content">
                                    <div class="echo-popular-cat-img img-transition-scale">
                                        <a href="{{ $post->url }}">
                                            {{ RvMedia::image($post->image, $post->name, 'thumb') }}
                                        </a>
                                    </div>
                                    <div class="echo-popular-cat-title">
                                        <h5 class="text-capitalize">
                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                        </h5>

                                        {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-popular-cat-view']) !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif
