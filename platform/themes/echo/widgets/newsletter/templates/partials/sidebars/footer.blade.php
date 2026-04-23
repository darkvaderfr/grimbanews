<div class="echo-footer-content-4 col-lg-3 col-md-6 col-12">
    @if ($title = Arr::get($config, 'title'))
        <div class="echo-get-in-tuch">
            <h3>{!! BaseHelper::clean($title) !!}</h3>
        </div>
    @endif

    @if ($description = Arr::get($config, 'description'))
        <div class="echo-footer-news-text">
            <p>{!! BaseHelper::clean($description) !!}</p>
        </div>
    @endif

    @if (is_plugin_active('newsletter'))
        <div class="echo-subscribe-box-button">
            {!! $form->renderForm() !!}
        </div>
    @endif
</div>
