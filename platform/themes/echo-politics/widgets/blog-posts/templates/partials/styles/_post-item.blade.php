@switch($shape)
    @case('circle')
        <div class="echo-hero-baner small">
            <div class="echo-hero-banner-main-img">
                <a href="{{ $post->url }}">
                    {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'banner-image-one']) }}
                </a>
            </div>
            <div class="content">
                {!! Theme::partial('category-badge', compact('post')) !!}
                <div class="echo-hero-title font-weight-bold">
                    <a href="{{ $post->url }}" class="title-hover truncate-2-custom truncate-custom">{{ $post->name }}</a>
                </div>
                <p class="echo-story-text">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="10" viewBox="0 0 11 10" fill="none">
                            <path d="M8 1H10C10.1326 1 10.2598 1.05268 10.3536 1.14645C10.4473 1.24021 10.5 1.36739 10.5 1.5V9.5C10.5 9.63261 10.4473 9.75979 10.3536 9.85355C10.2598 9.94732 10.1326 10 10 10H1C0.867392 10 0.740215 9.94732 0.646447 9.85355C0.552678 9.75979 0.5 9.63261 0.5 9.5V1.5C0.5 1.36739 0.552678 1.24021 0.646447 1.14645C0.740215 1.05268 0.867392 1 1 1H3V0H4V1H7V0H8V1ZM7 2H4V3H3V2H1.5V4H9.5V2H8V3H7V2ZM9.5 5H1.5V9H9.5V5Z" fill="#9F68F5"/>
                        </svg>
                        {{ $post->created_at->format('d M Y') }}
                    </span>
                </p>
            </div>
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
