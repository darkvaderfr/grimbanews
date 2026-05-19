@php
    /**
     * 7-tier bias chip (Ground-fidelity).
     *
     * Use this for source-detail rows + the full-coverage list under a
     * story. Use the simpler bias-badge.blade.php for the 3-tier
     * compressed display on cards.
     *
     * @var string $tier   Required. Slug from App\Ground\Bias::tier()
     * @var string $size   Optional. sm | md | lg (default md)
     * @var bool   $showLabel Optional. Default true
     * @var ?string $href  Optional. Wraps chip in an anchor when set.
     */
    use App\Ground\Bias;

    $tier      = $tier ?? 'unknown';
    $size      = $size ?? 'md';
    $showLabel = $showLabel ?? true;
    $href      = $href ?? null;

    $color = Bias::color($tier);
    $label = Bias::label($tier);
    $short = Bias::shortLabel($tier);

    $sizeStyle = match ($size) {
        'sm' => 'padding: 2px 8px; font-size: 11px; line-height: 1.4;',
        'lg' => 'padding: 6px 14px; font-size: 14px; line-height: 1.4;',
        default => 'padding: 4px 10px; font-size: 12px; line-height: 1.4;',
    };

    $tag = $href ? 'a' : 'span';
@endphp

@once
    <style>
        /* Wave ZZZZ (Vader 2026-05-18) — boost bias-chip visibility in
           dark mode. The inline `style` block on each chip uses
           `{color}1f` (12% opacity) for the chip background — that
           reads fine on the cream canvas but is barely perceptible on
           the dark canvas. Layer a stronger backdrop ring + slightly
           lift the text saturation so the chip still reads at a glance
           in dark mode. The bias-color text + dot stay the same so
           the chip's identity (left/center/right) remains obvious. */
        [data-bs-theme="dark"] .grimba-bias-chip,
        body[data-theme="dark"] .grimba-bias-chip {
            backdrop-filter: blur(1px);
            -webkit-backdrop-filter: blur(1px);
            box-shadow:
                inset 0 0 0 1px rgba(255, 250, 240, .08),
                0 2px 8px rgba(0, 0, 0, .28);
        }
    </style>
@endonce

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    class="grimba-bias-chip grimba-bias-chip--{{ str_replace('_', '-', $tier) }} grimba-bias-chip--{{ $size }}"
    title="{{ __('Biais éditorial') }} : {{ $label }}"
    aria-label="{{ __('Biais') }} : {{ $label }}"
    style="
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 9999px;
        background: {{ $color }}1f;
        color: {{ $color }};
        border: 1px solid {{ $color }}66;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: none;
        text-decoration: none;
        white-space: nowrap;
        {{ $sizeStyle }}
    "
    data-bias-tier="{{ $tier }}"
>
    <span aria-hidden="true" style="
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: {{ $color }};
        flex: 0 0 8px;
        box-shadow: 0 0 0 1px {{ $color }}33;
    "></span>
    @if($showLabel)
        <span class="grimba-bias-chip__label">{{ $size === 'sm' ? $short : $label }}</span>
    @endif
</{{ $tag }}>
