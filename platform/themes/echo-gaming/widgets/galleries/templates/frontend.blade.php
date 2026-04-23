@if (is_plugin_active('gallery') && $galleries->isNotEmpty())
    <div class="echo-banner-right-area-content">
        <div class="right-side-gallery">
            @if ($title = Arr::get($config, 'title'))
                <h5 class="title">{!! BaseHelper::clean($title) !!}</h5>
            @endif

            <ul class="gallery-inner">
                @foreach($galleries as $gallery)
                    <li class="gallery-item">
                        <a href="{{ $gallery->url }}" title="{{ $gallery->name }}">
                            {{ RvMedia::image($gallery->image, $gallery->name, 'small') }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
