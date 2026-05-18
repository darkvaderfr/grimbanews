<div {!! Html::attributes(array_merge($attributes, [
    'data-ad-provider' => $ad['provider'] ?? 'direct',
    'data-grimba-ad-placement' => $ad['placement'] ?? null,
])) !!}>
    <a class="grimba-direct-ad" href="{{ $ad['directUrl'] }}" @if(str_starts_with((string) $ad['directUrl'], 'http')) target="_blank" rel="sponsored noopener" @endif>
        <span class="grimba-direct-ad__signal"></span>
        <span class="grimba-direct-ad__copy">
            <strong>{{ __('Sponsor this coverage') }}</strong>
            <span>{{ __('Reach readers comparing source bias, ownership, and global news context.') }}</span>
        </span>
        <span class="grimba-direct-ad__cta">{{ __('Advertise') }}</span>
    </a>
</div>
