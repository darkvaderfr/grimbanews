@php
    /**
     * Grimba shells use their own newsletter UX. The stock Botble
     * newsletter popup auto-opens after a delay and creates a Bootstrap
     * backdrop over the reader UI, which looks like a broken black page.
     */
    Theme::asset()->container('footer')->remove('newsletter');

    $bodyHooks = (string) apply_filters(THEME_FRONT_BODY, null);
    $bodyHooks = preg_replace(
        '/<div\s+class="modal fade newsletter-popup"\s+id="newsletter-popup"[\s\S]*?<\/div>\s*/',
        '',
        $bodyHooks
    ) ?? $bodyHooks;
@endphp

{!! $bodyHooks !!}
