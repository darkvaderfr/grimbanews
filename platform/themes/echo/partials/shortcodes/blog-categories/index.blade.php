@php
    $style = (int) Str::after($shortcode->style, 'style-') <= 3 ? $shortcode->style : 'style-1';

    if ($style == 'style-2') {
            Theme::asset()
                ->container('footer')
                ->add('blog-categories-tab-js', Theme::asset()->url('js/blog-categories.js'));
        }

    if ($shortcode->enable_lazy_loading !== 'yes') {
         $sidebar = $shortcode->sidebar;

        $variableStyles = \Theme\Echo\Support\ThemeHelper::getVariableStylesByShortcode($shortcode);
    }
@endphp

@if ($shortcode->enable_lazy_loading === 'yes')
    <div data-bb-toggle="shortcode-load-ajax" class="wrapper-shortcode-load-ajax"
         data-url="{{ route('public.ajax.shortcode-blog-categories', [
            'shortcode' => $shortcode->toArray(),
        ]) }}"
    >
        <div class="backdrop"></div>
        <div class="loading"></div>
    </div>
@else
    {!! Theme::partial('shortcodes.blog-categories.styles.' . $style, compact('shortcode', 'firstCategory', 'categories', 'title', 'postsPerCategory', 'categoryIds', 'variableStyles', 'sidebar')) !!}
@endif
