<?php

use Botble\Language\Facades\Language;
use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\ThemeOption;

app('events')->listen(RenderingThemeOptionSettings::class, function (): void {
    theme_option()
        ->setField([
            'id' => 'copyright',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'text',
            'label' => __('Copyright'),
            'attributes' => [
                'name' => 'copyright',
                'value' => __('© %Y Your Company. All right reserved.'),
                'options' => [
                    'class' => 'form-control',
                    'placeholder' => __('Change copyright'),
                    'data-counter' => 250,
                ],
            ],
            'helper' => __('Copyright on footer of site, use %Y to show current year.'),
        ])
        ->setSection([
            'title' => __('Styles'),
            'id' => 'opt-text-subsection-styles',
            'subsection' => true,
            'icon' => 'ti ti-brush',
            'fields' => [
                [
                    'id' => 'primary_font',
                    'type' => 'googleFonts',
                    'label' => __('Primary font'),
                    'attributes' => [
                        'name' => 'primary_font',
                        'value' => 'Inter',
                    ],
                ],
                [
                    'id' => 'heading_font',
                    'type' => 'googleFonts',
                    'label' => __('Heading font'),
                    'attributes' => [
                        'name' => 'heading_font',
                        'value' => 'Bona Nova',
                    ],
                ],
                [
                    'id' => 'primary_color',
                    'type' => 'customColor',
                    'label' => __('Primary color'),
                    'attributes' => [
                        'name' => 'primary_color',
                        'value' => '#537FE7',
                    ],
                ],
                [
                    'id' => 'secondary_color',
                    'type' => 'customColor',
                    'label' => __('Secondary color'),
                    'attributes' => [
                        'name' => 'secondary_color',
                        'value' => '',
                    ],
                ],
                [
                    'id' => 'text_color',
                    'type' => 'customColor',
                    'label' => __('Text color'),
                    'attributes' => [
                        'name' => 'text_color',
                        'value' => '#5E5E5E',
                    ],
                ],
                [
                    'id' => 'heading_color',
                    'type' => 'customColor',
                    'label' => __('Heading color'),
                    'attributes' => [
                        'name' => 'heading_color',
                        'value' => '#181823',
                    ],
                ],
                [
                    'id' => 'footer_background_color',
                    'type' => 'customColor',
                    'label' => __('Footer background color'),
                    'attributes' => [
                        'name' => 'footer_background_color',
                        'value' => '#181823',
                    ],
                ],
                [
                    'id' => 'footer_background_image',
                    'type' => 'mediaImage',
                    'label' => __('Footer background image'),
                    'attributes' => [
                        'name' => 'footer_background_image',
                        'value' => null,
                    ],
                ],
                [
                    'id' => 'footer_heading_color',
                    'type' => 'customColor',
                    'label' => __('Footer heading color'),
                    'attributes' => [
                        'name' => 'footer_heading_color',
                        'value' => '#fff',
                    ],
                ],
                [
                    'id' => 'footer_text_color',
                    'type' => 'customColor',
                    'label' => __('Footer text color'),
                    'attributes' => [
                        'name' => 'footer_text_color',
                        'value' => '#fff',
                    ],
                ],
                [
                    'id' => 'theme_style',
                    'type' => 'customSelect',
                    'label' => __('Theme style'),
                    'attributes' => [
                        'name' => 'theme_style',
                        'list' => [
                            'dark' => __('Dark mode'),
                            'light' => __('Light mode'),
                        ],
                        'value' => 'light',
                    ],
                ],
                [
                    'id' => 'breadcrumb_background_image',
                    'type' => 'mediaImage',
                    'label' => __('Breadcrumb background image'),
                    'attributes' => [
                        'name' => 'breadcrumb_background_image',
                        'value' => null,
                    ],
                ],
                [
                    'id' => 'breadcrumb_background_color',
                    'type' => 'customColor',
                    'label' => __('Breadcrumb background color'),
                    'attributes' => [
                        'name' => 'breadcrumb_background_color',
                        'value' => null,
                    ],
                ],
                [
                    'id' => 'breadcrumb_text_color',
                    'type' => 'customColor',
                    'label' => __('Breadcrumb text color'),
                    'attributes' => [
                        'name' => 'breadcrumb_text_color',
                        'value' => 'transparent',
                    ],
                ],
                [
                    'id' => 'theme_style',
                    'section_id' => 'opt-text-subsection-general',
                    'type' => 'customSelect',
                    'label' => __('Theme style'),
                    'attributes' => [
                        'name' => 'theme_style',
                        'list' => [
                            'auto' => __('Auto'),
                            'dark' => __('Dark mode'),
                            'light' => __('Light mode'),
                        ],
                        'value' => 'light',
                    ],
                ],
            ],
        ])
        ->when(is_plugin_active('blog'), function (ThemeOption $option) {
            return $option
                ->setField([
                    'id' => 'blog_author_style',
                    'section_id' => 'opt-text-subsection-blog',
                    'type' => 'customSelect',
                    'label' => __('Author display style'),
                    'attributes' => [
                        'name' => 'blog_author_style',
                        'list' => [
                            'avatar_start' => __('Avatar on start'),
                            'avatar_center' => __('Avatar center'),
                        ],
                    ],
                ])
                ->setField([
                    'id' => 'blog_description_style',
                    'section_id' => 'opt-text-subsection-blog',
                    'type' => 'customSelect',
                    'label' => __('Description style'),
                    'attributes' => [
                        'name' => 'blog_description_style',
                        'list' => [
                            'normal' => __('Normal'),
                            'drop_cap' => __('Drop cap'),
                        ],
                    ],
                ])
                ->setField([
                    'id' => 'blog_style',
                    'section_id' => 'opt-text-subsection-blog',
                    'type' => 'customSelect',
                    'label' => __('Blog list style'),
                    'attributes' => [
                        'name' => 'post_style',
                        'list' => [
                            'card' => __('Card'),
                            'list' => __('List'),
                            'grid' => __('Grid'),
                            'mixed' => __('Mixed'),
                        ],
                        'value' => 'card',
                    ],
                ])
                ->setField([
                    'id' => 'blog_sidebar_enabled',
                    'section_id' => 'opt-text-subsection-blog',
                    'type' => 'customSelect',
                    'label' => __('Enable blog sidebar?'),
                    'attributes' => [
                        'name' => 'blog_sidebar_enabled',
                        'list' => [
                            1 => __('Yes'),
                            0 => __('No'),
                        ],
                        'value' => 1,
                    ],
                ])
                ->setField([
                    'id' => 'blog_post_meta_display_default',
                    'section_id' => 'opt-text-subsection-blog',
                    'type' => 'hidden',
                    'attributes' => [
                        'name' => 'blog_post_meta_display',
                        'value' => '[]',
                    ],
                ])
                ->setField([
                    'id' => 'blog_post_meta_display',
                    'section_id' => 'opt-text-subsection-blog',
                    'type' => 'multiChecklist',
                    'label' => __('Post meta display (blog list)'),
                    'attributes' => [
                        'name' => 'blog_post_meta_display[]',
                        'value' => json_decode(theme_option('blog_post_meta_display', '["reading_time"]')) ?: [],
                        'choices' => [
                            'author_name' => __('Author name'),
                            'reading_time' => __('Reading time'),
                            'views_count' => __('Views count'),
                        ],
                    ],
                ]);
        })
        ->setField([
            'id' => 'logo_dark',
            'section_id' => 'opt-text-subsection-logo',
            'type' => 'mediaImage',
            'label' => __('Logo Dark'),
            'attributes' => [
                'name' => 'logo_dark',
                'value' => null,
            ],
        ])
        ->setField([
            'id' => 'logo_height',
            'section_id' => 'opt-text-subsection-logo',
            'type' => 'number',
            'label' => __('Logo height (px)'),
            'attributes' => [
                'name' => 'logo_height',
                'value' => 60,
                'options' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 150,
                ],
            ],
        ])
        ->setField([
            'id' => 'back_to_top_enabled',
            'section_id' => 'opt-text-subsection-general',
            'type' => 'customSelect',
            'label' => __('Enable button back to top?'),
            'attributes' => [
                'name' => 'back_to_top_enabled',
                'list' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => 1,
            ],
        ])
        ->when(is_plugin_active('language') && count(Language::getSupportedLocales()) > 1, function (): void {
            theme_option()->setField([
                'id' => 'language_switcher_enabled',
                'section_id' => 'opt-text-subsection-general',
                'type' => 'customSelect',
                'label' => __('Enable language switcher?'),
                'attributes' => [
                    'name' => 'language_switcher_enabled',
                    'list' => [
                        1 => __('Yes'),
                        0 => __('No'),
                    ],
                    'value' => 1,
                ],
            ]);
        });
});
