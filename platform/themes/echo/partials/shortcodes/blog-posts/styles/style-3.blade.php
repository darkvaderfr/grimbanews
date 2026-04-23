@php
    $reverseRow ??= false;
    $rows = $posts->chunk(3);
@endphp

<section @class(['echo-trending-area', 'echo-feature-area' => $sidebar])
    @style($variableStyles)
>
    <div class="echo-trending-content">
        <div class="container">
            <div class="row gx-6">
                <div @class([
                    'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                    'col-12' => ! $sidebar,
                ])>
                    @if ($title = $shortcode->title)
                        <h2 class="position-relative title-align-{{ $shortcode->title_align ?: 'start' }}">
                            {!! BaseHelper::clean($title) !!}
                        </h2>
                    @endif
                    <div class="echo-trending-full-content">
                        @foreach($rows as $posts)
                            <div @class(['row gx-6', 'flex-row-reverse' => $reverseRow])>
                                <div class="col-xl-6 col-lg-6 col-md-12">
                                    @foreach($posts->skip(1) as $post)
                                        <div class="echo-trending-left-site-post">
                                            <div class="echo-trending-left-site-post-img img-transition-scale position-relative">
                                                <a href="{{ $post->url }}">
                                                    {{ RvMedia::image($post->image, $post->name, 'small', attributes: ['class' => 'img-hover']) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                            </div>
                                            <div class="echo-trending-right-site-post-title">
                                                <a href="{{ $post->url }}" class="text-capitalize title-hover truncate-custom truncate-2-custom" title="{{ $post->name }}">{{ $post->name }}</a>

                                                {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-trending-post-bottom-icons']) !!}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if ($post = $posts->first())
                                    <div class="col-xl-6 col-lg-6 col-md-12">
                                        <div class="echo-trending-right-site-post">
                                            <div class="echo-trending-right-site-post-img img-transition-scale position-relative">
                                                <a href="{{ $post->url }}">
                                                    {{ RvMedia::image($post->image, $post->name, 'medium', attributes: ['class' => 'img-hover']) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                            </div>
                                            <div class="echo-trending-right-site-post-title">
                                                <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                            </div>

                                            {!! Theme::partial('post-meta', ['post' => $post, 'wrapperClass' => 'echo-trending-right-site-like-comment-share-icons']) !!}
                                        </div>
                                        <hr class="echo-hr-home-1-tranding">
                                    </div>
                                @endif
                            </div>
                        @endforeach
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
</section>
