<?php

namespace Database\Seeders\Themes\Photography;

use Database\Seeders\Themes\Main\DatabaseSeeder as BaseDatabaseSeeder;
use Database\Seeders\Themes\Main\MenuSeeder;

class DatabaseSeeder extends BaseDatabaseSeeder
{
    protected string $themeName = 'echo-photography';

    public function getSeeders(): array
    {
        return [
            ...parent::getSeeders(),
            PageSeeder::class,
            ThemeOptionSeeder::class,
            WidgetSeeder::class,
            BlogSeeder::class,
            MenuSeeder::class,
        ];
    }
}
