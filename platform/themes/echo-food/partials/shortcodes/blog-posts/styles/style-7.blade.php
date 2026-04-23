@php
    $latestTwoPosts = $posts->count() >= 3 ? $posts->pop(2) : collect();
@endphp

<div class="echo-banner-area home-nine" @style($variableStyles)>
    <div class="hm-9-container">
        <div class="row gx-5">
            <div @class([
                'col-xl-8 col-lg-7 col-md-12' => $sidebar,
                'col-12' => ! $sidebar,
            ])>
                <div class="banner-inner">
                    <div class="row">
                        <div class="col-xl-9">
                            <div class="slider-area">
                                <div class="swiper swiper-container">
                                    <div class="swiper-wrapper">
                                        @foreach($posts as $post)
                                            @php
                                                $backgroundImage = $post->image ? RvMedia::url($post->image) : null;
                                            @endphp
                                            <div class="swiper-slide">
                                                <div class="echo-hero-baner" @style(["background-image: url($backgroundImage) !important;" => $backgroundImage])>
                                                    <div class="content">
                                                        {!! Theme::partial('category-badge', compact('post')) !!}
                                                        <div class="echo-hero-title font-weight-bold">
                                                            <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                        </div>

                                                        @if ($description = $post->description)
                                                            <p class="echo-hero-discription truncate-custom truncate-2-custom">{!! BaseHelper::clean($description) !!}</p>
                                                        @endif
                                                        <a href="{{ $post->url }}" class="read-more-btn">{{ __('Read More') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="swiper-btn-area">
                                    <div class="swiper-btn swiper-button-prev">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="23" height="17" viewBox="0 0 23 17" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.01632 5.81948C7.40401 4.23496 8.22179 2.21457 8.4508 0.406511L6.59427 0.17749C6.40752 1.65196 5.72852 3.32441 4.60078 4.6121C3.49167 5.87854 1.97732 6.74694 0.0636101 6.75442C0.0552711 6.75439 0.0469322 6.75438 0.0385818 6.75438V8.6008L0.0529995 8.60088H0.0675735C1.92013 8.61101 3.42542 9.59221 4.55679 11.0994C5.70222 12.6252 6.39964 14.618 6.5923 16.3926L8.45263 16.1959C8.22951 14.1408 7.38631 11.5067 6.01632 9.68165C5.63678 9.17603 5.54193 9.02134 5.06591 8.60089L22.4902 8.60088V6.75446L5.06591 6.75446C5.40862 6.46537 5.7256 6.15144 6.01632 5.81948Z" fill="#EBEBEB" />
                                        </svg>
                                    </div>
                                    <div class="swiper-btn swiper-button-next">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="17" viewBox="0 0 24 17" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.2864 5.81948C15.8987 4.23496 15.0809 2.21457 14.8519 0.406511L16.7085 0.17749C16.8952 1.65196 17.5742 3.32441 18.702 4.6121C19.8111 5.87854 21.3254 6.74694 23.2391 6.75442C23.2475 6.75439 23.2558 6.75438 23.2642 6.75438V8.6008L23.2497 8.60088H23.2352C21.3826 8.61101 19.8773 9.59221 18.7459 11.0994C17.6005 12.6252 16.9031 14.618 16.7104 16.3926L14.8501 16.1959C15.0732 14.1408 15.9164 11.5067 17.2864 9.68165C17.666 9.17603 17.7608 9.02134 18.2368 8.60089L0.8125 8.60088L0.8125 6.75446L18.2368 6.75446C17.8941 6.46537 17.5771 6.15144 17.2864 5.81948Z" fill="#181823" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            @if ($latestTwoPosts->isNotEmpty())
                                <div class="right-side-banner">
                                    @foreach($latestTwoPosts as $post)
                                        <div class="echo-hero-baner small">
                                            <div class="echo-hero-banner-main-img img-transition-scale position-relative">
                                                <a href="{{ $post->url }}">
                                                    {{ RvMedia::image($post->image, $post->name, attributes: ['class' => 'banner-image-one img-hover']) }}
                                                </a>

                                                {!! Theme::partial('blog.post.partials.action-post', compact('post')) !!}
                                            </div>
                                            <div class="content">
                                                {!! Theme::partial('category-badge', compact('post')) !!}
                                                <div class="echo-hero-title font-weight-bold">
                                                    <a href="{{ $post->url }}" title="{{ $post->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $post->name }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if ($sidebar)
                <div class="col-xl-4 col-lg-5 col-md-12">
                    {!! dynamic_sidebar($sidebar) !!}
                </div>
            @endif
        </div>
    </div>
</div>
