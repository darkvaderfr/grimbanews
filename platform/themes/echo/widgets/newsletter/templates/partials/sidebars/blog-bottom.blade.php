<div class="echo-feature-area">
    <div class="echo-ps-2-subscribe-box" @if ($bgImage = Arr::get($config, 'image')) style="background-image: url({{ RvMedia::getImageUrl($bgImage) }})" @endif>
        <div class="echo-ps-2-subscribe-all-content">
            @if ($title = Arr::get($config, 'title'))
                <div class="echo-ps-2-subscribe-title">
                    <p class="h5">{!! BaseHelper::clean($title) !!}</p>
                </div>
            @endif
            <hr>

            @if ($description = Arr::get($config, 'description'))
                <div class="echo-ps-2-subscribe-sub-title">
                    <p class="text-center">{!! BaseHelper::clean($description) !!}</p>
                </div>
            @endif
            <div class="echo-subscribe-input-fill">
                {!! $form->renderForm() !!}
            </div>
        </div>
    </div>
</div>
