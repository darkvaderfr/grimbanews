{{-- S98 — "Traduit par NobuAI" chip. Rendered on reader surfaces
     where translated content is currently being shown, so the brand
     appears next to the action. Never names the underlying provider.
     Size param: 'sm' (default, card inline) or 'md' (single post hero). --}}
@php
    $__size = $size ?? 'sm';
    $__label = $label ?? __('Traduit par NobuAI');
    $__inline = $__size === 'sm';
    $__padY = $__inline ? 2 : 4;
    $__padX = $__inline ? 8 : 10;
    $__font = $__inline ? 11 : 12.5;
@endphp
<span class="gn-nobuai-chip"
      style="display:inline-flex; align-items:center; gap:6px;
             padding:{{ $__padY }}px {{ $__padX }}px;
             border-radius:999px;
             background:rgba(26,23,19,0.06);
             border:1px solid rgba(26,23,19,0.12);
             color:var(--gn-ink,#1a1713);
             font-family:'Public Sans',system-ui,sans-serif;
             font-size:{{ $__font }}px; font-weight:600; letter-spacing:0.3px;
             line-height:1;"
      aria-label="{{ $__label }}">
    <span aria-hidden="true"
          style="display:inline-block; width:6px; height:6px; border-radius:50%;
                 background:linear-gradient(135deg,#6b7280,#1a1713);"></span>
    {{ $__label }}
</span>
