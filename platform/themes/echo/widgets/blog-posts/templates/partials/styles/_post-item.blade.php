@switch($shape)
    @case('circle')
        <div class="echo-feature-area-right-site-audio-news">
            <div class="echo-feature-area-right-img">
                <a href="{{ $post->url }}">
                    {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'image-circle']) }}
                </a>
            </div>
            @include(Theme::getThemeNamespace('widgets.blog-posts.templates.partials.styles.post-content'), compact('shape', 'post'))
        </div>
        @break

    @default
        <div class="echo-top-story">
            <div class="echo-story-picture img-transition-scale">
                <a href="{{ $post->url }}">
                    {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'img-hover']) }}
                </a>
            </div>
            @include(Theme::getThemeNamespace('widgets.blog-posts.templates.partials.styles.post-content'), compact('shape', 'post'))
        </div>
        @break
@endswitch
