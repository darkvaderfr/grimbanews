<div class="echo-home-1-hero-area-top-story story-review">
    @if ($title = Arr::get($config, 'title'))
        <h5 class="text-center">{!! BaseHelper::clean($title) !!}</h5>
    @endif
    @foreach ($posts as $post)
        <div class="echo-top-story">
            @if ($image = $post->image)
                <div class="echo-story-picture img-transition-scale"
                     @if (($score = $post->getMetaData('score', true))) style="--score: '{{ $score }}'"@endif
                >
                    <a href="{{ $post->url }}" class="orderly-post">
                        {{ RvMedia::image($image, $post->name, attributes: ['class' => 'img-hover scored-card-image']) }}
                    </a>

                    @if (echo_is_video_post($post))
                        <a href="{{ echo_get_post_video_url($post) }}" class="orderly-post-icon-video play-video popup-youtube video-play-button" aria-label="{{ __('Play Video') }}">
                            <i class="fa-solid fa-play"></i>
                        </a>
                    @elseif (echo_is_audio_post($post))
                        <a href="{{ $post->url }}" class="orderly-post-icon-volume">
                            <i class="fa-solid fa-volume"></i>
                        </a>
                    @endif
                </div>
            @endif
            <div class="echo-story-text">
                <h3 class="number">{{ '0' . $loop->index + 1 }}</h3>
                <div class="content">
                    <h6><a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a></h6>

                    {!! Theme::partial('post-meta', compact('post')) !!}
                </div>
            </div>
        </div>
    @endforeach
</div>
