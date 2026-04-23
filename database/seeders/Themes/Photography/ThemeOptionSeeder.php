<?php

namespace Database\Seeders\Themes\Photography;

use Database\Seeders\Themes\Main\ThemeOptionSeeder as MainThemeOptionSeeder;

class ThemeOptionSeeder extends MainThemeOptionSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('general', 'photography');

        return [
            ...parent::getData(),
            'site_name' => 'Echo - Photography',
            'primary_color' => '#FF4962',
            'secondary_color' => '#060F18',
            'footer_background_color' => '#060F18',
            'primary_font' => 'DM Sans',
            'heading_font' => 'Bebas Neue',
            'gray_filter_image' => true,
            'logo' => $this->filePath('general/logo.png', 'photography'),
            'favicon' => $this->filePath('general/favicon.png', 'photography'),
            'logo_dark' => $this->filePath('general/logo-dark.png', 'photography'),
        ];
    }
}
