<?php

namespace Database\Seeders\Themes\Technology;

use Database\Seeders\Themes\Main\ThemeOptionSeeder as MainThemeOptionSeeder;

class ThemeOptionSeeder extends MainThemeOptionSeeder
{
    public function run(): void
    {
        $this->uploadFiles('general');

        parent::run();
    }

    public function getData(): array
    {
        return [
            ...parent::getData(),
            'site_name' => 'Echo - Technology',
            'primary_color' => '#924FFF',
            'secondary_color' => '#191A23',
            'tertiary_color' => '#f05600',
            'logo' => $this->filePath('general/logo.png', 'technology'),
            'favicon' => $this->filePath('general/favicon.png', 'technology'),
        ];
    }
}
