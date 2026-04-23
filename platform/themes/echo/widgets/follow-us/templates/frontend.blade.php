@if ($items)
    <div class="echo-feature-area">
        <div class="echo-feature-area-right-site-follower">
            @if ($title = Arr::get($config, 'title'))
                <div class="echo-home-2-title">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="echo-home-2-main-title">
                                <p class="h5">{!! BaseHelper::clean($title) !!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @include(Theme::getThemeNamespace('widgets.follow-us.templates.partials.items'))
        </div>
    </div>
@endif
