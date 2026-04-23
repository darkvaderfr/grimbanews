<?php

namespace Database\Seeders\Themes\Food;

use Database\Seeders\Themes\Main\ThemeOptionSeeder as MainThemeOptionSeeder;

class ThemeOptionSeeder extends MainThemeOptionSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('general', 'food');

        return [
            ...parent::getData(),
            'site_name' => 'Echo - Food',
            'primary_color' => '#F5D253',
            'secondary_color' => '#003811',
            'tertiary_color' => '#FF9A82',
            'footer_background_color' => '#003811',
            'footer_heading_color' => '#ffffff',
            'logo' => $this->filePath('general/logo.png', 'food'),
            'favicon' => $this->filePath('general/favicon.png', 'food'),
            'logo_dark' => $this->filePath('general/logo-dark.png', 'food'),
        ];
    }
}
