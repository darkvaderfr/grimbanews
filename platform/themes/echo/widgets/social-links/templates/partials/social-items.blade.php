@php
    $wrapperClass = $wrapperClass ?? null;
@endphp

@foreach($items as $social)
    @php
        $url = $social->getUrl();
        $name = $social->getName();
    @endphp

    @continue((! $social->getIcon() && ! $social->getImage()) && ! $url)

    <li @class($wrapperClass)>
        <a @if ($name) title="{{ $name }}" @endif target="_blank" href="{{ $url }}">
            {!! $social->getIconHtml() !!}
        </a>
    </li>
@endforeach
