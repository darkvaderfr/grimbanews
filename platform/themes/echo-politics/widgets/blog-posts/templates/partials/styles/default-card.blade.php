@php
    $shape = Arr::get($config, 'shape', 'square');
@endphp

<div class="echo-right-ct-1" style="margin-bottom: 30px">
    <div class=" echo-home-1-hero-area-top-story">
        @if ($title = Arr::get($config, 'title'))
            <h5>{!! BaseHelper::clean($title) !!}</h5>
        @endif

        <div class="right-side-banner">
            @foreach($posts as $post)
                @include(Theme::getThemeNamespace('widgets.blog-posts.templates.partials.styles._post-item'))
            @endforeach
        </div>
    </div>
</div>

