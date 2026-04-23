<div class="echo-right-ct-1">
    <div class="echo-feature-area-right-site-newsletter">
        @if ($image = Arr::get($config, 'image'))
            <div class="echo-feature-area-right-site-newsletter-img">
                {{ RvMedia::image($image, Arr::get($config, 'title')) }}
            </div>
        @endif

        @if ($title = Arr::get($config, 'title'))
            <div class="echo-feature-area-right-site-sub-title">
                <p>{!! BaseHelper::clean($title) !!}</p>
            </div>
        @endif
        <div class="echo-feature-area-right-site-news-subscribe">
            {!! $form->renderForm() !!}
        </div>
    </div>
</div>
