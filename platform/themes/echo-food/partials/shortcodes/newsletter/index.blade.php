@if (is_plugin_active('newsletter'))
    <section class="echo-newsletter-area home-nine" @style($variableStyles)>
        <div class="hm-9-container">
            <div class="newsletter-inner">
                @if ($title = $shortcode->title)
                    <h3 class="content-title text-center">{!! BaseHelper::clean($title) !!}</h3>
                @endif

                @if ($subtitle = $shortcode->subtitle)
                    <p class="desc text-center">{!! BaseHelper::clean($subtitle) !!}</p>
                @endif

                <div class="echo-subscribe-box-button">
                    {!! $form->renderForm() !!}
                </div>
            </div>
        </div>
    </section>
@endif
