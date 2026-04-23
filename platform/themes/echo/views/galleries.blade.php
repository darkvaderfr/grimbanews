@if (is_plugin_active('gallery') && $galleries->isNotEmpty())
    @php
        Theme::set('pageTitle', __('Galleries'));
        Theme::layout('full-width');
    @endphp

    <div class="galleries-page echo-photography-area area3">
        <div class="container">
            <div class="section-inner">
                <div class="row g-4">
                    @foreach($galleries as $gallery)
                        <div class="col-lg-4 col-md-6 col-12 gallery-item">
                            <div class="echo-hero-banner-main-img img-transition-scale">
                                <a href="{{ $gallery->url }}">
                                    {{ RvMedia::image($gallery->image, $gallery->name, 'extra-large', attributes: ['class' => 'banner-image-one img-hover w-100']) }}
                                </a>
                            </div>
                            <div class="content">
                                <div class="echo-hero-title font-weight-bold">
                                    <a href="{{ $gallery->url }}" title="{{ $gallery->name }}" class="title-hover truncate-custom truncate-2-custom">{{ $gallery->name }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
