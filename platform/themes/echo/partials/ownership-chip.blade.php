@php
    /**
     * 8-category ownership chip (Ground-fidelity).
     *
     * @var ?string $type     Optional. ownership_type column value.
     * @var ?string $owner    Optional. owner_name column value.
     * @var ?string $category Optional. Pre-resolved slug. If given, wins.
     * @var string  $size     Optional. sm | md | lg (default md)
     * @var bool    $showLabel Optional. Default true
     */
    use App\Ground\Ownership;

    $category  = $category ?? Ownership::category($type ?? null, $owner ?? null);
    $size      = $size ?? 'md';
    $showLabel = $showLabel ?? true;

    $color = Ownership::color($category);
    $label = Ownership::label($category);
    $short = Ownership::shortLabel($category);
    $icon  = Ownership::icon($category);

    $sizeStyle = match ($size) {
        'sm' => 'padding: 2px 8px; font-size: 11px; line-height: 1.4;',
        'lg' => 'padding: 6px 14px; font-size: 14px; line-height: 1.4;',
        default => 'padding: 4px 10px; font-size: 12px; line-height: 1.4;',
    };
@endphp
<span
    class="grimba-own-chip grimba-own-chip--{{ str_replace('_', '-', $category) }} grimba-own-chip--{{ $size }}"
    title="{{ __('Type de propriété') }} : {{ $label }}{{ ! empty($owner) ? ' · ' . $owner : '' }}"
    aria-label="{{ __('Propriété') }} : {{ $label }}"
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
    data-own-category="{{ $category }}"
>
    <x-core::icon name="{{ $icon }}" style="width: 12px; height: 12px;" />
    @if($showLabel)
        <span class="grimba-own-chip__label">{{ $size === 'sm' ? $short : $label }}</span>
    @endif
</span>
