<?php

namespace Database\Seeders\Themes\AI;

use Database\Seeders\Themes\Main\ThemeOptionSeeder as MainThemeOptionSeeder;

class ThemeOptionSeeder extends MainThemeOptionSeeder
{
    public function getData(): array
    {
        $this->uploadFiles('general', 'ai');

        return [
            ...parent::getData(),
            'site_name' => 'Echo - AI',
            'primary_color' => '#7817F2',
            'secondary_color' => '#181823',
            'logo' => $this->filePath('general/logo.png', 'ai'),
            'favicon' => $this->filePath('general/favicon.png', 'ai'),
            'logo_dark' => $this->filePath('general/logo-dark.png', 'ai'),
            'heading_font' => 'Jost',
        ];
    }
}
