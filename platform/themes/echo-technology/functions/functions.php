<?php

register_sidebar([
    'id' => 'header_sidebar',
    'name' => __('Header sidebar'),
    'description' => __('Header sidebar'),
]);

app()->booted(function (): void {
    remove_sidebar('header_top_sidebar');

    theme_option()
        ->setField([
            'id' => 'tertiary_color',
            'section_id' => 'opt-text-subsection-styles',
            'type' => 'customColor',
            'label' => __('Tertiary color'),
            'attributes' => [
                'name' => 'tertiary_color',
                'value' => null,
            ],
        ]);
});
