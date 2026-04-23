@php
    $shape = Arr::get($config, 'shape', 'square');
@endphp

<div class="echo-feature-area">
    <div @class([
    'widget-blog-post-category echo-feature-area-right-site-full-content',
    'echo-home-1-hero-area-top-story' => $shape == 'square',
])>

        @if ($title = Arr::get($config, 'title'))
            <div class="echo-feature-area-right-site-audio-title">
                <p class="h5">{!! BaseHelper::clean($title) !!}</p>
            </div>
        @endif

        <div class="echo-audio-news-home-1-flexing">
            @foreach($posts as $post)
                @include(Theme::getThemeNamespace('widgets.blog-posts.templates.partials.styles._post-item'))
            @endforeach
        </div>
    </div>

</div>
