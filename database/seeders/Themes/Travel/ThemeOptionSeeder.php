<?php

namespace Database\Seeders\Themes\Travel;

use Database\Seeders\Themes\Main\ThemeOptionSeeder as MainThemeOptionSeeder;

class ThemeOptionSeeder extends MainThemeOptionSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('general', 'travel');

        return [
            ...parent::getData(),
            'site_name' => 'Echo - Travel',
            'primary_color' => '#FFCC48',
            'secondary_color' => '#714707',
            'heading_font' => 'DM Sans',
            'logo' => $this->filePath('general/logo.png', 'travel'),
            'favicon' => $this->filePath('general/favicon.png', 'travel'),
            'logo_dark' => $this->filePath('general/logo.png', 'travel'),
        ];
    }
}
