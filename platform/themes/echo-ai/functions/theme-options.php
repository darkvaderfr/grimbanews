<?php

app()->booted(function (): void {
    theme_option()
        ->setField([
            'id' => 'gradient_from',
            'section_id' => 'opt-text-subsection-styles',
            'type' => 'customColor',
            'label' => __('Gradient from'),
            'attributes' => [
                'name' => 'gradient_from',
                'value' => '#9F68F5',
            ],
        ])
        ->setField([
            'id' => 'gradient_to',
            'section_id' => 'opt-text-subsection-styles',
            'type' => 'customColor',
            'label' => __('Gradient to'),
            'attributes' => [
                'name' => 'gradient_to',
                'value' => '#BEE44F',
            ],
        ]);
});
