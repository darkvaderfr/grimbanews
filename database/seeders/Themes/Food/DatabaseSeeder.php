<?php

namespace Database\Seeders\Themes\Food;

use Database\Seeders\Themes\Main\MenuSeeder;

class DatabaseSeeder extends \Database\Seeders\Themes\Main\DatabaseSeeder
{
    protected string $themeName = 'echo-food';

    public function getSeeders(): array
    {
        return [
            ...parent::getSeeders(),
            AnnouncementSeeder::class,
            PageSeeder::class,
            ThemeOptionSeeder::class,
            WidgetSeeder::class,
            BlogSeeder::class,
            MenuSeeder::class,
        ];
    }
}
