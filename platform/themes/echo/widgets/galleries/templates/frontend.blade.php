@if (is_plugin_active('gallery') && $galleries->isNotEmpty())
    <div class="echo-right-ct-1">
        <div class="echo-feature-area-right-site-gallery ">
            @if ($title = Arr::get($config, 'title'))
                <p class="h5">{!! BaseHelper::clean($title) !!}</p>
            @endif

            <ul class="gallery-wrapper">
                @foreach($galleries as $gallery)
                    <li class="wrapper">
                        <div class="image-area">
                            <a href="{{ $gallery->url }}">
                                {{ RvMedia::image($gallery->image, $gallery->name, 'small') }}
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
