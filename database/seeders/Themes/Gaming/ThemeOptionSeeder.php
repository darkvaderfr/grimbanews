<?php

namespace Database\Seeders\Themes\Gaming;

use Database\Seeders\Themes\Main\ThemeOptionSeeder as MainThemeOptionSeeder;

class ThemeOptionSeeder extends MainThemeOptionSeeder
{
    public function run(): void
    {
        $this->uploadFiles('general');
        $this->uploadFiles('backgrounds');

        parent::run();
    }

    public function getData(): array
    {
        return [
            ...parent::getData(),
            'site_name' => 'Echo - Gaming',
            'logo' => $this->filePath('general/logo.png', 'gaming'),
            'favicon' => $this->filePath('general/favicon.png', 'gaming'),
            'logo_dark' => $this->filePath('general/logo-dark.png', 'gaming'),
            'primary_color' => '#FF4962',
            'secondary_color' => '#080F19',
            'footer_background_image' => $this->filePath('backgrounds/footer-bg.png', 'gaming'),
        ];
    }
}
