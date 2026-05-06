<script>
    window.GrimbaFocus = window.GrimbaFocus || (function () {
        const FOCUSABLE_SELECTOR = [
            'a[href]',
            'button:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            'summary',
            '[tabindex]:not([tabindex="-1"])'
        ].join(', ');

        function isVisible(element) {
            if (!(element instanceof HTMLElement)) return false;
            if (element.hidden || element.getAttribute('aria-hidden') === 'true') return false;

            const style = window.getComputedStyle(element);
            return style.visibility !== 'hidden'
                && style.display !== 'none'
                && element.getClientRects().length > 0;
        }

        function focusables(root) {
            return Array.from(root.querySelectorAll(FOCUSABLE_SELECTOR)).filter(isVisible);
        }

        function resolveTarget(root, target) {
            if (target instanceof HTMLElement) return target;
            if (typeof target === 'string') return root.querySelector(target);
            if (typeof target === 'function') return target(root);

            return null;
        }

        function trap(root, options) {
            options = options || {};

            let active = false;
            let lastFocus = null;

            function focusInitial() {
                const nodes = focusables(root);
                const target = resolveTarget(root, options.initialFocus) || nodes[0] || root;

                if (!target.hasAttribute('tabindex') && target === root) {
                    target.setAttribute('tabindex', '-1');
                }

                target.focus({ preventScroll: true });
            }

            function onKeydown(event) {
                if (!active) return;

                if (event.key === 'Escape' && typeof options.onEscape === 'function') {
                    event.preventDefault();
                    options.onEscape(event);
                    return;
                }

                if (event.key !== 'Tab') return;

                const nodes = focusables(root);
                if (!nodes.length) {
                    event.preventDefault();
                    root.focus({ preventScroll: true });
                    return;
                }

                const first = nodes[0];
                const last = nodes[nodes.length - 1];
                const current = document.activeElement;

                if (!root.contains(current)) {
                    event.preventDefault();
                    (event.shiftKey ? last : first).focus({ preventScroll: true });
                    return;
                }

                if (event.shiftKey && current === first) {
                    event.preventDefault();
                    last.focus({ preventScroll: true });
                } else if (!event.shiftKey && current === last) {
                    event.preventDefault();
                    first.focus({ preventScroll: true });
                }
            }

            return {
                activate(trigger) {
                    lastFocus = trigger instanceof HTMLElement ? trigger : document.activeElement;
                    active = true;
                    root.dataset.grimbaFocusTrap = 'active';
                    document.addEventListener('keydown', onKeydown, true);
                    window.requestAnimationFrame(focusInitial);
                },
                deactivate(shouldRestoreFocus) {
                    active = false;
                    delete root.dataset.grimbaFocusTrap;
                    document.removeEventListener('keydown', onKeydown, true);

                    if (shouldRestoreFocus === false) return;
                    if (lastFocus && document.contains(lastFocus) && typeof lastFocus.focus === 'function') {
                        lastFocus.focus({ preventScroll: true });
                    }
                },
                focusables() {
                    return focusables(root);
                },
                isActive() {
                    return active;
                }
            };
        }

        return { trap, focusables };
    })();
</script>
