@php
    /**
     * Grimba shells use their own newsletter UX. The stock Botble
     * newsletter popup auto-opens after a delay and creates a Bootstrap
     * backdrop over the reader UI, which looks like a broken black page.
     */
    Theme::asset()->container('footer')->remove('newsletter');

    $bodyHooks = (string) apply_filters(THEME_FRONT_BODY, null);
    $bodyHooks = preg_replace(
        '/<div\b[^>]*\bid=["\']newsletter-popup["\'][\s\S]*?<\/div>\s*/i',
        '',
        $bodyHooks
    ) ?? $bodyHooks;
    $bodyHooks = preg_replace(
        '/<div\b[^>]*\bclass=["\'][^"\']*\bmodal-backdrop\b[^"\']*["\'][^>]*><\/div>\s*/i',
        '',
        $bodyHooks
    ) ?? $bodyHooks;
@endphp

{!! $bodyHooks !!}

<script>
    (function () {
        const cleanupStockBackdrop = () => {
            document.querySelectorAll('#newsletter-popup, .newsletter-popup').forEach(node => node.remove());
            document.querySelectorAll('.modal-backdrop').forEach(node => node.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        };

        cleanupStockBackdrop();
        window.addEventListener('load', cleanupStockBackdrop, { once: true });

        new MutationObserver(mutations => {
            for (const mutation of mutations) {
                for (const node of mutation.addedNodes) {
                    if (!(node instanceof Element)) continue;
                    if (node.matches('#newsletter-popup, .newsletter-popup, .modal-backdrop')
                        || node.querySelector?.('#newsletter-popup, .newsletter-popup, .modal-backdrop')) {
                        cleanupStockBackdrop();
                        return;
                    }
                }
            }
        }).observe(document.documentElement, { childList: true, subtree: true });
    })();
</script>
