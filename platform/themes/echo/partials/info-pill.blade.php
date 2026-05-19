@php
    /*
     * Vader 2026-05-16 Sprint 28 — reusable info pill.
     *
     * A circular "i" button that expands inline to reveal hidden
     * contextual copy. Intent: keep the home / list surfaces lean,
     * and let curious readers pop open the methodology / definition
     * tucked inside.
     *
     * Vars:
     *   $label   (string|null)  visible label next to the "i" pill
     *                            — pass null when you want the pill alone.
     *   $body    (string|html)  body slot — accepts plain text or markup.
     *   $align   (string|null)  'right' floats the pill to the line end.
     *   $size    (string|null)  'sm' renders a 18px pill, default = 22px.
     *   $tone    (string|null)  'soft' uses a muted ink tone (default = red).
     *   $id      (string|null)  optional id for the <details> element.
     *
     * Built on top of <details>+<summary> so Sprint 27's chevron
     * rotation + reduced-motion handling apply for free. The chevron
     * is suppressed here — the pill itself doubles as the affordance.
     */
    $label = $label ?? null;
    $body  = $body  ?? '';
    $align = $align ?? null;
    $size  = $size  ?? null;
    $tone  = $tone  ?? null;
    $id    = $id    ?? null;
    // Vader 2026-05-16 (Zen audit) — escape by default. Callers that
    // need to render trusted HTML (e.g. inline <strong>, <em>) must
    // pass 'html' => true explicitly so future user-derived content
    // can't accidentally bypass the escape.
    $bodyIsHtml = ($html ?? false) === true;
    $classes = 'grimba-info-pill';
    if ($align === 'right') $classes .= ' grimba-info-pill--right';
    if ($size === 'sm')     $classes .= ' grimba-info-pill--sm';
    if ($tone === 'soft')   $classes .= ' grimba-info-pill--soft';
@endphp

@php
    // S-PILL-07 a11y: every pill body needs an id the summary can
    // reference via aria-controls. Reuse the user-supplied $id when
    // present; otherwise generate a stable per-render id.
    $bodyId = ($id ? $id . '__body' : 'grimba-info-pill-body-' . substr(md5(uniqid('', true)), 0, 8));
@endphp
<details class="{{ $classes }}" @if($id) id="{{ $id }}" @endif data-grimba-info-pill>
    <summary
        aria-label="{{ $label ? __('Plus d’infos sur :label', ['label' => strip_tags($label)]) : __('Plus d’infos') }}"
        aria-expanded="false"
        aria-controls="{{ $bodyId }}">
        <span class="grimba-info-pill__btn" aria-hidden="true">i</span>
        @if($label)
            <span class="grimba-info-pill__label">{{ $label }}</span>
        @endif
    </summary>
    <div class="grimba-info-pill__body"
         data-grimba-info-pill-body
         id="{{ $bodyId }}"
         role="region"
         aria-label="{{ $label ? __('Détails — :label', ['label' => strip_tags($label)]) : __('Détails') }}"
         tabindex="-1">
        @if($bodyIsHtml)
            {!! $body !!}
        @else
            {{ $body }}
        @endif
    </div>
</details>

