<?php

namespace Database\Seeders\Themes\Main;

use Botble\Base\Supports\BaseSeeder;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Setting\Facades\Setting;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Botble\Theme\Facades\Theme;

class SettingSeeder extends BaseSeeder
{
    public function run(): void
    {
        $settings = [
            'show_admin_bar' => '1',
            'theme' => Theme::getThemeName(),
            'media_random_hash' => md5(time()),
            'admin_favicon' => $this->filePath('general/favicon.png', 'main'),
            'admin_logo' => $this->filePath('general/logo-dark.png', 'main'),
            SlugHelper::getPermalinkSettingKey(Post::class) => 'blog',
            SlugHelper::getPermalinkSettingKey(Category::class) => 'blog',
        ];

        Setting::delete(array_keys($settings));

        Setting::set($settings)->save();

        Slug::query()->where('reference_type', Post::class)->update(['prefix' => 'blog']);
        Slug::query()->where('reference_type', Category::class)->update(['prefix' => 'blog']);
    }
}
