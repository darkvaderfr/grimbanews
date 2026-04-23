<?php

namespace Database\Seeders\Themes\Main;

use Botble\Gallery\Database\Traits\HasGallerySeeder;
use Botble\Theme\Database\Seeders\ThemeSeeder;

class GallerySeeder extends ThemeSeeder
{
    use HasGallerySeeder;

    public function run(): void
    {
        $galleries = [
            'Perfect',
            'New Day',
            'Happy Day',
            'Nature',
            'Morning',
            'Sunset',
            'Ocean Views',
            'Adventure Time',
            'City Lights',
            'Dreamscape',
            'Enchanted Forest',
            'Golden Hour',
            'Serenity',
            'Eternal Beauty',
            'Moonlight Magic',
            'Starry Night',
            'Hidden Gems',
            'Tranquil Waters',
            'Urban Escape',
            'Twilight Zone',
        ];

        $this->createGalleries(
            collect($galleries)->map(function (string $item, int $index) {
                return ['name' => $item, 'image' => $this->filePath('news/' . ($index + 1) . '.jpg', 'main')];
            })->toArray(),
            array_map(function ($index) {
                return ['img' => $this->filePath('news/' . $index . '.jpg', 'main'), 'description' => ''];
            }, range(1, 20))
        );
    }
}
