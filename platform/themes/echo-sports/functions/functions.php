<?php

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
    remove_sidebar('header_top_sidebar');

    theme_option()
        ->setField([
            'id' => 'header_text_color',
            'section_id' => 'opt-text-subsection-styles',
            'type' => 'customColor',
            'label' => __('Header text color'),
            'attributes' => [
                'name' => 'header_text_color',
                'value' => null,
            ],
        ]);
});
