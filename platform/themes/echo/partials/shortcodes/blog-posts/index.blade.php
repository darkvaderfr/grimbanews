@php
    if ($shortcode->enable_lazy_loading !== 'yes') {
        $style = (int) Str::after($shortcode->style, 'style-') <= 18 ? $shortcode->style : 'style-1';
        $style = $style ?: 'style-1';

        $variableStyles = \Theme\Echo\Support\ThemeHelper::getVariableStylesByShortcode($shortcode);

        $sidebar = $shortcode->sidebar;
    }
@endphp

@if ($shortcode->enable_lazy_loading === 'yes')
    <div data-bb-toggle="shortcode-load-ajax" class="wrapper-shortcode-load-ajax"
        data-url="{{ route('public.ajax.shortcode-blog-posts', [
            'shortcode' => $shortcode->toArray(),
        ]) }}"
    >
        <div class="backdrop"></div>
        <div class="loading"></div>
    </div>
@else
    {!! Theme::partial('shortcodes.blog-posts.styles.' . $style, compact('shortcode', 'posts', 'sidebar', 'variableStyles')) !!}
@endif
