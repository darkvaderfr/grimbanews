@if ($items)
    <div class="echo-feature-area-right-site-follower row">
        @if ($title = Arr::get($config, 'title'))
            <div class="col-lg-12">
                <div class="title-area right">
                    <h6 class="sect-title">{!! BaseHelper::clean($title) !!}</h6>
                </div>
            </div>
        @endif

        @include(Theme::getThemeNamespace('widgets.follow-us.templates.partials.items'))
    </div>
@endif
