<div class="echo-footer-content-3 col-lg-3 col-md-6 col-12">
    @if ($title = Arr::get($config, 'name'))
        <div class="echo-get-in-tuch">
            <h3>{!! BaseHelper::clean($title) !!}</h3>
        </div>
    @endif

    <div class="echo-footer-help">
        <ul class="list-unstyled">
            @foreach($items as $item)
                @if (($label = $item->label) && ($url = $item->url))
                    <li>
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
    </div>
</div>
