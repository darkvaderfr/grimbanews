<ul class="list-unstyled" style="list-style-type: none">
    @foreach($items as $item)
        @php
            $item = collect($item)->pluck('value', 'key');
            $url = Arr::get($item, 'url');
            $title = Arr::get($item, 'title');
        @endphp

        @continue(! $url && ! $title)
        <li>
            <a href="{{ $url }}">
                @if ($icon = Arr::get($item, 'icon'))
                    {!! BaseHelper::renderIcon($icon) !!}
                @endif

                <span title="{{ $title }}" class="truncate-custom truncate-1-custom">{!! BaseHelper::clean($title) !!}</span>
            </a>
        </li>
    @endforeach
</ul>
