@if ($posts->isNotEmpty())
    @php
        $alignment = Arr::get($config, 'alignment', 'center');
    @endphp

    <div class="d-none d-lg-flex w-100 justify-content-{{ $alignment }}">
        <div class="breaking-news-content">
            <div class="echo-meta-total-jobs">
                @if ($image = Arr::get($config, 'image'))
                    <div class="echo-meta-jobs-icons" style="flex-shrink: 0!important;">
                        {{ RvMedia::image($image, Arr::get($config, 'name'), attributes: ['loading' => false]) }}
                    </div>
                @endif

                <div class="echo-meta-jobs-text">
                    <div class="swiper rt-treding-slider10" data-delay-time="{{ Arr::get($config, 'delay_time', 2000) }}">
                        <div class="swiper-wrapper">
                            @foreach ($posts as $post)
                                <div class="swiper-slide">
                                    <a href="{{ $post->url }}" title="{{ $post->name }}"><span class="truncate-custom truncate-1-custom">{{ $post->name }}</span></a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

