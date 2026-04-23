@php
    /**
     * Bias Badge Component — GroundNews-inspired
     *
     * Displays political bias indicator: Left / Center / Right
     *
     * @var string|null $bias One of: 'left', 'center', 'right', 'unknown'
     * @var bool $showLabel Whether to show text label (default: true)
     * @var string $size Badge size: 'sm', 'md', 'lg' (default: 'sm')
     */

    $bias = $bias ?? ($post->bias_rating ?? null);
    $showLabel = $showLabel ?? true;
    $size = $size ?? 'sm';

    // Bias configuration
    $biasConfig = [
        'left' => [
            'label' => 'Gauche',
            'color' => '#3b82f6',      // Blue
            'bg' => 'rgba(59, 130, 246, 0.15)',
            'icon' => 'ti ti-arrow-left'
        ],
        'center' => [
            'label' => 'Centre',
            'color' => '#22c55e',      // Green
            'bg' => 'rgba(34, 197, 94, 0.15)',
            'icon' => 'ti ti-minus'
        ],
        'right' => [
            'label' => 'Droite',
            'color' => '#ef4444',      // Red
            'bg' => 'rgba(239, 68, 68, 0.15)',
            'icon' => 'ti ti-arrow-right'
        ],
        'unknown' => [
            'label' => 'Non évalué',
            'color' => '#9ca3af',      // Gray
            'bg' => 'rgba(156, 161, 169, 0.15)',
            'icon' => 'ti ti-question-mark'
        ]
    ];

    $config = $biasConfig[$bias] ?? $biasConfig['unknown'];

    // Size classes
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-base'
    ];
@endphp

@if($bias)
    <span
        class="bias-badge bias-badge--{{ $size }} d-inline-flex align-items-center gap-1 rounded-pill"
        style="
            background: {{ $config['bg'] }};
            color: {{ $config['color'] }};
            border: 1px solid {{ $config['color'] }}30;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.2s ease;
        "
        data-bias="{{ $bias }}"
        title="Biais éditorial: {{ $config['label'] }}"
    >
        <x-core::icon name="{{ $config['icon'] }}" style="width: 14px; height: 14px;" />
        @if($showLabel)
            <span>{{ $config['label'] }}</span>
        @endif
    </span>
@endif
