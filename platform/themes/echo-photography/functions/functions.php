<?php

use Botble\Media\Facades\RvMedia;

remove_sidebar('header_top_sidebar');

register_sidebar([
    'id' => 'header_sidebar',
    'name' => __('Header sidebar'),
    'description' => __('Header sidebar'),
]);

register_sidebar([
    'id' => 'above_footer_sidebar',
    'name' => __('Above footer sidebar'),
    'description' => __('Above footer sidebar'),
]);

app()->booted(function (): void {
    RvMedia::addSize('column', 600, 800);
    RvMedia::addSize('column-small', 365, 420);

    theme_option()
        ->setField([
            'id' => 'gray_filter_image',
            'section_id' => 'opt-text-subsection-styles',
            'type' => 'onOff',
            'label' => __('Gray filter image'),
            'attributes' => [
                'name' => 'gray_filter_image',
            ],
        ]);

    add_filter(THEME_FRONT_BODY, function (?string $html): string {
        if (! theme_option('gray_filter_image')) {
            return $html;
        }

        $style = '<style> :root { --filter-image: saturate(0) } </style>';

        return $html . $style;
    }, 15);
});