@once
    {{-- Vader 2026-05-18 — pill body must escape overflow:hidden
         ancestors so it never clips against a card/glass-panel edge
         AND never shifts surrounding content. Strategy: on toggle,
         switch the body to `position: fixed` with viewport-anchored
         coordinates measured from the pill button's rect. CSS handles
         the default `absolute` fallback for browsers that don't fire
         the `toggle` event (graceful degradation).

         Polish 2026-05-18: smoother close animation, caret pointer
         that flips when the popover renders above the summary, and
         a small dimmer backdrop on mobile so the bottom-sheet feels
         tactile. --}}
    <script>
        (function () {
            if (window.__grimbaInfoPillReady) return;
            window.__grimbaInfoPillReady = true;

            const VIEWPORT_PAD = 12;
            const CHEVRON_GAP = 10;
            const MOBILE_BP = 600;
            const CLOSE_ANIM_MS = 180;

            // Ensure a single shared backdrop exists for mobile.
            const getBackdrop = () => {
                let bd = document.getElementById('grimba-info-pill-backdrop');
                if (!bd) {
                    bd = document.createElement('div');
                    bd.id = 'grimba-info-pill-backdrop';
                    bd.setAttribute('aria-hidden', 'true');
                    document.body.appendChild(bd);
                    bd.addEventListener('click', () => closeOthers(null));
                }
                return bd;
            };

            const showBackdrop = (show) => {
                const bd = getBackdrop();
                bd.dataset.active = show ? '1' : '0';
            };

            const positionBody = (details) => {
                const body = details.querySelector('[data-grimba-info-pill-body]');
                if (!body) return;
                const isMobile = window.innerWidth <= MOBILE_BP;
                if (isMobile) {
                    // Mobile uses the CSS bottom-sheet pattern; reset
                    // any inline coordinates from a previous desktop
                    // toggle and turn the backdrop on.
                    body.style.position = '';
                    body.style.top = '';
                    body.style.left = '';
                    body.style.right = '';
                    body.style.bottom = '';
                    body.style.maxHeight = '';
                    body.removeAttribute('data-pill-flipped');
                    showBackdrop(true);
                    return;
                }
                showBackdrop(false);
                const summary = details.querySelector('summary');
                if (!summary) return;
                const sRect = summary.getBoundingClientRect();
                const bodyW = Math.min(380, window.innerWidth - VIEWPORT_PAD * 2);
                body.style.position = 'fixed';
                body.style.right = 'auto';
                body.style.bottom = 'auto';
                body.style.width = bodyW + 'px';
                body.style.maxWidth = bodyW + 'px';
                let left = sRect.left;
                if (left + bodyW + VIEWPORT_PAD > window.innerWidth) {
                    left = Math.max(VIEWPORT_PAD, window.innerWidth - bodyW - VIEWPORT_PAD);
                }
                if (left < VIEWPORT_PAD) left = VIEWPORT_PAD;
                let top = sRect.bottom + CHEVRON_GAP;
                const bodyH = body.offsetHeight || 200;
                let flippedUp = false;
                if (top + bodyH + VIEWPORT_PAD > window.innerHeight) {
                    const flipped = sRect.top - CHEVRON_GAP - bodyH;
                    if (flipped >= VIEWPORT_PAD) {
                        top = flipped;
                        flippedUp = true;
                    } else {
                        // Constrained — pin to viewport, scroll internally.
                        top = Math.max(VIEWPORT_PAD, window.innerHeight - bodyH - VIEWPORT_PAD);
                        body.style.maxHeight = (window.innerHeight - VIEWPORT_PAD * 2) + 'px';
                        body.style.overflowY = 'auto';
                    }
                }
                body.style.top = top + 'px';
                body.style.left = left + 'px';
                if (flippedUp) {
                    body.setAttribute('data-pill-flipped', 'up');
                } else {
                    body.removeAttribute('data-pill-flipped');
                }
            };

            // Smooth close — fade the body out, then drop the open attr.
            const closeWithAnim = (details) => {
                const body = details.querySelector('[data-grimba-info-pill-body]');
                const summary = details.querySelector('summary');
                if (!body) {
                    details.removeAttribute('open');
                    if (summary) summary.setAttribute('aria-expanded', 'false');
                    return;
                }
                // Respect reduced motion — drop immediately.
                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    details.removeAttribute('open');
                    if (summary) summary.setAttribute('aria-expanded', 'false');
                    showBackdrop(false);
                    return;
                }
                body.style.opacity = '0';
                body.style.transform = (body.getAttribute('data-pill-flipped') === 'up')
                    ? 'translateY(6px) scale(.98)'
                    : 'translateY(-6px) scale(.98)';
                setTimeout(() => {
                    details.removeAttribute('open');
                    if (summary) summary.setAttribute('aria-expanded', 'false');
                    body.style.opacity = '';
                    body.style.transform = '';
                    // Backdrop is per-page, not per-pill — only hide
                    // when no pills remain open.
                    if (!document.querySelector('[data-grimba-info-pill][open]')) {
                        showBackdrop(false);
                    }
                }, CLOSE_ANIM_MS);
            };

            const closeOthers = (except) => {
                document.querySelectorAll('[data-grimba-info-pill][open]').forEach((d) => {
                    if (d !== except) closeWithAnim(d);
                });
            };

            // S-PILL-07 a11y helper: reflect open-state on the
            // summary's aria-expanded so screen readers announce the
            // pill correctly when toggled.
            const syncAriaExpanded = (details) => {
                const summary = details.querySelector('summary');
                if (summary) {
                    summary.setAttribute('aria-expanded', details.open ? 'true' : 'false');
                }
            };

            // Wave TTTT (Vader 2026-05-18) — explicit OPEN animation.
            // Without this, the body just snaps into view at whatever
            // position CSS chose, then JS shifts it via positionBody.
            // That caused a "pill not working" feel — visually it
            // looked like an unstyled flash. Now we (1) hide the body
            // before positioning, (2) position it, then (3) fade +
            // slide it into view with a single rAF so paint and
            // animation start at the same time.
            const openWithAnim = (details) => {
                const body = details.querySelector('[data-grimba-info-pill-body]');
                if (!body) {
                    positionBody(details);
                    return;
                }
                const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (reduced) {
                    positionBody(details);
                    body.style.opacity = '';
                    body.style.transform = '';
                    return;
                }
                // Step 1: hide before measurement so the user never
                // sees the unpositioned flash.
                body.style.opacity = '0';
                body.style.transform = 'translateY(-6px) scale(.98)';
                body.style.transition = 'none';
                positionBody(details);
                // Step 2: in the NEXT frame, swap to the open easing
                // and let the browser animate to the rest state.
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        const flipped = body.getAttribute('data-pill-flipped') === 'up';
                        body.style.transition = 'opacity .22s cubic-bezier(.2,.85,.3,1), transform .22s cubic-bezier(.2,.85,.3,1)';
                        body.style.opacity = '1';
                        body.style.transform = flipped
                            ? 'translateY(0) scale(1)'
                            : 'translateY(0) scale(1)';
                    });
                });
            };

            document.addEventListener('toggle', (e) => {
                const t = e.target;
                if (!(t instanceof Element) || !t.matches('[data-grimba-info-pill]')) return;
                syncAriaExpanded(t);
                if (t.open) {
                    closeOthers(t);
                    openWithAnim(t);
                    // Move focus to the body so the SR reads the
                    // newly-revealed content. tabindex="-1" makes
                    // the div programmatically focusable without
                    // adding it to the tab order.
                    const body = t.querySelector('[data-grimba-info-pill-body]');
                    if (body) {
                        // Defer so the layout settles before focus.
                        setTimeout(() => {
                            try { body.focus({ preventScroll: true }); } catch (_) { /* noop */ }
                        }, 30);
                    }
                } else if (!document.querySelector('[data-grimba-info-pill][open]')) {
                    showBackdrop(false);
                }
            }, true);

            // Click outside / Escape closes — but ignore clicks on a
            // pill's own summary so the second-click-to-close native
            // <details> behavior still fires.
            document.addEventListener('click', (e) => {
                document.querySelectorAll('[data-grimba-info-pill][open]').forEach((d) => {
                    if (!d.contains(e.target)) closeWithAnim(d);
                });
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeOthers(null);
            });

            // Reposition on scroll / resize so the popover follows the pill button.
            const reflow = () => {
                document.querySelectorAll('[data-grimba-info-pill][open]').forEach(positionBody);
            };
            window.addEventListener('scroll', reflow, { passive: true });
            window.addEventListener('resize', reflow);
        })();
    </script>
    <style>
        /* Mobile backdrop — sits between the page and the bottom-sheet
           pill body. Pure CSS-driven via the [data-active] attribute. */
        #grimba-info-pill-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(20, 17, 13, 0.34);
            z-index: 9998;
            opacity: 0;
            pointer-events: none;
            transition: opacity .18s ease-out;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
        }
        #grimba-info-pill-backdrop[data-active="1"] {
            opacity: 1;
            pointer-events: auto;
        }
        @media (min-width: 601px) {
            /* Desktop never shows the backdrop. */
            #grimba-info-pill-backdrop { display: none; }
        }
    </style>
@endonce
