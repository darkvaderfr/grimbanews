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

<details class="{{ $classes }}" @if($id) id="{{ $id }}" @endif data-grimba-info-pill>
    <summary aria-label="{{ $label ? __('Plus d’infos sur :label', ['label' => strip_tags($label)]) : __('Plus d’infos') }}">
        <span class="grimba-info-pill__btn" aria-hidden="true">i</span>
        @if($label)
            <span class="grimba-info-pill__label">{{ $label }}</span>
        @endif
    </summary>
    <div class="grimba-info-pill__body" data-grimba-info-pill-body>
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
         the `toggle` event (graceful degradation). --}}
    <script>
        (function () {
            if (window.__grimbaInfoPillReady) return;
            window.__grimbaInfoPillReady = true;

            const VIEWPORT_PAD = 12;
            const CHEVRON_GAP = 6;
            const MOBILE_BP = 600;

            const positionBody = (details) => {
                const body = details.querySelector('[data-grimba-info-pill-body]');
                if (!body) return;
                const isMobile = window.innerWidth <= MOBILE_BP;
                if (isMobile) {
                    // Mobile already uses position:fixed bottom-sheet via CSS — reset inline styles.
                    body.style.position = '';
                    body.style.top = '';
                    body.style.left = '';
                    body.style.right = '';
                    body.style.bottom = '';
                    body.style.maxHeight = '';
                    return;
                }
                const summary = details.querySelector('summary');
                if (!summary) return;
                const sRect = summary.getBoundingClientRect();
                const bodyW = Math.min(380, window.innerWidth - VIEWPORT_PAD * 2);
                // Position relative to viewport so we escape any
                // overflow:hidden ancestor.
                body.style.position = 'fixed';
                body.style.right = 'auto';
                body.style.bottom = 'auto';
                body.style.width = bodyW + 'px';
                body.style.maxWidth = bodyW + 'px';
                // Anchor below the summary, clamp left/right to viewport.
                let left = sRect.left;
                if (left + bodyW + VIEWPORT_PAD > window.innerWidth) {
                    left = Math.max(VIEWPORT_PAD, window.innerWidth - bodyW - VIEWPORT_PAD);
                }
                if (left < VIEWPORT_PAD) left = VIEWPORT_PAD;
                let top = sRect.bottom + CHEVRON_GAP;
                // If the popover would run off the bottom, flip above the summary.
                const bodyH = body.offsetHeight || 200;
                if (top + bodyH + VIEWPORT_PAD > window.innerHeight) {
                    const flipped = sRect.top - CHEVRON_GAP - bodyH;
                    if (flipped >= VIEWPORT_PAD) {
                        top = flipped;
                    } else {
                        // Constrained — pin to viewport bottom and let it scroll internally.
                        top = Math.max(VIEWPORT_PAD, window.innerHeight - bodyH - VIEWPORT_PAD);
                        body.style.maxHeight = (window.innerHeight - VIEWPORT_PAD * 2) + 'px';
                        body.style.overflowY = 'auto';
                    }
                }
                body.style.top = top + 'px';
                body.style.left = left + 'px';
            };

            const closeOthers = (except) => {
                document.querySelectorAll('[data-grimba-info-pill][open]').forEach((d) => {
                    if (d !== except) d.removeAttribute('open');
                });
            };

            document.addEventListener('toggle', (e) => {
                const t = e.target;
                if (!(t instanceof Element) || !t.matches('[data-grimba-info-pill]')) return;
                if (t.open) {
                    closeOthers(t);
                    positionBody(t);
                }
            }, true);

            // Click outside / Escape closes.
            document.addEventListener('click', (e) => {
                document.querySelectorAll('[data-grimba-info-pill][open]').forEach((d) => {
                    if (!d.contains(e.target)) d.removeAttribute('open');
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
@endonce
