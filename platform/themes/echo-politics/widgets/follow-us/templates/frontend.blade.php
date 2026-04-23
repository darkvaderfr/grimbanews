@if ($items)
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
            <div class="echo-feature-area-right-site-follower">
                @include(Theme::getThemeNamespace('widgets.follow-us.templates.partials.items'))
            </div>
        </div>
    </div>
@endif
