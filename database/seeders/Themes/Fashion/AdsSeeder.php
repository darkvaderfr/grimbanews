<?php

namespace Database\Seeders\Themes\Fashion;

use Database\Seeders\Themes\Main\AdsSeeder as MainAdsSeeder;

class AdsSeeder extends MainAdsSeeder
{
    protected function getData(): array
    {
        $this->filePath('banner', 'fashion');

        return [
            [
                'key' => 'UROL9F9ZZVAA',
                'image' => $this->filePath('banner/1.png', 'fashion'),
                'url' => '/',
            ],
        ];
    }
}
