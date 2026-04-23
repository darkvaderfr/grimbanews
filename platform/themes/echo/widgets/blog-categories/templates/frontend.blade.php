@if (Arr::get($config, 'enable_lazy_loading') === 'yes')
    <div data-bb-toggle="widget-load-ajax" class="wrapper-widget-load-ajax"
         data-widget-name="blog-categories"
         data-url="{{ route('public.ajax.widget-blog-categories', [
            'config' => $config,
            'sidebar' => $sidebar,
        ]) }}"
    >
        <div class="backdrop"></div>
        <div class="loading"></div>
    </div>
@else
    @include(Theme::getThemeNamespace('widgets.blog-categories.templates.partials.blog-categories'))
@endif
