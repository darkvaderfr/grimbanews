<div class="widget-blog-post-wrapper">
    <div class="row">
        @if ($title = Arr::get($config, 'title'))
            <div class="col-lg-12">
                <div class="title-area">
                    <h6 class="sect-title">{!! BaseHelper::clean($title) !!}</h6>
                </div>
            </div>
        @endif
        <div class="col-lg-12">
            <div class="echo-banner-right-area-content">
                <div class="right-side-news">
                    <ul>
                        @foreach($posts as $post)
                            @include(Theme::getThemeNamespace('widgets.blog-posts.templates.partials.styles._post-item'))
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
