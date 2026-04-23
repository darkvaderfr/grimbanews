<?php

namespace Database\Seeders\Themes\AI;

use Database\Seeders\Themes\Main\MenuSeeder;

class DatabaseSeeder extends \Database\Seeders\Themes\Main\DatabaseSeeder
{
    protected string $themeName = 'echo-ai';

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
