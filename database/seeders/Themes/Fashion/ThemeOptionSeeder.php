<?php

namespace Database\Seeders\Themes\Fashion;

use Database\Seeders\Themes\Main\ThemeOptionSeeder as MainThemeOptionSeeder;

class ThemeOptionSeeder extends MainThemeOptionSeeder
{
    public function getData(): array
    {
        return [
            ...parent::getData(),
            'site_name' => 'Echo - Fashion',
            'primary_color' => '#924FFF',
            'footer_text_color' => '#5e5e5e',
            'footer_background_color' => '#F9F9F9',
            'footer_heading_color' => '#181823',
            'logo' => $this->filePath('general/logo.png', 'fashion'),
            'favicon' => $this->filePath('general/favicon.png', 'fashion'),
            'logo_dark' => $this->filePath('general/logo-dark.png', 'fashion'),
        ];
    }
}
