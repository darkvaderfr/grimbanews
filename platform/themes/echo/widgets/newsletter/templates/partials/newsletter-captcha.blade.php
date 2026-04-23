@if (setting('enable_captcha') && is_plugin_active('captcha'))
    <div class="mb-3">
        {!! Captcha::display() !!}
    </div>
    <i class="fa-regular fa-envelope newsletter-icon-with-captcha"></i>
@else
    <i class="fa-regular fa-envelope newsletter-icon-without-captcha"></i>
@endif
