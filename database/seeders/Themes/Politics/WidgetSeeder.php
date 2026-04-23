<?php

namespace Database\Seeders\Themes\Politics;

use Botble\Widget\Models\Widget;
use Database\Seeders\Themes\Main\WidgetSeeder as MainWidgetSeeder;

class WidgetSeeder extends MainWidgetSeeder
{
    public function run(): void
    {
        Widget::query()->truncate();

        $this->createWidgets($this->getData());
    }

    public function getDataCustomSidebar(): array
    {
        return [
            [
                'widget_id' => 'FollowUsWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 1,
                'data' => [
                    'id' => 'FollowUsWidget',
                    'items' => $this->getFollowUsData(),
                ],
            ],
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 2,
                'data' => [
                    'id' => 'BlogPostsWidget',
                    'title' => 'Popular Now',
                    'style' => 'default-card',
                    'shape' => 'circle',
                    'category_ids' => [1, 2, 3],
                    'enable_lazy_loading' => 'yes',
                ],
            ],
            [
                'widget_id' => 'GalleriesWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 3,
                'data' => [
                    'id' => 'GalleriesWidget',
                    'title' => 'Gallery',
                    'limit' => 4,
                ],
            ],
        ];
    }

    public function getDataCustomSidebar2(): array
    {
        $blogCategoriesBg = $this->filePath('backgrounds/blog-category-bg.png', 'politics');

        return [
            [
                'widget_id' => 'BlogCategoriesWidget',
                'sidebar_id' => 'custom_sidebar_2',
                'position' => 1,
                'data' => [
                    'title' => 'Hot Categories',
                    'id' => 'BlogCategoriesWidget',
                    'quantity' => '5',
                    'category_id_1' => '1',
                    'background_image_1' => $blogCategoriesBg,
                    'background_color_1' => 'rgb(174 81 43)',
                    'category_id_2' => '2',
                    'background_image_2' => $blogCategoriesBg,
                    'background_color_2' => 'rgb(102 64 165)',
                    'category_id_3' => '3',
                    'background_image_3' => $blogCategoriesBg,
                    'background_color_3' => 'rgb(85 163 168)',
                    'category_id_4' => '4',
                    'background_image_4' => $blogCategoriesBg,
                    'background_color_4' => 'rgb(83 39 21)',
                    'category_id_5' => '5',
                    'background_image_5' => $blogCategoriesBg,
                    'background_color_5' => 'rgb(85 163 168)',
                    'enable_lazy_loading' => 'yes',
                ],
            ],
            [
                'widget_id' => 'NewsletterWidget',
                'sidebar_id' => 'custom_sidebar_2',
                'position' => 2,
                'data' => [
                    'id' => 'NewsletterWidget',
                    'title' => 'Stay Tuned With Updates',
                    'image' => $this->filePath('general/newsletter-image.png', 'politics'),
                ],
            ],
        ];
    }

    public function getDataTopHeaderSideBar(): array
    {
        return [];
    }

    public function getDataHeaderSidebar(): array
    {
        return [
            [
                'widget_id' => 'ActionButtonsWidget',
                'sidebar_id' => 'header_sidebar',
                'position' => 0,
                'data' => [
                    'enable_dark_light_switcher_button' => true,
                    'enable_search_button' => true,
                    'enable_toggle_side_menu_button' => true,
                    'alignment' => 'end',
                ],
            ],
        ];
    }
}
