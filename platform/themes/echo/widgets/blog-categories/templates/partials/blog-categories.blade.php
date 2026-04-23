@if (! empty($tabs))
    <div class="echo-popular-hl-img">
        @if ($title)
            <div class="echo-home-2-title">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="echo-home-2-main-title">
                            <h5>{{ $title }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="echo-popular-item">
            <ul class="list-unstyled">
                @foreach ($tabs as $tab)
                    @continue(empty($tab['category']))

                    @php($category = $tab['category'])

                    <li
                        @style([
                            sprintf('background-image: url(%s) !important;', RvMedia::getImageUrl($bgImage = Arr::get($tab, 'background_image'))) => $bgImage,
                            sprintf('background-color: %s !important;', Arr::get($tab, 'background_color', 'transparent')) => true,
                        ])
                    >
                        <a href="{{ $category->url }}">
                            <h5>{{ $tab['title'] ?: $category->name }}</h5>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
