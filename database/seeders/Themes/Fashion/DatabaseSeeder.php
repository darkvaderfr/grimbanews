<?php

namespace Database\Seeders\Themes\Fashion;

use Database\Seeders\Themes\Main\MenuSeeder;

class DatabaseSeeder extends \Database\Seeders\Themes\Main\DatabaseSeeder
{
    protected string $themeName = 'echo-fashion';

    public function getSeeders(): array
    {
        return [
            ...parent::getSeeders(),
            AdsSeeder::class,
            PageSeeder::class,
            ThemeOptionSeeder::class,
            WidgetSeeder::class,
            BlogSeeder::class,
            MenuSeeder::class,
            GallerySeeder::class,
        ];
    }
}
