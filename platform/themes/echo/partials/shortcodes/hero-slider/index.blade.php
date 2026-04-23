@if (count($tabs) > 0)
    @php
        Theme::set('header-transparent', true);
        $textColor = $shortcode->text_color ?: '#ffffff';
    @endphp
    <section class="hero-home-six" @style(["--text-color: $textColor" => $textColor])>
        <div class="hero-inner">
            <div class="swiper rts-heroSlider">
                <div class="swiper-wrapper">
                    @foreach($tabs as $tab)
                        @php($media = Arr::get($tab, 'media'))

                        @continue(! $media || ! echo_check_extension_video($media))

                        <div class="swiper-slide">
                            <div class="slider-inner">
                                <video autoplay loop muted>
                                    <source src="{{ RvMedia::url($media) }}" />
                                </video>

                                <div class="content">
                                    @if ($title = Arr::get($tab, 'title'))
                                        <div class="hero-hm6-title">
                                            <h1>{!! BaseHelper::clean($title) !!}</h1>
                                        </div>
                                    @endif

                                    @if (($buttonUrl = Arr::get($tab, 'button_url')) && ($buttonLabel = Arr::get($tab, 'button_label')))
                                    <div class="hero-hm6-btn">
                                        <a href="{{ $buttonUrl }}" class="hm6-btn-1">{!! BaseHelper::clean($buttonLabel) !!}
                                            <svg width="20" height="15" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path id="Union" fill-rule="evenodd" clip-rule="evenodd" d="M14.4195 5.03445C13.2415 3.68932 12.5473 1.97418 12.3529 0.439292L13.9289 0.244873C14.0875 1.49657 14.6639 2.91634 15.6212 4.00949C16.5628 5.08458 17.8483 5.82178 19.4729 5.82814C19.48 5.82811 19.4871 5.8281 19.4941 5.8281V7.39556L19.4819 7.39563H19.4695C17.8969 7.40423 16.619 8.23718 15.6586 9.51662C14.6862 10.812 14.0941 12.5037 13.9306 14.0101L12.3513 13.8431C12.5407 12.0986 13.2565 9.8624 14.4195 8.3131C14.7417 7.88388 14.8223 7.75256 15.2264 7.39564L0.43457 7.39563L0.43457 5.82817L15.2264 5.82817C14.9354 5.58276 14.6663 5.31626 14.4195 5.03445Z" fill="#2B2004"/>
                                            </svg>
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination">
                    <span class="swiper-pagination-current">01</span> / <span class="swiper-pagination-total">{{ count($tabs) }}</span>
                </div>
                <div class="swiper-btn-area">
                    <div class="swiper-btn swiper-button-prev">
                        <svg width="23" height="17" viewBox="0 0 23 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.97921 5.95864C7.3669 4.37412 8.18468 2.35373 8.41369 0.545671L6.55717 0.31665C6.37041 1.79112 5.69141 3.46357 4.56367 4.75126C3.45456 6.0177 1.94021 6.8861 0.0265007 6.89358C0.0181618 6.89355 0.00982285 6.89354 0.00147247 6.89354V8.73996L0.0158901 8.74004H0.0304642C1.88302 8.75017 3.38831 9.73137 4.51968 11.2385C5.66512 12.7644 6.36253 14.7572 6.55519 16.5317L8.41552 16.335C8.1924 14.28 7.3492 11.6458 5.97921 9.82081C5.59967 9.31519 5.50482 9.1605 5.0288 8.74005L22.4531 8.74004V6.89362L5.0288 6.89362C5.37152 6.60453 5.68849 6.2906 5.97921 5.95864Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="swiper-btn swiper-button-next">
                        <svg width="23" height="17" viewBox="0 0 23 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.97921 5.95864C7.3669 4.37412 8.18468 2.35373 8.41369 0.545671L6.55717 0.31665C6.37041 1.79112 5.69141 3.46357 4.56367 4.75126C3.45456 6.0177 1.94021 6.8861 0.0265007 6.89358C0.0181618 6.89355 0.00982285 6.89354 0.00147247 6.89354V8.73996L0.0158901 8.74004H0.0304642C1.88302 8.75017 3.38831 9.73137 4.51968 11.2385C5.66512 12.7644 6.36253 14.7572 6.55519 16.5317L8.41552 16.335C8.1924 14.28 7.3492 11.6458 5.97921 9.82081C5.59967 9.31519 5.50482 9.1605 5.0288 8.74005L22.4531 8.74004V6.89362L5.0288 6.89362C5.37152 6.60453 5.68849 6.2906 5.97921 5.95864Z" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
                <div class="border"></div>
            </div>
            <div class="pagination-area">
                <div class="position-relative">
                    <div class="banner-blog-thumbs">
                        <div class="swiper SlideThumb">
                            <div class="swiper-wrapper">
                                @foreach($tabs as $tab)
                                    @continue(! $thumb = Arr::get($tab, 'thumb'))
                                    <div class="swiper-slide">
                                        <div class="banner-single-thumb">
                                            <div class="thumbnail">
                                                {{ Rvmedia::image($thumb, Arr::get($tab, 'title')) }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
