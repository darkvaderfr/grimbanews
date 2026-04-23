<?php

namespace Database\Seeders\Themes\Travel;

use Botble\Widget\Models\Widget;
use Database\Seeders\Themes\Main\WidgetSeeder as MainWidgetSeeder;

class WidgetSeeder extends MainWidgetSeeder
{
    public function run(): void
    {
        Widget::query()->truncate();

        $this->createWidgets($this->getData());
    }

    public function getDataOtherSidebar(): array
    {
        return [
            [
                'widget_id' => 'SocialLinksWidget',
                'sidebar_id' => 'above_footer_sidebar',
                'position' => 1,
                'data' => [],
            ],
        ];
    }

    public function getDataCustomSidebar(): array
    {
        return [
            [
                'widget_id' => 'MembersWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 1,
                'data' => [
                    'id' => 'MembersWidget',
                    'member_ids' => [1],
                ],
            ],
            [
                'widget_id' => 'FollowUsWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 2,
                'data' => [
                    'id' => 'FollowUsWidget',
                    'items' => $this->getFollowUsData(),
                ],
            ],
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 3,
                'data' => [
                    'id' => 'BlogPostsWidget',
                    'title' => 'Top Story',
                    'style' => 'default-card',
                    'shape' => 'square',
                    'limit' => 4,
                    'category_ids' => [2, 3],
                    'enable_lazy_loading' => 'yes',
                ],
            ],
            [
                'widget_id' => 'MembersWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 4,
                'data' => [
                    'id' => 'MembersWidget',
                    'title' => 'Featured Authors',
                    'member_ids' => [2, 4, 5, 6],
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
                'position' => 1,
                'data' => [
                    'id' => 'ActionButtonsWidget',
                    'enable_toggle_side_menu_button' => false,
                    'alignment' => 'end',
                ],
            ],
        ];
    }
}
