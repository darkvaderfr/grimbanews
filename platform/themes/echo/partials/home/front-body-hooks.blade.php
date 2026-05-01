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

    /*
     * Hero / grid card images come from external publishers (Le Figaro,
     * BBC, L'Express, Huffington Post, …). Hot-link protection, signed-
     * URL expiry, outbound network blocks, and silently-hanging
     * connections turn those into broken or never-loading <img>s on top
     * of a card with `background:#111` + a heavy dark gradient overlay —
     * net result is a screen of solid black squares. Swap any failed or
     * stalled img tagged with data-grimba-post-id to our own post-aware
     * editorial placeholder so the card always shows something legible
     * instead of a black hole.
     */
    (function () {
        // S320 — was 6000ms, but the user-visible cost of waiting six
        // full seconds on every failed publisher CDN was a screen of
        // empty card frames. 1500ms is enough for a successful image
        // to decode on a normal connection (TCP + TLS + image transfer
        // for ~80 KB hero photos averages 600-900ms in our network),
        // and is short enough that a stalled publisher swaps before
        // the reader has finished reading the headline. Once the
        // backend img-proxy ships (PUBLISHER_IMAGE_PROXY_DIAGNOSIS.md)
        // we can drop this to ~700ms because every image will hit our
        // own origin.
        const STALL_TIMEOUT_MS = 1500;

        const swapToPlaceholder = (img) => {
            if (!img || img.dataset.grimbaFallback === '1') return;
            const id = img.getAttribute('data-grimba-post-id');
            if (!id) return;
            img.dataset.grimbaFallback = '1';
            img.removeAttribute('srcset');
            img.src = '/og/placeholder/' + encodeURIComponent(id) + '.svg';
            img.classList.add('gn-placeholder');
            // Mark the photo container so CSS can dial back the dark
            // gradient overlay (which is sized for vivid news photos and
            // crushes the cream editorial placeholder).
            const card = img.closest('.grimba-hero__media, .grimba-section__hero, .grimba-blind-card, .ratio');
            if (card) card.classList.add('gn-fallback-card');
        };

        const armStallWatch = (img) => {
            if (img.dataset.grimbaWatch === '1') return;
            img.dataset.grimbaWatch = '1';
            // If the image hasn't completed within the timeout, swap it.
            // We check both `complete` and `naturalWidth` — a 0-width
            // complete image is a decode failure on some browsers.
            setTimeout(() => {
                if (img.dataset.grimbaFallback === '1') return;
                if (!img.complete || img.naturalWidth === 0) {
                    swapToPlaceholder(img);
                }
            }, STALL_TIMEOUT_MS);
        };

        // GrimbaNews ships pre-seeded /storage/grimba-seeds/cluster-*.svg
        // banners that are intentionally near-black editorial graphics.
        // Combined with the contrast-styles `rgba(0,0,0,.86)` overlay,
        // those cards render as a void. Treat them like fallback cards
        // (lighter gradient) so the headline + bias chip stay readable.
        const isSeedBanner = (img) => {
            const src = img?.currentSrc || img?.src || '';
            return src.indexOf('/storage/grimba-seeds/') !== -1
                || src.indexOf('/og/placeholder/') !== -1;
        };

        const markSeedBanner = (img) => {
            if (!isSeedBanner(img)) return;
            const card = img.closest('.grimba-hero__media, .grimba-section__hero, .grimba-blind-card, .ratio');
            if (card) card.classList.add('gn-fallback-card');
        };

        const init = () => {
            document.querySelectorAll('img[data-grimba-post-id]').forEach((img) => {
                // Already-failed sync imgs (cached error from previous load).
                if (img.complete && img.naturalWidth === 0) {
                    swapToPlaceholder(img);
                    return;
                }
                // Live-loaded seed banners: lighten gradient now.
                markSeedBanner(img);
                if (img.complete) return;
                img.addEventListener('load', () => markSeedBanner(img), { once: true });
                armStallWatch(img);
            });
        };

        // Catch errors as they fire (capture phase — img error doesn't bubble).
        document.addEventListener('error', (e) => {
            const el = e.target;
            if (el && el.tagName === 'IMG' && el.hasAttribute('data-grimba-post-id')) {
                swapToPlaceholder(el);
            }
        }, true);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, { once: true });
        } else {
            init();
        }
    })();
</script>
