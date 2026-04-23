<div class="echo-latest-news-area home-eight" style="padding-top: 0 !important; padding-bottom: 0 !important;">
    <div class="echo-right-ct-1">
        @if ($title = Arr::get($config, 'title'))
            <div class="hm5-feature-title right">
                <div class="hm-5-title-btn">
                    <div class="hm-5-main-title">
                        <h2>{!! BaseHelper::clean($title) !!}</h2>
                    </div>
                </div>
            </div>
        @endif
        <div class="echo-home-1-hero-area-top-story">
            <div class="right-side-banner">
                <ul class="category">
                    @foreach ($tabs as $tab)
                        @continue(empty($category = Arr::get($tab, 'category')))

                        @php
                            $backgroundImage = Arr::get($tab, 'background_image') ? RvMedia::url(Arr::get($tab, 'background_image')) : null;
                            $backgroundColor = Arr::get($tab, 'background_color', '#efefef');
                        @endphp
                        <li
                            @style([
                                "--background-color: $backgroundColor" => $backgroundColor,
                                "--background-image: url($backgroundImage)" => $backgroundImage,
                            ])
                        >
                            <a title="{{ $category->name }}" class="text-uppercase" href="{{ $category->url }}">
                                <span>{{ $loop->iteration > 10 ? $loop->iteration : "0$loop->iteration"  }}</span><span>/</span>
                                <span class="truncate-custom truncate-1-custom">{{ $category->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

