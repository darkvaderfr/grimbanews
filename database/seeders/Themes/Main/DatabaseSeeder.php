<?php

namespace Database\Seeders\Themes\Main;

use Botble\ACL\Database\Seeders\UserSeeder;
use Botble\Contact\Database\Seeders\ContactSeeder;
use Botble\Language\Database\Seeders\LanguageSeeder;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends ThemeSeeder
{
    protected string $themeName = 'echo';

    public function run(): void
    {
        $this->prepareRun();

        $this->activateTheme($this->themeName);

        $this->uploadFiles('general', 'main');

        $seeders = [];

        foreach ($this->getSeeders() as $seeder) {
            $seeders[Str::afterLast($seeder, '\\')] = $seeder;
        }

        $this->call($seeders);

        $this->finished();
    }

    protected function getSeeders(): array
    {
        return [
            UserSeeder::class,
            LanguageSeeder::class,
            ContactSeeder::class,
            MemberSeeder::class,
            BlogSeeder::class,
            GallerySeeder::class,
            SettingSeeder::class,
            PageSeeder::class,
            ThemeOptionSeeder::class,
            AnnouncementSeeder::class,
            WidgetSeeder::class,
            MenuSeeder::class,
            CommentSeeder::class,
        ];
    }
}
