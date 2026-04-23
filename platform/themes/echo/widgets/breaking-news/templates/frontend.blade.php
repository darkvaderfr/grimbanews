@if (Arr::get($config, 'enable_lazy_loading') === 'yes')
    <div data-bb-toggle="widget-load-ajax" class="wrapper-widget-load-ajax"
         data-widget-name="breaking-news"
         data-url="{{ route('public.ajax.widget-breaking-news', [
            'config' => $config,
            'sidebar' => $sidebar,
        ]) }}"
    >
        <div class="backdrop"></div>
        <div class="loading"></div>
    </div>
@else
    @include(Theme::getThemeNamespace('widgets.breaking-news.templates.partials.breaking-news'))
@endif
