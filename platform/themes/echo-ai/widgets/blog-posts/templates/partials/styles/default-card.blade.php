@if ($sidebar == 'primary_sidebar')
    <div class="echo-trending-news-area home-seven" style="padding-top: 20px !important;">
        <div class="right-side-inner">
@endif
        @if ($title = Arr::get($config, 'title'))
            <div class="hm5-feature-title">
                <div class="hm-5-main-title text-left">
                    <h2>{!! BaseHelper::clean($title) !!}</h2>
                </div>
            </div>
        @endif
        <div class="post-area">
            @foreach($posts as $post)
                <div @class(['echo-hero-baner small', 'last' => $loop->last])>
                    <div class="echo-hero-banner-main-img">
                        <a href="{{ $post->url }}">
                            {{ RvMedia::image($post->image, $post->name, 'thumb', attributes: ['class' => 'banner-image-one']) }}
                        </a>
                    </div>
                    <div class="content">
                        <p class="author">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10" fill="none">
                                <path d="M7.5 1H9.5C9.63261 1 9.75979 1.05268 9.85355 1.14645C9.94732 1.24021 10 1.36739 10 1.5V9.5C10 9.63261 9.94732 9.75979 9.85355 9.85355C9.75979 9.94732 9.63261 10 9.5 10H0.5C0.367392 10 0.240215 9.94732 0.146447 9.85355C0.0526784 9.75979 0 9.63261 0 9.5V1.5C0 1.36739 0.0526784 1.24021 0.146447 1.14645C0.240215 1.05268 0.367392 1 0.5 1H2.5V0H3.5V1H6.5V0H7.5V1ZM6.5 2H3.5V3H2.5V2H1V4H9V2H7.5V3H6.5V2ZM9 5H1V9H9V5Z" fill="currentColor" />
                            </svg>
                            {{ $post->created_at->format('d M Y') }}
                        </p>
                        <div class="echo-hero-title font-weight-bold">
                            <a href="{{ $post->url }}" class="title-hover truncate-2-custom truncate-custom">{{ $post->name }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
@if ($sidebar == 'primary')
        </div>
    </div>
@endif
