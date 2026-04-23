@if ($shape == 'circle')
    <div class="echo-feature-area-right-audio-text" @style(['height: 110px' => echo_is_audio_post($post)])>
        <div @style(['margin-bottom: 25px' => echo_is_audio_post($post)])>
            <a href="{{ $post->url }}" title="{{ $post->name }}" class="text-capitalize title-hover text-heading truncate-custom truncate-2-custom">{{ $post->name }}</a>
        </div>
        @if (echo_is_audio_post($post))
            <div class="echo-feature-area-right-site-audio-main-contetn">
                <div class="wrappers">
                    <audio preload="auto" controls>
                        <source src="{{ RvMedia::url(echo_get_post_audio_url($post)) }}" type="audio/mpeg">
                    </audio>
                </div>
            </div>

        @else
            {!! Theme::partial('post-meta', compact('post')) !!}
        @endif
    </div>

@else
    <div class="echo-story-text">
        <h6 @style(['margin-bottom: 5px' => ! echo_is_audio_post($post)])>
            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
        </h6>
        @if (echo_is_audio_post($post))
            <div class="echo-feature-area-right-site-audio-main-contetn">
                <div class="wrappers">
                    <audio preload="auto" controls>
                        <source src="{{ RvMedia::url(echo_get_post_audio_url($post)) }}" type="audio/mpeg">
                    </audio>
                </div>
            </div>
        @else
            {!! Theme::partial('post-meta', compact('post')) !!}
        @endif
    </div>
@endif
