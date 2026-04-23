<?php

namespace Database\Seeders\Themes\Main;

use Botble\Page\Database\Traits\HasPageSeeder;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Botble\Theme\Database\Traits\HasThemeOptionSeeder;

class ThemeOptionSeeder extends ThemeSeeder
{
    use HasPageSeeder;
    use HasThemeOptionSeeder;

    public function run(): void
    {
        $this->createThemeOptions($this->getData());
    }

    public function getData(): array
    {
        $this->uploadFiles('backgrounds', 'main');

        return [
            'site_name' => 'Echo',
            'site_title' => 'News & Magazine Laravel Script.',
            'seo_description' => 'Echo is a modern, clean, and professional Laravel script that is suitable for news, magazine, blog, and any kind of website.',
            'homepage_id' => $this->getPageId('Homepage'),
            'logo' => $this->filePath('general/logo.png', 'main'),
            'logo_dark' => $this->filePath('general/logo-dark.png', 'main'),
            'favicon' => $this->filePath('general/favicon.png', 'main'),
            'blog_page_id' => $this->getPageId('Blog'),
            'primary_font' => 'Inter',
            'heading_font' => 'Bona Nova',
            'theme_style' => 'auto',
            'secondary_color' => '#181823',
            'breadcrumb_background_image' => $this->filePath('backgrounds/breadcrumb.png', 'main'),
            'breadcrumb_background_color' => 'transparent',
            'breadcrumb_text_color' => '',
            'blog_author_style' => 'avatar_start',
            'blog_description_style' => 'normal',
            'copyright' => '©%Y Archi Elite JSC. All Rights Reserved.',
            'language_switcher_enabled' => true,
            'newsletter_popup_enable' => true,
            'newsletter_popup_image' => $this->filePath('general/newsletter-popup.png', 'main'),
            'newsletter_popup_title' => 'Let’s join our newsletter!',
            'newsletter_popup_subtitle' => 'Weekly Updates',
            'newsletter_popup_description' => 'Do not worry we don’t spam!',
            'social_links' => [
                [
                    [
                        'key' => 'name',
                        'value' => 'Facebook',
                    ],
                    [
                        'key' => 'icon',
                        'value' => 'ti ti-brand-facebook',
                    ],
                    [
                        'key' => 'url',
                        'value' => 'https://www.facebook.com/',
                    ],
                ],
                [
                    [
                        'key' => 'name',
                        'value' => 'Instagram',
                    ],
                    [
                        'key' => 'icon',
                        'value' => 'ti ti-brand-instagram',
                    ],
                    [
                        'key' => 'url',
                        'value' => 'https://www.instagram.com/',
                    ],
                ],
                [
                    [
                        'key' => 'name',
                        'value' => 'Twitter',
                    ],
                    [
                        'key' => 'icon',
                        'value' => 'ti ti-brand-x',
                    ],
                    [
                        'key' => 'url',
                        'value' => 'https://www.twitter.com/',
                    ],
                ],
                [
                    [
                        'key' => 'name',
                        'value' => 'YouTube',
                    ],
                    [
                        'key' => 'icon',
                        'value' => 'ti ti-brand-youtube',
                    ],
                    [
                        'key' => 'url',
                        'value' => 'https://www.youtube.com/',
                    ],
                ],
                [
                    [
                        'key' => 'name',
                        'value' => 'Pinterest',
                    ],
                    [
                        'key' => 'icon',
                        'value' => 'ti ti-brand-pinterest',
                    ],
                    [
                        'key' => 'url',
                        'value' => 'https://www.pinterest.com/',
                    ],
                ],
            ],
            'social_sharing' => [
                [
                    ['key' => 'social', 'value' => 'facebook'],
                    ['key' => 'icon', 'value' => 'ti ti-brand-facebook'],
                ],
                [
                    ['key' => 'social', 'value' => 'x'],
                    ['key' => 'icon', 'value' => 'ti ti-brand-x'],
                ],
                [
                    ['key' => 'social', 'value' => 'pinterest'],
                    ['key' => 'icon', 'value' => 'ti ti-brand-pinterest'],
                ],
                [
                    ['key' => 'social', 'value' => 'linkedin'],
                    ['key' => 'icon', 'value' => 'ti ti-brand-linkedin'],
                ],
                [
                    ['key' => 'social', 'value' => 'whatsapp'],
                    ['key' => 'icon', 'value' => 'ti ti-brand-whatsapp'],
                ],
                [
                    ['key' => 'social', 'value' => 'email'],
                    ['key' => 'icon', 'value' => 'ti ti-mail'],
                ],
            ],
        ];
    }
}
