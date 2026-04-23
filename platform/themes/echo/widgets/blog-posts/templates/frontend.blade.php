@if (Arr::get($config, 'enable_lazy_loading') === 'yes')
    <div data-bb-toggle="widget-load-ajax" class="wrapper-widget-load-ajax"
         data-widget-name="blog-posts"
         data-url="{{ route('public.ajax.widget-blog-posts', [
            'config' => $config,
            'sidebar' => $sidebar,
        ]) }}"
    >
        <div class="backdrop"></div>
        <div class="loading"></div>
    </div>
@else
    @include(Theme::getThemeNamespace('widgets.blog-posts.templates.partials.posts'))
@endif
