<div class="footer-top-inner">
    @if ($title = Arr::get($config, 'title'))
        <div class="footer-top-left">
            <h3 class="content">{!! BaseHelper::clean($title) !!}</h3>
        </div>
    @endif

    <div class="footer-top-right">
        <div class="echo-subscribe-box-button">
            {!! $form->renderForm() !!}
        </div>
    </div>
</div>
