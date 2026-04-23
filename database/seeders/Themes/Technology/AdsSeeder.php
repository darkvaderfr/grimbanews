<?php

namespace Database\Seeders\Themes\Technology;

use Database\Seeders\Themes\Main\AdsSeeder as MainAdsSeeder;

class AdsSeeder extends MainAdsSeeder
{
    protected function getData(): array
    {
        $this->filePath('banner', 'technology');

        return [
            [
                'key' => 'UROL9F9ZZVAA',
                'image' => $this->filePath('banner/1.png', 'technology'),
                'url' => '/',
            ],
        ];
    }
}
