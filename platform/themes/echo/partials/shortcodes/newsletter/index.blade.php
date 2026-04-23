@if (is_plugin_active('newsletter'))
    <section class="echo-newsletter-area home-six" @style($variableStyles)>
        <div class="hm-6-container">
            <div class="newsletter-inner">
                @if ($title = $shortcode->title)
                    <h3 class="content-title text-center">{!! BaseHelper::clean($title) !!}</h3>
                @endif
                {!! $form->renderForm() !!}
            </div>
        </div>
    </section>
@endif
