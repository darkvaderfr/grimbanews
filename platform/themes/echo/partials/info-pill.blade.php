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

<details class="{{ $classes }}" @if($id) id="{{ $id }}" @endif>
    <summary aria-label="{{ $label ? __('Plus d’infos sur :label', ['label' => strip_tags($label)]) : __('Plus d’infos') }}">
        <span class="grimba-info-pill__btn" aria-hidden="true">i</span>
        @if($label)
            <span class="grimba-info-pill__label">{{ $label }}</span>
        @endif
    </summary>
    <div class="grimba-info-pill__body">
        @if($bodyIsHtml)
            {!! $body !!}
        @else
            {{ $body }}
        @endif
    </div>
</details>
