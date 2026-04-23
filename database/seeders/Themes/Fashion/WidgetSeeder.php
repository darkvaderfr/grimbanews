<?php

namespace Database\Seeders\Themes\Fashion;

use Database\Seeders\Themes\Main\WidgetSeeder as MainWidgetSeeder;

class WidgetSeeder extends MainWidgetSeeder
{
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
                'widget_id' => 'BlogCategoriesWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 2,
                'data' => [
                    'title' => 'Popular Categories',
                    'id' => 'BlogCategoriesWidget',
                    'enable_lazy_loading' => 'yes',
                    'quantity' => '5',
                    'category_id_1' => '1',
                    'background_image_1' => $this->filePath('blog-categories/1.png', 'main'),
                    'category_id_2' => '2',
                    'background_image_2' => $this->filePath('blog-categories/2.png', 'main'),
                    'category_id_3' => '3',
                    'background_image_3' => $this->filePath('blog-categories/4.png', 'main'),
                    'category_id_4' => '6',
                    'background_image_4' => $this->filePath('blog-categories/5.png', 'main'),
                    'category_id_5' => '1',
                    'background_image_5' => $this->filePath('blog-categories/3.png', 'main'),
                ],
            ],
            [
                'widget_id' => 'MembersWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 3,
                'data' => [
                    'id' => 'MembersWidget',
                    'title' => 'Featured Authors',
                    'member_ids' => [2, 4, 5, 6],
                ],
            ],
        ];
    }
}
