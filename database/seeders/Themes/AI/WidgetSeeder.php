<?php

namespace Database\Seeders\Themes\AI;

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
                'widget_id' => 'ActionButtonsWidget',
                'sidebar_id' => 'header_top_left_sidebar',
                'position' => 1,
                'data' => [
                    'id' => 'ActionButtonsWidget',
                    'enable_search_button' => true,
                    'enable_toggle_side_menu_button' => true,
                    'enable_dark_light_switcher_button' => false,
                    'alignment' => 'start',
                ],
            ],
            [
                'widget_id' => 'ActionButtonsWidget',
                'sidebar_id' => 'header_top_right_sidebar',
                'position' => 1,
                'data' => [
                    'id' => 'ActionButtonsWidget',
                    'enable_dark_light_switcher_button' => true,
                    'enable_search_button' => false,
                    'enable_toggle_side_menu_button' => false,
                    'alignment' => 'end',
                ],
            ],
            [
                'widget_id' => 'SocialLinksWidget',
                'sidebar_id' => 'header_top_right_sidebar',
                'position' => 2,
                'data' => [],
            ],
            [
                'widget_id' => 'ActionButtonsWidget',
                'sidebar_id' => 'header_top_right_sidebar',
                'position' => 3,
                'data' => [
                    'id' => 'ActionButtonsWidget',
                    'enable_dark_light_switcher_button' => false,
                    'enable_search_button' => false,
                    'enable_toggle_side_menu_button' => false,
                    'form_newsletter_button_label' => 'Subscribe',
                    'alignment' => 'end',
                ],
            ],
        ];
    }

    public function getDataTopHeaderSideBar(): array
    {
        return [];
    }

    public function getDataCustomSidebar(): array
    {
        return [
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'custom_sidebar_1',
                'position' => 1,
                'data' => [
                    'type' => 'recent',
                    'category_ids' => [1, 2, 3],
                    'title' => 'Most Read',
                    'limit' => 5,
                    'enable_lazy_loading' => 'yes',
                ],
            ],
        ];
    }
}
