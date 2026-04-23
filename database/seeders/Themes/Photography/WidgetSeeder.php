<?php

namespace Database\Seeders\Themes\Photography;

use Botble\Widget\Models\Widget;
use Database\Seeders\Themes\Main\WidgetSeeder as MainWidgetSeeder;

class WidgetSeeder extends MainWidgetSeeder
{
    public function run(): void
    {
        Widget::query()->truncate();

        $this->createWidgets($this->getData());
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
                    'alignment' => 'end',
                ],
            ],
        ];
    }

    public function getDataFooterSidebar(): array
    {
        return [
            [
                'widget_id' => 'SocialLinksWidget',
                'sidebar_id' => 'footer_sidebar',
                'position' => 1,
                'data' => [],
            ],
        ];
    }

    public function getDataCustomSidebar(): array
    {
        return [
            [
                'widget_id' => 'NewsletterWidget',
                'sidebar_id' => 'above_footer_sidebar',
                'position' => 1,
                'data' => [
                    'id' => 'NewsletterWidget',
                    'title' => 'Get the gaming world News & Stories into your inbox',
                    'description' => '',
                ],
            ],
        ];
    }

    public function getDataMenuSidebar(): array
    {
        return [
            [
                'widget_id' => 'BlogPostsWidget',
                'sidebar_id' => 'menu_sidebar',
                'position' => 1,
                'data' => [
                    'type' => 'recent',
                    'category_ids' => [1, 2, 3],
                    'limit' => 3,
                ],
            ],
            [
                'widget_id' => 'SocialLinksWidget',
                'sidebar_id' => 'menu_sidebar',
                'position' => 2,
                'data' => [],
            ],
        ];
    }
}
