@php
    Theme::set('pageTitle', $gallery->name);
    Theme::set('pageDescription', $gallery->description);
    Theme::layout('full-width');
@endphp

<section class="section gallery-detail-content pt-50 pb-50">
    <div class="container">
        <div class="page-content">
            <article class="post post--single">
                <div class="post__content">

<div class="ck-content">
            {!! BaseHelper::clean($gallery->description) !!}
        </div>                    <div id="list-photo" class="row g-4">
                        @foreach (gallery_meta_data($gallery) as $image)
                            @continue(! $image)

                            <div
                                class="gallery-items col-lg-4 col-md-6 col-12"
                                data-src="{{ RvMedia::getImageUrl($imageUrl = Arr::get($image, 'img')) }}"
                                data-sub-html="{{ $description = BaseHelper::clean(Arr::get($image, 'description')) }}"
                            >
                                <div class="photo-item">
                                    <div class="thumb img-transition-scale">
                                        <a href="{{ $description }}">
                                            {{ RvMedia::image($imageUrl, $description, 'extra-large') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <br>
                    {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, null, $gallery) !!}
                </div>
            </article>
        </div>
    </div>
</section>
