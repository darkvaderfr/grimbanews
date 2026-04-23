<div class="echo-footer-content-1 col-lg-3 col-md-6 col-12">
    @if ($title = Arr::get($config, 'title'))
        <div class="echo-get-in-tuch">
            <h3>{!! BaseHelper::clean($title) !!}</h3>
        </div>
    @endif

    <div class="echo-footer-address">
        @if ($address = Arr::get($config, 'address'))
            <span><i class="fa-regular fa-map"></i> {!! BaseHelper::clean($address) !!}</span>
        @endif

        @if ($hotline = Arr::get($config, 'hotline'))
            <span><i class="fa-regular fa-phone"></i>
                <a href="tel:{{ $hotline }}">{!! BaseHelper::clean($hotline) !!}</a>
            </span>
        @endif

        @if ($email = Arr::get($config, 'email'))
            <span><i class="fa-sharp fa-regular fa-envelope"></i>
                {{ Html::mailto($email) }}
            </span>
        @endif

        @if ($socialLinks = Theme::getSocialLinks())
            <div class="echo-footer-social-media">
                @foreach($socialLinks as $social)
                    @php
                        $url = $social->getUrl();
                        $name = $social->getName();
                    @endphp

                    @continue((! $social->getIcon() && ! $social->getImage()) && ! $url)
                    <a
                        @if ($name) title="{{ $name }}" @endif
                        href="{{ $url }}"
                        target="_blank"
                    >
                        {!! $social->getIconHtml() !!}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
