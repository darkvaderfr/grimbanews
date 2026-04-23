<section class="echo-catagories-area" @style($variableStyles)>
    <div class="echo-catagories-area-content">
        <div class="container">
            <div class="title-area">
                @if ($title = $shortcode->title)
                    <h2 @class([
                        'section-title',
                        'text-center' => $shortcode->title_align == 'center',
                        'text-end' => $shortcode->title_align == 'end',
                        'text-start' => $shortcode->title_align == 'start',
                    ])>
                        {!! BaseHelper::clean($title) !!}
                    </h2>
                @endif
                <div class="swiper-btn">
                    <div class="swiper-button-prev"><i class="fa-regular fa-arrow-left"></i></div>
                    <div class="swiper-button-next"><i class="fa-regular fa-arrow-right"></i></div>
                </div>
            </div>
            <div class="echo-catagories-inner">
                <div class="swiper mySwiper2">
                    <div class="swiper-wrapper">
                        @foreach($categories as $category)
                            <div class="swiper-slide">
                                <div class="echo-latest-news-main-content">
                                    <div class="echo-latest-news-img img-transition-scale">
                                        <a href="{{ $category->url }}">
                                            {{ RvMedia::image($category->getMetaData('image', true), $category->name, 'small') }}
                                        </a>
                                        <div class="image-content">
                                            <a class="title truncate-custom truncate-2-custom text-heading title-hover" href="{{ $category->url }}" title="{{ $category->name }}">
                                                {{ $category->name }}
                                            </a>
                                            <p class="desc">
                                                {{ $category->posts_count == 1
                                                    ? __('1 Article')
                                                    : __(':number Articles', ['number' => $category->posts_count])
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
