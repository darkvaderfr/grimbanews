<?php

namespace Database\Seeders\Themes\Travel;

use Database\Seeders\Themes\Main\DatabaseSeeder as BaseDatabaseSeeder;
use Database\Seeders\Themes\Main\MenuSeeder;

class DatabaseSeeder extends BaseDatabaseSeeder
{
    protected string $themeName = 'echo-travel';

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
