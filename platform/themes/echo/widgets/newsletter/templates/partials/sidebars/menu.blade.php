<div class="newsletter-form">
    <div class="form-inner">
        <div class="content">
            @if ($image = Arr::get($config, 'image'))
                <div class="newsletter-image">
                    {{ RvMedia::image($image, Arr::get($config, 'name')) }}
                </div>
            @endif

            @if ($title = Arr::get($config, 'title'))
                <h3 class="title">{!! BaseHelper::clean($title) !!}</h3>
            @endif

            @if ($description = Arr::get($config, 'description'))
                <p class="desc">{!! BaseHelper::clean($description) !!}</p>
            @endif
        </div>

        {!! $form->renderForm() !!}
    </div>
</div>
