<section class="echo-contact-area home-nine" @style($variableStyles)>
    <div class="hm-6-container">
        <div class="contact-inner">
            <div class="inner-wrapper">
                @if ($title = $shortcode->title)
                    <h3 class="content-title text-center">{!! BaseHelper::clean($title) !!}</h3>
                @endif

                @if ($subtitle = $shortcode->subtitle)
                    <p class="desc text-center">{!! BaseHelper::clean($subtitle) !!}</p>
                @endif

                <ul class="list-unstyled social-area">
                    @foreach(range(1, $shortcode->quantity) as $i)
                        @continue(! $icon = $shortcode->{"icon_$i"})
                        <li>
                            <a title="{{ $shortcode->{"title_$i"} }}" target="_blank" href="{{ $shortcode->{"url_$i"} }}">
                                {!! BaseHelper::renderIcon($icon) !!}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
