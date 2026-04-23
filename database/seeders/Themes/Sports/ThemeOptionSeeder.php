<?php

namespace Database\Seeders\Themes\Sports;

use Database\Seeders\Themes\Main\ThemeOptionSeeder as MainThemeOptionSeeder;

class ThemeOptionSeeder extends MainThemeOptionSeeder
{
    public function run(): void
    {
        parent::run();
    }

    public function getData(): array
    {
        return [
            ...parent::getData(),
            'site_name' => 'Echo - Sports',
            'primary_color' => '#D5225B',
            'secondary_color' => '#4C091E',
            'header_text_color' => '#ffffff',
            'text_color' => '#999',
            'logo' => $this->filePath('general/logo.png', 'sports'),
            'favicon' => $this->filePath('general/favicon.png', 'sports'),
            'logo_dark' => $this->filePath('general/logo-dark.png', 'sports'),
            'heading_font' => 'Bebas Neue',
            'footer_background_image' => $this->filePath('backgrounds/footer-bg.png', 'sports'),
        ];
    }
}
