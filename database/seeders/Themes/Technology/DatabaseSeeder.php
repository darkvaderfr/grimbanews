<?php

namespace Database\Seeders\Themes\Technology;

use Database\Seeders\Themes\Main\DatabaseSeeder as BaseDatabaseSeeder;

class DatabaseSeeder extends BaseDatabaseSeeder
{
    protected string $themeName = 'echo-technology';

    protected function getSeeders(): array
    {
        return [
            ...parent::getSeeders(),
            AdsSeeder::class,
            PageSeeder::class,
            ThemeOptionSeeder::class,
            WidgetSeeder::class,
            BlogSeeder::class,
        ];
    }
}
