<?php

namespace Database\Seeders\Themes\Sports;

use Botble\Widget\Models\Widget;
use Botble\Widget\Widgets\CoreSimpleMenu;
use Database\Seeders\Themes\Main\WidgetSeeder as MainWidgetSeeder;

class WidgetSeeder extends MainWidgetSeeder
{
    public function run(): void
    {
        Widget::query()->truncate();

        $this->createWidgets($this->getData());
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
                    'description' => 'By subscribing, you accepted the our Policy.',
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
            [
                'widget_id' => CoreSimpleMenu::class,
                'sidebar_id' => 'footer_sidebar',
                'position' => 2,
                'data' => [
                    'id' => CoreSimpleMenu::class,
                    'name' => 'Most Popular',
                    'items' => $this->getCoreSimpleMenuData(),
                ],
            ],
        ];
    }

    public function getDataTopHeaderSideBar(): array
    {
        return [];
    }
}
