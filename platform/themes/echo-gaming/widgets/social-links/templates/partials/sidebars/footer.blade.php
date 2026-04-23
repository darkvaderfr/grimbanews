@if ($items)
    <ul class="social-area">
        @include(Theme::getThemeNamespace('widgets.social-links.templates.partials.social-items'), ['wrapperClass' => 'social-icon'])
    </ul>
@endif
