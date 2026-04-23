@php
    $enableActionAudio = $enableActionAudio ?? true;
@endphp

@if (echo_is_video_post($post))
    <div class="echo-hm2-video-icons">
        <div class="vedio-icone">
            <a class="play-video popup-youtube video-play-button" href="{{ echo_get_post_video_url($post) }}" aria-label="{{ __('Play Video') }}">
                <span></span>
            </a>
        </div>
    </div>
@elseif (echo_is_audio_post($post) && $enableActionAudio)
    <div class="echo-hm2-video-icons action-play-audio">
        <div class="vedio-icone">
            <a class="video-play-button" href="{{ $post->url }}" aria-label="{{ __('Play Audio') }}">
                <i class="fa-solid fa-volume"></i>
            </a>
        </div>
    </div>
@endif
