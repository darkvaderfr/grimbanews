@if (is_plugin_active('newsletter') && $enableNewsletterButton = Arr::get($config, 'enable_newsletter_button', true) && ($buttonLabel = Arr::get($config, 'form_newsletter_button_label')))
    <div class="echo-header-top-subs-social-menu btn-newsletter">
        <div class="echo-header-top-subs-social">
            <div class="echo-header-top-subscribe-btn">
                <button type="button" class="echo-py-btn" data-bs-toggle="modal" data-bs-target="#newsletter-popup">
                    <i class="fa-regular fa-envelope"></i> {!! BaseHelper::clean($buttonLabel) !!}
                </button>
            </div>
        </div>
    </div>
@endif
