<div class="echo-feature-area">
    <div class="echo-feature-area-right-site-newsletter">
        @if ($title = Arr::get($config, 'title'))
            <div class="echo-feature-area-rigth-site-newsletter-title">
                <p class="h5">{!! BaseHelper::clean($title) !!}</p>
            </div>
        @endif

        @if ($image = Arr::get($config, 'image'))
            <div class="echo-feature-area-right-site-newsletter-img">
                {{ RvMedia::image($image, $title) }}
            </div>
        @endif

        @if ($description = Arr::get($config, 'description'))
            <div class="echo-feature-area-right-site-sub-title">
                <p>{!! BaseHelper::clean($description) !!}</p>
            </div>
        @endif

        <div class="echo-feature-area-right-site-news-subscribe">
            {!! $form->renderForm() !!}
        </div>
    </div>
</div>
