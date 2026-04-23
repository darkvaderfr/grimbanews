@if ($items)
    <ul class="footer-menu-area">
        @foreach($items as $item)
            @if (($label = $item->label) && ($url = $item->url))
                <li class="menu-item">
                    <a
                        href="{{ url($url) }}"
                        {!! $item->attributes ? BaseHelper::clean($item->attributes) : null !!}
                    >
                        {!! BaseHelper::clean($label) !!}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
@endif
