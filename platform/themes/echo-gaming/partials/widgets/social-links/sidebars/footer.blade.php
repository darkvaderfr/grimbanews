@if ($items)
    <ul class="social-area">
        @include(Theme::getThemeNamespace('widgets.social-links.partials.templates.partials.social-items'))
    </ul>
@endif
