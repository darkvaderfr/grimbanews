<?php

namespace Database\Seeders\Themes\Technology;

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
                    'title' => 'New Podcasts',
                    'style' => 'default-card',
                    'shape' => 'circle',
                    'category_ids' => [2, 3],
                ],
            ],
            [
                'widget_id' => 'ActionButtonsWidget',
                'sidebar_id' => 'header_sidebar',
                'position' => 1,
                'data' => [
                    'id' => 'ActionButtonsWidget',
                    'enable_search_button' => false,
                    'enable_dark_light_switcher_button' => true,
                    'enable_toggle_side_menu_button' => false,
                    'enable_newsletter_button' => false,
                ],
            ],
        ];
    }

    public function getDataCustomSidebar2(): array
    {
        return [];
    }

    public function getDataTopHeaderSideBar(): array
    {
        return [];
    }
}
