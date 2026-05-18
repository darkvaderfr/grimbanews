<div {!! Html::attributes(array_merge($attributes, [
    'data-ad-provider' => 'adsense',
    'data-grimba-ad-network' => 'adsense',
    'data-grimba-ad-slot' => $ad['slotId'],
])) !!}>
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="{{ $ad['clientId'] }}"
         data-ad-slot="{{ $ad['slotId'] }}"
         data-ad-format="{{ $ad['format'] ?? 'auto' }}"
         data-full-width-responsive="true"></ins>
</div>
