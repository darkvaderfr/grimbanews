@php
    /**
     * 5-tier factuality chip (Ground-fidelity).
     *
     * @var int|null $score   Optional. credibility_score 0-100.
     * @var ?string  $tier    Optional. Pre-resolved slug. If both given, $tier wins.
     * @var string   $size    Optional. sm | md | lg (default md)
     * @var bool     $showLabel Optional. Default true
     */
    use App\Ground\Factuality;

    $tier      = $tier ?? Factuality::tier($score ?? null);
    $size      = $size ?? 'md';
    $showLabel = $showLabel ?? true;

    $color = Factuality::color($tier);
    $label = Factuality::label($tier);
    $glyph = Factuality::glyph($tier);

    $sizeStyle = match ($size) {
        'sm' => 'padding: 2px 8px; font-size: 11px; line-height: 1.4;',
        'lg' => 'padding: 6px 14px; font-size: 14px; line-height: 1.4;',
        default => 'padding: 4px 10px; font-size: 12px; line-height: 1.4;',
    };
@endphp
<span
    class="grimba-fact-chip grimba-fact-chip--{{ str_replace('_', '-', $tier) }} grimba-fact-chip--{{ $size }}"
    title="{{ __('Fiabilité éditoriale') }} : {{ $label }}"
    aria-label="{{ __('Fiabilité') }} : {{ $label }}"
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
        text-decoration: none;
        white-space: nowrap;
        {{ $sizeStyle }}
    "
    data-fact-tier="{{ $tier }}"
>
    <span aria-hidden="true" style="
        font-family: 'Public Sans', system-ui, sans-serif;
        font-weight: 800;
        line-height: 1;
        opacity: 0.95;
    ">{{ $glyph }}</span>
    @if($showLabel)
        <span class="grimba-fact-chip__label">{{ $label }}</span>
    @endif
</span>
