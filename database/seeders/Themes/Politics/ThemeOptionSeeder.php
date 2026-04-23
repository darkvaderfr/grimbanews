<?php

namespace Database\Seeders\Themes\Politics;

use Database\Seeders\Themes\Main\ThemeOptionSeeder as MainThemeOptionSeeder;

class ThemeOptionSeeder extends MainThemeOptionSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('general', 'politics');

        return [
            ...parent::getData(),
            'site_name' => 'Echo - Politics',
            'primary_color' => '#ff390e',
            'secondary_color' => '#47164F',
            'logo' => $this->filePath('general/logo.png', 'politics'),
            'favicon' => $this->filePath('general/favicon.png', 'politics'),
            'logo_dark' => $this->filePath('general/logo.png', 'politics'),
            'heading_font' => 'DM Sans',
        ];
    }
}
